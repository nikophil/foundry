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

use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Zenstruck\Foundry\ObjectFactory;

/**
 * Built-in "Given" steps creating Foundry objects.
 *
 * The composing class must implement FoundryContextInterface and declare
 * FactoryShortNameResolver $factoryResolver and ObjectRegistry $objectRegistry
 * properties (see FoundryCreationContext).
 *
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 */
trait CreationSteps
{
    #[Given('there is a(n) :factoryShortName')]
    #[Given('there is a(n) :factoryShortName named :objectName')]
    public function createObject(string $factoryShortName, ?string $objectName = null): void
    {
        $this->resolveFactory($factoryShortName, $objectName)->create();
    }

    #[Given('there is a(n) :factoryShortName with:')]
    #[Given('there is a(n) :factoryShortName named :objectName with:')]
    public function createObjectWithProperties(TableNode $table, string $factoryShortName, ?string $objectName = null): void
    {
        $factory = $this->resolveFactory($factoryShortName, $objectName);
        $parametersList = $table->getColumnsHash();

        if (1 !== \count($parametersList)) {
            throw new \InvalidArgumentException(\sprintf('Expected exactly one line of properties to create one object, got %d lines. Use "there are %s with:" to create multiple objects.', \count($parametersList), $factoryShortName));
        }

        $factory->create($parametersList[0]);
    }

    #[Given('there are :factoryShortName with:')]
    public function createObjectsWithProperties(TableNode $table, string $factoryShortName): void
    {
        $parametersList = $table->getColumnsHash();

        foreach ($parametersList as $parameters) {
            $objectName = $parameters['_ref'] ?? null;
            unset($parameters['_ref']);

            $this->resolveFactory($factoryShortName, $objectName)
                ->create($parameters);
        }
    }

    /**
     * @return ObjectFactory<object>
     */
    private function resolveFactory(string $factoryShortName, ?string $objectName = null): ObjectFactory
    {
        $factory = $this->factoryResolver->factoryFor($factoryShortName);

        if (!$objectName) {
            return $factory;
        }

        return $factory->afterInstantiate(
            fn(object $object) => $this->objectRegistry->store($object, $objectName)
        );
    }
}
