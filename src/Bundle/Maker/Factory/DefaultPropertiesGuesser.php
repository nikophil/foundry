<?php

namespace Zenstruck\Foundry\Bundle\Maker\Factory;

/**
 * @internal
 */
interface DefaultPropertiesGuesser
{
    public function __invoke(MakeFactoryData $makeFactoryData, MakeFactoryQuery $makeFactoryQuery): void;

    public function supports(MakeFactoryData $makeFactoryData): bool;
}
