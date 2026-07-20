<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Test\Behat;

use Behat\Transformation\Transform;

/**
 * Built-in transformations resolving <foundry:lastId(...)> and <foundry:id(...)>
 * placeholders in step arguments.
 *
 * The composing class must implement FoundryContextInterface and declare an
 * ObjectRegistry $objectRegistry property (see FoundryPlaceholderContext).
 *
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 */
trait PlaceholderTransforms
{
    #[Transform('/(.*)<foundry:lastId\(\s*([^)]+?)\s*\)>(.*)/')]
    public function transformLastIdForSpecificObject(string $before, string $factoryShortName, string $after): string
    {
        return $this->objectRegistry->resolveIdPlaceholders("{$before}<foundry:lastId({$factoryShortName})>{$after}");
    }

    #[Transform('/(.*)<foundry:id\(\s*([^,)]+?)\s*,\s*([^)]+?)\s*\)>(.*)/')]
    public function transformIdForSpecificObject(string $before, string $factoryShortName, string $objectName, string $after): string
    {
        return $this->objectRegistry->resolveIdPlaceholders("{$before}<foundry:id({$factoryShortName}, {$objectName})>{$after}");
    }
}
