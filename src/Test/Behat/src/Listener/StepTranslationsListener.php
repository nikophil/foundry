<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Test\Behat\Listener;

use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\Translator;
use Zenstruck\Foundry\Test\Behat\Exception\UnsupportedTranslationResource;

/**
 * Overrides the built-in step definition patterns by registering translations
 * for the canonical patterns onto Behat's translator.
 *
 * @internal
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 */
final class StepTranslationsListener implements EventSubscriberInterface
{
    /**
     * @param array<string, string> $steps        canonical pattern => overriding pattern
     * @param list<string>          $translations paths to xliff/yaml/php catalogues
     */
    public function __construct(
        private readonly Translator $translator,
        private readonly array $steps,
        private readonly array $translations,
        private readonly string $locale,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SuiteTested::BEFORE => ['registerTranslations', 200],
        ];
    }

    public function registerTranslations(BeforeSuiteTested $event): void
    {
        $suiteName = $event->getSuite()->getName();

        if ($this->steps) {
            $this->translator->addResource('array', $this->steps, $this->locale, $suiteName);
        }

        foreach ($this->translations as $path) {
            $this->translator->addResource(self::loaderFor($path), $path, $this->locale, $suiteName);
        }
    }

    private static function loaderFor(string $path): string
    {
        return match (\mb_strtolower(\pathinfo($path, \PATHINFO_EXTENSION))) {
            'yaml', 'yml' => 'yaml',
            'xliff', 'xlf' => 'xliff',
            'php' => 'php',
            default => throw UnsupportedTranslationResource::forPath($path),
        };
    }
}
