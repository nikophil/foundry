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

/**
 * Context providing only the built-in "Given" steps creating Foundry objects.
 *
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 */
final class FoundryCreationContext implements FoundryContextInterface
{
    use CreationSteps;

    public function __construct(
        private readonly FactoryShortNameResolver $factoryResolver,
        private readonly ObjectRegistry $objectRegistry,
    ) {
    }
}
