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

use DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension;
use PHPUnit\Event;
use Zenstruck\Foundry\StoryRegistry;

/**
 * @internal
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 */
final class CleanupSharedStoryOnTestSuiteFinished implements Event\TestSuite\FinishedSubscriber
{
    public function notify(Event\TestSuite\Finished $event): void
    {
        if (!SharedStoryState::isActive()) {
            return;
        }

        if (!$event->testSuite()->isForTestClass()) {
            return;
        }

        if ($event->testSuite()->name() !== SharedStoryState::testClassName()) {
            return;
        }

        // Restore DAMA connections so the next class's rollBack() can clean up
        // the root transaction (which contains the shared story data)
        SharedStoryState::restoreConnections();

        // Tell DAMA a transaction is active so it will rollBack the root
        // transaction when the next test class starts
        PHPUnitExtension::$transactionStarted = true;

        StoryRegistry::resetClassInstances();
        SharedStoryState::deactivate();
    }
}
