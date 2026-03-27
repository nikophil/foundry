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

/**
 * Neutralizes DAMA's per-test rollBack/beginTransaction for shared story classes.
 *
 * By saving and clearing StaticDriver::$connections in Test::Finished (which fires
 * BEFORE the next test's PreparationStarted), DAMA's subscriber operates on an empty
 * array — making both rollBack() and beginTransaction() no-ops. This is independent
 * of extension registration order.
 *
 * @internal
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 */
final class NeutralizeDamaOnTestFinished implements Event\Test\FinishedSubscriber
{
    public function notify(Event\Test\Finished $event): void
    {
        if (!SharedStoryState::isActive()) {
            return;
        }

        SharedStoryState::saveAndClearDamaConnections();
    }
}
