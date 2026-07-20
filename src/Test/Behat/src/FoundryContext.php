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
use Zenstruck\Foundry\Persistence\Exception\ObjectNoLongerExist;
use Zenstruck\Foundry\Persistence\PersistenceManager;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\ProxyGenerator;
use Zenstruck\Foundry\Persistence\RepositoryAssertions;

use function Zenstruck\Foundry\get;
use function Zenstruck\Foundry\Persistence\refresh;
use function Zenstruck\Foundry\Persistence\repository;

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
    #[Then('/^(\d+) (?!.*\S\s+named\s+\S)([^"]*) should exist$/')]
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
        $object = $this->objectRegistry->getByFactoryShortName($factoryShortName, $objectName);
        $this->refreshObject($object, "Object with name \"{$objectName}\" of type \"{$factoryShortName}\" no longer exists in the database.");

        $this->assertProperties($object, self::singleRow($table));
    }

    #[Then('/^(?:the |an? )?(?|"(?P<factoryShortName>[^"]+)"|(?!\d)(?P<factoryShortName>\S+)) named (?|"(?P<objectName>[^"]+)"|(?P<objectName>\S+)) should exist$/')]
    public function assertObjectExists(string $factoryShortName, string $objectName): void
    {
        $objectClass = $this->factoryResolver->targetObjectClassFor($factoryShortName);

        Assert::that($this->objectRegistry->has($objectClass, $objectName))
            ->is(true, "Object with name \"{$objectName}\" of type \"{$factoryShortName}\" has never been created. Create it first with a \"Given\" step.");

        $criteria = $this->identifierCriteriaFor($this->objectRegistry->getByObjectClass($objectClass, $objectName));

        if (null !== $criteria) {
            repository($objectClass)->assert()->exists(
                $criteria,
                "Object with name \"{$objectName}\" of type \"{$factoryShortName}\" does not exist in the database although it should."
            );
        }
    }

    #[Then('/^(?:the |an? )?(?|"(?P<factoryShortName>[^"]+)"|(?!\d)(?P<factoryShortName>\S+)) named (?|"(?P<objectName>[^"]+)"|(?P<objectName>\S+)) should not exist$/')]
    public function assertObjectDoesNotExist(string $factoryShortName, string $objectName): void
    {
        $objectClass = $this->factoryResolver->targetObjectClassFor($factoryShortName);

        if (!$this->objectRegistry->has($objectClass, $objectName)) {
            return;
        }

        $criteria = $this->identifierCriteriaFor($this->objectRegistry->getByObjectClass($objectClass, $objectName));

        if (null !== $criteria) {
            repository($objectClass)->assert()->notExists(
                $criteria,
                "Object with name \"{$objectName}\" of type \"{$factoryShortName}\" still exists in the database although it should not."
            );

            return;
        }

        Assert::fail("Object with name \"{$objectName}\" of type \"{$factoryShortName}\" exists although it should not.");
    }

    #[Then('/^an? (?|"(?P<factoryShortName>[^"]+)"|(?!\d)(?P<factoryShortName>\S+)) should exist with:$/')]
    public function assertSomeObjectExistsWithProperties(FoundryTableNode $table, string $factoryShortName): void
    {
        $this->repositoryAssertionFor($factoryShortName)->exists(
            self::singleRow($table),
            "No \"{$factoryShortName}\" matching the given properties exists in the database."
        );
    }

    #[Then('/^no (?|"(?P<factoryShortName>[^"]+)"|(?!\d)(?P<factoryShortName>\S+)) should exist with:$/')]
    public function assertNoObjectExistsWithProperties(FoundryTableNode $table, string $factoryShortName): void
    {
        $this->repositoryAssertionFor($factoryShortName)->notExists(
            self::singleRow($table),
            "A \"{$factoryShortName}\" matching the given properties exists in the database although it should not."
        );
    }

    #[Then('/^the (?|"(?P<factoryShortName>[^"]+)"|(?!\d)(?P<factoryShortName>\S+)) with id (?|"(?P<id>[^"]+)"|(?P<id>\S+)) should exist$/')]
    public function assertObjectWithIdExists(string $factoryShortName, string $id): void
    {
        $this->findByIdOrFail($factoryShortName, $id);
    }

    #[Then('/^the (?|"(?P<factoryShortName>[^"]+)"|(?!\d)(?P<factoryShortName>\S+)) with id (?|"(?P<id>[^"]+)"|(?P<id>\S+)) should not exist$/')]
    public function assertObjectWithIdDoesNotExist(string $factoryShortName, string $id): void
    {
        $objectClass = $this->factoryResolver->targetObjectClassFor($factoryShortName);

        Assert::that(repository($objectClass)->find($id))
            ->isNull("\"{$factoryShortName}\" with id \"{$id}\" still exists in the database although it should not.");
    }

    #[Then('/^the (?|"(?P<factoryShortName>[^"]+)"|(?!\d)(?P<factoryShortName>\S+)) with id (?|"(?P<id>[^"]+)"|(?P<id>\S+)) should (?:exist and )?have(?: properties)?:$/')]
    public function assertObjectWithIdHasProperties(FoundryTableNode $table, string $factoryShortName, string $id): void
    {
        $object = $this->findByIdOrFail($factoryShortName, $id);
        $this->refreshObject($object, "\"{$factoryShortName}\" with id \"{$id}\" no longer exists in the database.");

        $this->assertProperties($object, self::singleRow($table));
    }

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

    /**
     * When auto-refresh is not handled by lazy objects, re-read the object from the database
     * so assertions see mutations made by the application under test.
     */
    private function refreshObject(object &$object, string $notFoundMessage): void
    {
        if (Configuration::autoRefreshWithLazyObjectsIsEnabled() || null === $this->identifierCriteriaFor($object)) {
            return;
        }

        try {
            refresh($object);
        } catch (ObjectNoLongerExist) {
            Assert::fail($notFoundMessage);
        }
    }

    private function findByIdOrFail(string $factoryShortName, string $id): object
    {
        $objectClass = $this->factoryResolver->targetObjectClassFor($factoryShortName);

        return repository($objectClass)->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function assertProperties(object $object, array $parameters): void
    {
        foreach ($parameters as $key => $valueExpected) {
            $actualValue = get($object, $key);

            match (true) {
                null === $valueExpected => Assert::that($actualValue)->is(null),

                $valueExpected instanceof \DateTimeInterface => self::assertSameDate($actualValue, $valueExpected),

                \is_object($valueExpected) => $this->assertSameObject($actualValue, $valueExpected),

                default => Assert::that($actualValue)->equals($valueExpected),
            };
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function singleRow(FoundryTableNode $table): array
    {
        $parametersList = $table->getColumnsHash();

        if (1 !== \count($parametersList)) {
            throw new \InvalidArgumentException(\sprintf('Expected exactly one line of properties for assertion, got %d lines.', \count($parametersList)));
        }

        return $parametersList[0];
    }

    private static function assertSameDate(mixed $actual, \DateTimeInterface $expected): void
    {
        Assert::that($actual)->isInstanceOf(\DateTimeInterface::class);
        \assert($actual instanceof \DateTimeInterface);

        Assert::that(self::normalizeDate($actual))->is(self::normalizeDate($expected));
    }

    private static function normalizeDate(\DateTimeInterface $date): string
    {
        return \DateTimeImmutable::createFromInterface($date)->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    /**
     * Compares by identifier when both objects are persisted: the expected object comes from the
     * registry and may not be the same instance as the actual value once the kernel has rebooted.
     */
    private function assertSameObject(mixed $actual, object $expected): void
    {
        $expected = ProxyGenerator::unwrap($expected);
        \assert(\is_object($expected));

        Assert::that($actual)->isInstanceOf($expected::class);
        \assert(\is_object($actual));

        $expectedCriteria = $this->identifierCriteriaFor($expected);

        if (null !== $expectedCriteria) {
            Assert::that($this->identifierCriteriaFor($actual))->is($expectedCriteria);

            return;
        }

        Assert::that($actual)->equals($expected);
    }

    /**
     * @return array<string, mixed>|null null when the object cannot be identified in the database
     */
    private function identifierCriteriaFor(object $object): ?array
    {
        // also initializes uninitialized lazy ghosts: reading identifiers through raw
        // reflection on a ghost (e.g. reset by the PersistedObjectsTracker) yields null
        $object = ProxyGenerator::unwrap($object);
        \assert(\is_object($object));

        $persistence = $this->persistenceFor($object);

        if (!$persistence) {
            return null;
        }

        $criteria = $persistence->getIdentifierValues($object);

        return $criteria && !\in_array(null, $criteria, true) ? $criteria : null;
    }

    private function persistenceFor(object $object): ?PersistenceManager
    {
        if (!Configuration::instance()->isPersistenceEnabled()) {
            return null;
        }

        $persistence = Configuration::instance()->persistence();

        return $persistence->hasPersistenceFor($object) ? $persistence : null;
    }
}
