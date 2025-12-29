<?php

namespace Zenstruck\Foundry\Persistence;

use Zenstruck\Foundry\Object\Event\AfterInstantiate;

final class PersistAfterInstantiate
{
    public function __invoke(AfterInstantiate $event): void
    {
        foreach (\array_merge(...$event->factory->afterInstantiate) as $hook) {
            $hook($event->object, $event->parameters, $event->factory);
        }
    }
}
