<?php

declare(strict_types=1);

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\PHPUnit;

use PHPUnit\Event;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Attribute\WithStory;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Persistence\PersistenceManager;
use Zenstruck\Foundry\Persistence\ResetDatabase\ResetDatabaseManager;
use Zenstruck\Foundry\StoryRegistry;

/**
 * @internal
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 */
final class LoadSharedStoryOnTestSuiteStarted implements Event\TestSuite\StartedSubscriber
{
    public function notify(Event\TestSuite\Started $event): void
    {
        if (!$event->testSuite()->isForTestClass()) {
            return;
        }

        $testClassName = $event->testSuite()->name();

        if (!\class_exists($testClassName)) {
            return;
        }

        $reflectionClass = new \ReflectionClass($testClassName);
        $allAttributes = AttributeReader::collectAttributesFromClassAndParents(WithStory::class, $reflectionClass);

        $sharedAttributes = \array_values(\array_filter(
            $allAttributes,
            static fn(\ReflectionAttribute $attr) => $attr->newInstance()->shared,
        ));

        if (!$sharedAttributes) {
            return;
        }

        $this->validate($testClassName, $allAttributes, $sharedAttributes);

        if (!ResetDatabaseManager::isDAMADoctrineTestBundleEnabled()) {
            throw new \InvalidArgumentException(\sprintf('#[WithStory(shared: true)] requires DAMADoctrineTestBundle to be enabled. Found on test class "%s".', $testClassName));
        }

        KernelTestCaseHelper::bootKernel($testClassName);
        $container = KernelTestCaseHelper::getContainer($testClassName);
        Configuration::boot(static fn(): Configuration => $container->get('.zenstruck_foundry.configuration')); // @phpstan-ignore return.type

        if (!PersistenceManager::isOrmOnly()) {
            KernelTestCaseHelper::ensureKernelShutdown($testClassName);
            Configuration::shutdown();

            throw new \InvalidArgumentException(\sprintf('#[WithStory(shared: true)] is only supported with ORM (not MongoDB). Found on test class "%s".', $testClassName));
        }

        // Load shared stories inside DAMA's root transaction
        StoryRegistry::beginLoadingClassStories();

        try {
            foreach ($sharedAttributes as $attribute) {
                $attribute->newInstance()->story::load();
            }
        } finally {
            StoryRegistry::endLoadingClassStories();
        }

        // Create a savepoint after shared data — per-test rollbacks will preserve it
        SharedStoryState::createSavepoint();

        SharedStoryState::activate($testClassName);

        // Neutralize DAMA: save connections and clear them so DAMA's
        // rollBack/beginTransaction operate on an empty array (no-op)
        SharedStoryState::saveAndClearDamaConnections();

        KernelTestCaseHelper::ensureKernelShutdown($testClassName);
        Configuration::shutdown();
    }

    /**
     * @param class-string $testClassName
     * @param list<\ReflectionAttribute<WithStory>> $allAttributes
     * @param list<\ReflectionAttribute<WithStory>> $sharedAttributes
     */
    private function validate(string $testClassName, array $allAttributes, array $sharedAttributes): void
    {
        if (!\is_subclass_of($testClassName, KernelTestCase::class)) {
            throw new \InvalidArgumentException(\sprintf('The test class "%s" must extend "%s" to use the "%s" attribute with "shared: true".', $testClassName, KernelTestCase::class, WithStory::class));
        }

        if (\count($sharedAttributes) > 1) {
            throw new \InvalidArgumentException(\sprintf('The test class "%s" cannot have more than one #[WithStory] attribute with "shared: true".', $testClassName));
        }

        if (\count($allAttributes) > \count($sharedAttributes)) {
            throw new \InvalidArgumentException(\sprintf('The test class "%s" cannot mix #[WithStory(shared: true)] with other #[WithStory] attributes on the class.', $testClassName));
        }
    }
}
