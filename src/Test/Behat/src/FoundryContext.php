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

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Transformation\Transform;
use Zenstruck\Assert;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\ObjectFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\RepositoryAssertions;

use function Zenstruck\Foundry\get;
use function Zenstruck\Foundry\Persistence\refresh;

/**
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 *
 * @phpstan-import-type Parameters from Factory
 *
 * @internal
 */
final class FoundryContext implements Context
{
    public function __construct(
        private readonly FactoryShortNameResolver $factoryResolver,
        private readonly ObjectRegistry $objectRegistry,
    ) {
    }

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

    #[Then('/^(\d+) "([^"]*)" should exist$/')]
    #[Then('/^(\d+) ([^"]*) should exist$/')]
    public function assertNbObjectsExist(int $nb, string $factoryShortName): void
    {
        $this->repositoryAssertionFor($factoryShortName)
            ->count($nb);
    }

    /**
     * Captures:
     *  - factoryShortName: quoted or unquoted factory/entity name
     *  - objectName: quoted or unquoted object reference
     *
     * Supports optional articles ("the", "a", "an"), optional "exist and",
     * and optional trailing "properties".
     */
    #[Then('/^(?:the |an? )?(?|"(?P<factoryShortName>[^"]+)"|(?!\d)(?P<factoryShortName>\S+)) named (?|"(?P<objectName>[^"]+)"|(?P<objectName>\S+)) should (?:exist and )?have(?: properties)?:$/')]
    public function assertObjectHasProperties(FoundryTableNode $table, string $factoryShortName, string $objectName): void
    {
        $parametersList = $table->getColumnsHash();

        if (1 !== \count($parametersList)) {
            throw new \InvalidArgumentException(\sprintf('Expected exactly one line of properties for assertion, got %d lines.', \count($parametersList)));
        }

        $object = $this->objectRegistry->getByFactoryShortName($factoryShortName, $objectName);

        if (!Configuration::autoRefreshWithLazyObjectsIsEnabled()) {
            refresh($object);
        }

        foreach ($parametersList[0] as $key => $valueExpected) {
            $actualValue = get($object, $key);

            match (true) {
                $valueExpected instanceof \DateTimeInterface => Assert::that($actualValue)
                    ->isInstanceOf(\DateTimeInterface::class)
                    ->and($actualValue->format('Y-m-d H:i:s'))
                    ->is($valueExpected->format('Y-m-d H:i:s')),

                \is_object($valueExpected) => Assert::that($actualValue)->is($valueExpected),

                default => Assert::that($actualValue)->equals($valueExpected),
            };
        }
    }

    #[Then('/^(?:the |an? )?(?|"(?P<factoryShortName>[^"]+)"|(?!\d)(?P<factoryShortName>\S+)) named (?|"(?P<objectName>[^"]+)"|(?P<objectName>\S+)) should exist$/')]
    public function assertObjectExists(string $factoryShortName, string $objectName): void
    {
        Assert::that(
            $this->objectRegistry->has(
                $this->factoryResolver->targetObjectClassFor($factoryShortName),
                $objectName
            )
        )->is(true, "Object with name \"{$objectName}\" of type \"{$factoryShortName}\" does not exist although it should.");
    }

    #[Then('/^(?:the |an? )?(?|"(?P<factoryShortName>[^"]+)"|(?!\d)(?P<factoryShortName>\S+)) named (?|"(?P<objectName>[^"]+)"|(?P<objectName>\S+)) should not exist$/')]
    public function assertObjectDoesNotExist(string $factoryShortName, string $objectName): void
    {
        Assert::that(
            $this->objectRegistry->has(
                $this->factoryResolver->targetObjectClassFor($factoryShortName),
                $objectName
            )
        )->is(false, "Object with name \"{$objectName}\" of type \"{$factoryShortName}\" exists although it should not.");
    }

    #[Transform('/(.*)<lastId>(.*)/')]
    public function transformLastId(string $before, string $after): string
    {
        return "{$before}{$this->objectRegistry->lastId()}{$after}";
    }

    #[Transform('/(.*)<lastId\((.*)\)>(.*)/')]
    public function transformLastIdForSpecificObject(string $before, string $factoryShortName, string $after): string
    {
        return "{$before}{$this->objectRegistry->lastIdFor($factoryShortName)}{$after}";
    }

    #[Transform('/(.*)<id\((.*), (.*)\)>(.*)/')]
    public function transformIdForSpecificObject(string $before, string $factoryShortName, string $objectName, string $after): string
    {
        return "{$before}{$this->objectRegistry->idFor($factoryShortName, $objectName)}{$after}";
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

    private function repositoryAssertionFor(string $factoryShortName): RepositoryAssertions
    {
        $factory = $this->factoryResolver->factoryFor($factoryShortName);

        if (!$factory instanceof PersistentObjectFactory) {
            throw new \LogicException(\sprintf('Cannot make assertions with factory of class "%s" with short name "%s": it does not extend "%s".', $factory::class, $factoryShortName, PersistentObjectFactory::class));
        }

        return $factory::assert();
    }
}
