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

use Symfony\Component\Uid\AbstractUid;
use Zenstruck\Foundry\Persistence\IdentifierResolver;
use Zenstruck\Foundry\Persistence\ProxyGenerator;
use Zenstruck\Foundry\Story\Event\StateAddedToStory;
use Zenstruck\Foundry\Test\Behat\Exception\ObjectAlreadyRegistered;
use Zenstruck\Foundry\Test\Behat\Exception\ObjectNotFound;

use function Zenstruck\Foundry\Persistence\repository;

/**
 * @internal
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 */
final class ObjectRegistry
{
    /**
     * We need to use static properties in order that this is kept between kernel resets.
     */

    /** @var array<class-string, array<string, object>> */
    private static array $objects = [];

    public function __construct(
        private readonly FactoryShortNameResolver $factoryShortNameResolver,
        private readonly IdentifierResolver $persistenceManager,
    ) {
    }

    /**
     * @param StateAddedToStory<object> $event
     */
    public function storeAfterStateAddedToStory(StateAddedToStory $event): void
    {
        $this->store($event->object, $event->name);
    }

    public function store(object $object, string $objectName): void
    {
        // story states may carry a legacy Foundry proxy: index by the real class,
        // like every read path (targetObjectClassFor()) does
        $object = ProxyGenerator::unwrap($object, withAutoRefresh: false);
        \assert(\is_object($object));

        if ($this->has($object::class, $objectName)) {
            throw ObjectAlreadyRegistered::forClassAndName($object::class, $objectName);
        }

        self::$objects[$object::class][$objectName] = $object;
    }

    /**
     * @param class-string $objectClass
     */
    public function has(string $objectClass, string $objectName): bool
    {
        return isset(self::$objects[$objectClass][$objectName]);
    }

    public function getByFactoryShortName(string $factoryShortName, string $objectName): object
    {
        $objectClass = $this->factoryShortNameResolver->targetObjectClassFor($factoryShortName);

        if (!$this->has($objectClass, $objectName)) {
            throw ObjectNotFound::forFactoryAndName($factoryShortName, $objectName);
        }

        return self::$objects[$objectClass][$objectName];
    }

    /**
     * @param class-string $objectClass
     *
     * @throws ObjectNotFound
     */
    public function getByObjectClass(string $objectClass, string $objectName): object
    {
        if (!$this->has($objectClass, $objectName)) {
            throw ObjectNotFound::forClassAndName($objectClass, $objectName);
        }

        return self::$objects[$objectClass][$objectName];
    }

    public function reset(): void
    {
        self::$objects = [];
    }

    /**
     * Resolved from the database (the row with the highest id): also sees rows created
     * by the application under test, not only the ones persisted by Foundry.
     */
    public function lastIdFor(string $factoryShortName): int|string
    {
        $objectClass = $this->factoryShortNameResolver->targetObjectClassFor($factoryShortName);
        $last = repository($objectClass)->lastOrFail();

        return $this->coerceIdToScalar(
            $this->persistenceManager->getIdentifierValues(ProxyGenerator::unwrap($last))
        );
    }

    public function idFor(string $factoryShortName, string $objectName): int|string
    {
        // unwrap() also initializes uninitialized lazy ghosts (e.g. reset by the
        // PersistedObjectsTracker), whose identifiers read as null through raw reflection
        $object = ProxyGenerator::unwrap($this->getByFactoryShortName($factoryShortName, $objectName));
        \assert(\is_object($object));

        return $this->coerceIdToScalar(
            $this->persistenceManager->getIdentifierValues($object)
        );
    }

    /**
     * Replaces every <lastId(factory)> and <id(factory, name)> placeholder in the given string.
     */
    public function resolveIdPlaceholders(string $value): string
    {
        $resolved = \preg_replace_callback(
            '/<(?:lastId\(\s*(?<lastIdFactory>[^)]+?)\s*\)|id\(\s*(?<factory>[^,)]+?)\s*,\s*(?<name>[^)]+?)\s*\))>/',
            fn(array $matches): string => (string) ('' !== ($matches['factory'] ?? '')
                ? $this->idFor(self::unquote($matches['factory']), self::unquote($matches['name']))
                : $this->lastIdFor(self::unquote($matches['lastIdFactory']))),
            $value
        ) ?? $value;

        if (\preg_match('/<(?:lastId(?:\([^)]*\))?|id\([^)]*\))>/', $resolved, $matches)) {
            throw new \InvalidArgumentException("Malformed id placeholder \"{$matches[0]}\": expected <lastId(factory)> or <id(factory, name)>.");
        }

        return $resolved;
    }

    public function isStored(object $object): bool
    {
        return array_any(
            self::$objects[$object::class] ?? [],
            static fn(object $o) => $o === $object
        );
    }

    public function getNameFor(object $object): string
    {
        return array_find_key(
            self::$objects[$object::class] ?? [],
            static fn(object $o) => $o === $object
        ) ?? throw new \LogicException('Object is not stored in the registry.');
    }

    private static function unquote(string $value): string
    {
        return \trim($value, '"');
    }

    /**
     * @param array<string, mixed> $ids
     */
    private function coerceIdToScalar(array $ids): int|string
    {
        if (1 !== \count($ids)) {
            throw new \InvalidArgumentException(\sprintf('Cannot resolve the id: the entity must have exactly one identifier value, got %d ("%s").', \count($ids), \implode('", "', \array_keys($ids))));
        }

        $id = array_first($ids);

        if ($id instanceof AbstractUid) {
            return $id->toRfc4122();
        }

        if (!\is_int($id) && !\is_string($id)) {
            throw new \InvalidArgumentException(\sprintf('Wrong type for the id: expected int, string or Uid, got "%s".', \get_debug_type($id)));
        }

        return $id;
    }
}
