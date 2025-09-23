<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Persistence;

use PHPUnit\Event\Code\ClassMethod;

/**
 * If a persistent object has been created in a data provider, we need to initialize the lazy object,
 * which will trigger the object to be persisted.
 *
 * Otherwise, such a test would not pass:
 * ```php
 * #[DataProvider('provide')]
 * public function testSomething(MyEntity $entity): void
 * {
 *     MyEntityFactory::assert()->count(1);
 * }
 *
 * public static function provide(): iterable
 * {
 *     yield [MyEntityFactory::createOne()];
 * }
 * ```
 *
 * Sadly, this cannot be done directly a subscriber, since PHPUnit does not give access to the actual tests instances.
 *
 * ⚠️ This class is highly hacky!
 *
 * If we detect that a persisting object was created in a data provider, we collect the "datasets" of the test,
 * and we trigger the persistence of these objects before the test is executed.
 *
 * This means that the data providers using Foundry are called twice.
 * To prevent the persisted object from being different from the one returned by the data provider, we use a "buffer" so
 * that we can return the same object for each data provider call.
 *
 * @internal
 */
final class PersistentObjectFromDataProviderRegistry
{
    private static ?self $instance = null;

    /** @var array<string, array<array-key, mixed>> */
    private array $datasets = [];

    /** @var list<object> */
    private array $objectsBuffer = [];

    private bool $shouldReturnObjectFromBuffer = false;

    public static function instance(): self
    {
        return self::$instance ?? self::$instance = new self();
    }

    public function storeDatasetIfFoundryWasUsedInDataProvider(string $className, string $methodName, ClassMethod ...$calledMethods): void
    {
        if (count($this->objectsBuffer) === 0) {
            return;
        }

        $this->shouldReturnObjectFromBuffer = true;

        $testCaseContext = $this->testCaseContext($className, $methodName);
        $this->datasets[$testCaseContext] = [];

        foreach ($calledMethods as $calledMethod) {
            $dataProviderResult = "{$calledMethod->className()}::{$calledMethod->methodName()}"(); // @phpstan-ignore callable.nonCallable

            if (!\is_array($dataProviderResult)) {
                $dataProviderResult = \iterator_to_array($dataProviderResult);
            }

            $this->datasets[$testCaseContext] = [...$this->datasets[$testCaseContext], ...$dataProviderResult];
        }

        $this->shouldReturnObjectFromBuffer = false;

        if (count($this->objectsBuffer) !== 0) { // @phpstan-ignore notIdentical.alwaysTrue
            throw new \InvalidArgumentException("No object found. Hint: make sure you're not creating a randomized number of objects with Foundry in a data provider, as they are not supported.");
        }
    }

    /**
     * @template T of object
     *
     * @param PersistentObjectFactory<T> $factory
     *
     * @return ($factory is PersistentProxyObjectFactory<T> ? T&Proxy<T> : T)
     */
    public function deferObjectCreation(PersistentObjectFactory $factory): object
    {
        if (!$factory->isPersisting()) {
            return $factory->create();
        }

        if (!$this->shouldReturnObjectFromBuffer) {
            return $this->objectsBuffer[] = ProxyGenerator::wrapFactory($factory);
        }

        if (count($this->objectsBuffer) === 0) {
            throw new \InvalidArgumentException("No object found. Hint: make sure you're not creating a randomized number of objects with Foundry in a data provider, as they are not supported.");
        }

        return \array_shift($this->objectsBuffer); // @phpstan-ignore return.type
    }

    public function triggerPersistenceForDataset(string $className, string $methodName, int|string $dataSetName): void
    {
        $testCaseContext = $this->testCaseContext($className, $methodName);

        if (!isset($this->datasets[$testCaseContext][$dataSetName])) {
            return;
        }

        initialize_lazy_object($this->datasets[$testCaseContext][$dataSetName]);

        unset($this->datasets[$testCaseContext][$dataSetName]);
    }

    private function testCaseContext(string $className, string $methodName): string
    {
        return "{$className}::{$methodName}";
    }
}
