<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Test\Behat\Tests\Unit\Listener;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\Suite;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Zenstruck\Foundry\Test\Behat\Exception\UnsupportedTranslationResource;
use Zenstruck\Foundry\Test\Behat\Listener\StepTranslationsListener;

final class StepTranslationsListenerTest extends TestCase
{
    private const CANONICAL_PATTERN = 'there is a(n) :factoryShortName named :objectName';
    private const OVERRIDING_PATTERN = 'create a :factoryShortName called :objectName';

    #[Test]
    public function it_registers_inline_step_overrides_scoped_to_the_suite(): void
    {
        $translator = self::createTranslator();

        $listener = new StepTranslationsListener(
            $translator,
            [self::CANONICAL_PATTERN => self::OVERRIDING_PATTERN],
            [],
            'en',
        );

        $listener->registerTranslations(self::createEvent('my-suite'));

        self::assertSame(self::OVERRIDING_PATTERN, $translator->trans(self::CANONICAL_PATTERN, [], 'my-suite', 'en'));
        // another suite (i.e. another translation domain) is left untouched
        self::assertSame(self::CANONICAL_PATTERN, $translator->trans(self::CANONICAL_PATTERN, [], 'another-suite', 'en'));
    }

    #[Test]
    public function it_registers_overrides_from_a_translation_file(): void
    {
        $translator = self::createTranslator();

        $listener = new StepTranslationsListener(
            $translator,
            [],
            [__DIR__.'/../../Fixture/translations/foundry-steps.xliff'],
            'en',
        );

        $listener->registerTranslations(self::createEvent('my-suite'));

        self::assertSame(self::OVERRIDING_PATTERN, $translator->trans(self::CANONICAL_PATTERN, [], 'my-suite', 'en'));
    }

    #[Test]
    public function it_applies_overrides_to_the_configured_locale(): void
    {
        $translator = self::createTranslator();

        $listener = new StepTranslationsListener(
            $translator,
            [self::CANONICAL_PATTERN => 'il existe un(e) :factoryShortName nommé :objectName'],
            [],
            'fr',
        );

        $listener->registerTranslations(self::createEvent('my-suite'));

        self::assertSame('il existe un(e) :factoryShortName nommé :objectName', $translator->trans(self::CANONICAL_PATTERN, [], 'my-suite', 'fr'));
        // the "en" catalogue (i.e. default ".feature" language) is not affected
        self::assertSame(self::CANONICAL_PATTERN, $translator->trans(self::CANONICAL_PATTERN, [], 'my-suite', 'en'));
    }

    #[Test]
    public function it_throws_on_an_unsupported_translation_resource(): void
    {
        $listener = new StepTranslationsListener(self::createTranslator(), [], ['/path/to/steps.txt'], 'en');

        $this->expectException(UnsupportedTranslationResource::class);

        $listener->registerTranslations(self::createEvent('my-suite'));
    }

    private static function createTranslator(): Translator
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addLoader('xliff', new XliffFileLoader());

        return $translator;
    }

    private static function createEvent(string $suiteName): BeforeSuiteTested
    {
        $suite = self::createStub(Suite::class);
        $suite->method('getName')->willReturn($suiteName);

        $environment = self::createStub(Environment::class);
        $environment->method('getSuite')->willReturn($suite);

        return new BeforeSuiteTested($environment, self::createStub(SpecificationIterator::class));
    }
}
