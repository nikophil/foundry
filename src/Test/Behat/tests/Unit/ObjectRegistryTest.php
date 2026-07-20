<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Test\Behat\Tests\Unit\Test\Behat;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ObjectFactory;
use Zenstruck\Foundry\Persistence\Event\AfterPersist;
use Zenstruck\Foundry\Persistence\IdentifierResolver;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;
use Zenstruck\Foundry\Story\Event\StateAddedToStory;
use Zenstruck\Foundry\Test\Behat\Exception\ObjectAlreadyRegistered;
use Zenstruck\Foundry\Test\Behat\Exception\ObjectNotFound;
use Zenstruck\Foundry\Test\Behat\FactoryShortNameResolver;
use Zenstruck\Foundry\Test\Behat\ObjectRegistry;

final class ObjectRegistryTest extends TestCase
{
    private ObjectRegistry $registry;
    private FactoryShortNameResolver $resolver;
    private IdentifierResolver $persistenceManager;

    protected function setUp(): void
    {
        $this->resolver = new FactoryShortNameResolver([new UserFactory()]);
        $this->persistenceManager = $this->createStub(IdentifierResolver::class);
        $this->persistenceManager->method('getIdentifierValues')->willReturnCallback(
            static function(object $object): array {
                \assert($object instanceof User || $object instanceof Post);

                return ['id' => $object->id];
            }
        );
        $this->registry = new ObjectRegistry($this->resolver, $this->persistenceManager);
        $this->registry->reset();
    }

    #[Test]
    public function it_stores_an_object(): void
    {
        $user = new User(id: 1, name: 'John');

        $this->registry->store($user, 'john');

        self::assertTrue($this->registry->has(User::class, 'john'));
    }

    #[Test]
    public function it_throws_when_storing_duplicate_object_name(): void
    {
        $user1 = new User(id: 1, name: 'John');
        $user2 = new User(id: 2, name: 'Jane');

        $this->registry->store($user1, 'john');

        $this->expectException(ObjectAlreadyRegistered::class);
        $this->expectExceptionMessage('Object "john" is already registered for class "Zenstruck\Foundry\Test\Behat\Tests\Unit\Test\Behat\User".');

        $this->registry->store($user2, 'john');
    }

    #[Test]
    public function it_allows_same_name_for_different_classes(): void
    {
        $user = new User(id: 1, name: 'John');
        $post = new Post(id: 1, title: 'John');

        $this->registry->store($user, 'john');
        $this->registry->store($post, 'john');

        self::assertTrue($this->registry->has(User::class, 'john'));
        self::assertTrue($this->registry->has(Post::class, 'john'));
    }

    #[Test]
    public function it_checks_if_object_exists(): void
    {
        $user = new User(id: 1, name: 'John');
        $this->registry->store($user, 'john');

        self::assertTrue($this->registry->has(User::class, 'john'));
        self::assertFalse($this->registry->has(User::class, 'jane'));
        self::assertFalse($this->registry->has(Post::class, 'john'));
    }

    #[Test]
    public function it_gets_stored_object(): void
    {
        $user = new User(id: 1, name: 'John');
        $this->registry->store($user, 'john');

        $retrieved = $this->registry->getByObjectClass(User::class, 'john');

        self::assertSame($user, $retrieved);
    }

    #[Test]
    public function it_throws_when_getting_non_existent_object(): void
    {
        $this->expectException(ObjectNotFound::class);
        $this->expectExceptionMessage('Object of class "Zenstruck\Foundry\Test\Behat\Tests\Unit\Test\Behat\User" with name "john" was not found.');

        $this->registry->getByObjectClass(User::class, 'john');
    }

    #[Test]
    public function it_resets_all_stored_objects(): void
    {
        $user = new User(id: 1, name: 'John');
        $this->registry->store($user, 'john');

        $this->registry->reset();

        self::assertFalse($this->registry->has(User::class, 'john'));
    }

    #[Test]
    public function it_stores_last_id_from_after_persist_event(): void
    {
        $user = new User(id: 42, name: 'John');
        $event = new AfterPersist($user, [], $this->createStub(PersistentObjectFactory::class));

        $this->registry->storeLastId($event);

        self::assertSame(42, $this->registry->lastId());
    }

    #[Test]
    public function it_stores_string_id_from_after_persist_event(): void
    {
        $user = new User(id: 'uuid-123', name: 'John');
        $event = new AfterPersist($user, [], $this->createStub(PersistentObjectFactory::class));

        $this->registry->storeLastId($event);

        self::assertSame('uuid-123', $this->registry->lastId());
    }

    #[Test]
    public function it_stores_uuid_from_after_persist_event(): void
    {
        $uuid = Uuid::v7();

        $persistenceManager = $this->createStub(IdentifierResolver::class);
        $persistenceManager->method('getIdentifierValues')->willReturn(['id' => $uuid]);

        $registry = new ObjectRegistry($this->resolver, $persistenceManager);
        $registry->reset();

        $user = new User(id: 1, name: 'John');
        $event = new AfterPersist($user, [], $this->createStub(PersistentObjectFactory::class));

        $registry->storeLastId($event);

        self::assertSame($uuid->toRfc4122(), $registry->lastId());
    }

    #[Test]
    public function it_throws_when_no_last_id_available(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No last id found.');

        $this->registry->lastId();
    }

    #[Test]
    public function it_resets_last_id(): void
    {
        $user = new User(id: 42, name: 'John');
        $event = new AfterPersist($user, [], $this->createStub(PersistentObjectFactory::class));
        $this->registry->storeLastId($event);

        $this->registry->reset();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No last id found.');

        $this->registry->lastId();
    }

    #[Test]
    public function it_gets_last_id_for_specific_factory_even_for_unnamed_objects(): void
    {
        $user1 = new User(id: 1, name: 'John');
        $user2 = new User(id: 2, name: 'Jane');

        $this->registry->storeLastId(new AfterPersist($user1, [], $this->createStub(PersistentObjectFactory::class)));
        $this->registry->storeLastId(new AfterPersist($user2, [], $this->createStub(PersistentObjectFactory::class)));

        self::assertSame(2, $this->registry->lastIdFor('user'));
    }

    #[Test]
    public function it_tracks_last_id_per_class(): void
    {
        $user = new User(id: 1, name: 'John');
        $post = new Post(id: 2, title: 'Hello');

        $this->registry->storeLastId(new AfterPersist($user, [], $this->createStub(PersistentObjectFactory::class)));
        $this->registry->storeLastId(new AfterPersist($post, [], $this->createStub(PersistentObjectFactory::class)));

        self::assertSame(2, $this->registry->lastId());
        self::assertSame(1, $this->registry->lastIdFor('user'));
    }

    #[Test]
    public function it_stores_object_from_state_added_event(): void
    {
        $user = new User(id: 1, name: 'John');
        $event = new StateAddedToStory($user, 'john');

        $this->registry->storeAfterStateAddedToStory($event);

        self::assertTrue($this->registry->has(User::class, 'john'));
        self::assertSame($user, $this->registry->getByObjectClass(User::class, 'john'));
    }

    #[Test]
    public function it_throws_when_storing_duplicate_from_story_event(): void
    {
        $user1 = new User(id: 1, name: 'John');
        $user2 = new User(id: 2, name: 'Jane');

        $event1 = new StateAddedToStory($user1, 'duplicate');
        $event2 = new StateAddedToStory($user2, 'duplicate');

        $this->registry->storeAfterStateAddedToStory($event1);

        $this->expectException(ObjectAlreadyRegistered::class);
        $this->expectExceptionMessage('Object "duplicate" is already registered for class "Zenstruck\Foundry\Test\Behat\Tests\Unit\Test\Behat\User".');

        $this->registry->storeAfterStateAddedToStory($event2);
    }

    #[Test]
    public function it_throws_when_no_objects_for_factory(): void
    {
        $user = new User(id: 1, name: 'John');
        $this->registry->store($user, 'john');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No object of type "user" has been created by Foundry yet.');

        $this->registry->lastIdFor('user');
    }

    #[Test]
    public function it_throws_when_entity_has_multiple_identifiers(): void
    {
        $persistenceManager = $this->createStub(IdentifierResolver::class);
        $persistenceManager->method('getIdentifierValues')->willReturn(['id1' => 1, 'id2' => 2]);

        $registry = new ObjectRegistry($this->resolver, $persistenceManager);

        $user = new User(id: 42, name: 'John');
        $event = new AfterPersist($user, [], $this->createStub(PersistentObjectFactory::class));

        $registry->storeLastId($event);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot resolve the id: the entity must have exactly one identifier value, got 2 ("id1", "id2").');

        $registry->lastId();
    }

    #[Test]
    public function it_throws_when_id_type_is_invalid(): void
    {
        $persistenceManager = $this->createStub(IdentifierResolver::class);
        $persistenceManager->method('getIdentifierValues')->willReturn(['id' => ['invalid']]);

        $registry = new ObjectRegistry($this->resolver, $persistenceManager);

        $user = new User(id: 42, name: 'John');
        $event = new AfterPersist($user, [], $this->createStub(PersistentObjectFactory::class));

        $registry->storeLastId($event);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Wrong type for the id: expected int, string or Uid, got "array".');

        $registry->lastId();
    }

    #[Test]
    public function it_resolves_all_id_placeholders_in_a_string(): void
    {
        $john = new User(id: 7, name: 'John');
        $this->registry->store($john, 'john');
        $this->registry->storeLastId(new AfterPersist($john, [], $this->createStub(PersistentObjectFactory::class)));
        $this->registry->storeLastId(new AfterPersist(new User(id: 9, name: 'Jane'), [], $this->createStub(PersistentObjectFactory::class)));

        self::assertSame(
            '/users/9/friends/7/last-user/9',
            $this->registry->resolveIdPlaceholders('/users/<lastId>/friends/<id(user, john)>/last-user/<lastId(user)>')
        );
    }

    #[Test]
    public function it_resolves_id_placeholders_with_quotes_and_extra_spaces(): void
    {
        $john = new User(id: 7, name: 'John');
        $this->registry->store($john, 'john doe');

        self::assertSame('/7', $this->registry->resolveIdPlaceholders('/<id( "user" , "john doe" )>'));
    }

    #[Test]
    public function it_leaves_strings_without_placeholders_untouched(): void
    {
        self::assertSame('/users/list', $this->registry->resolveIdPlaceholders('/users/list'));
    }

    #[Test]
    public function it_throws_on_malformed_placeholder(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Malformed id placeholder "<id(user)>": expected <lastId>, <lastId(factory)> or <id(factory, name)>.');

        $this->registry->resolveIdPlaceholders('/users/<id(user)>');
    }

    #[Test]
    public function it_stores_proxies_under_their_real_class(): void
    {
        $user = new ProxyableUser();
        $proxy = new FakeUserProxy($user);

        $this->registry->store($proxy, 'john');

        self::assertTrue($this->registry->has(ProxyableUser::class, 'john'));
        self::assertSame($user, $this->registry->getByObjectClass(ProxyableUser::class, 'john'));
    }

    #[Test]
    public function it_checks_if_object_is_stored(): void
    {
        $user = new User(id: 1, name: 'John');
        $otherUser = new User(id: 2, name: 'Jane');

        $this->registry->store($user, 'john');

        self::assertTrue($this->registry->isStored($user));
        self::assertFalse($this->registry->isStored($otherUser));
    }

    #[Test]
    public function it_gets_name_for_stored_object(): void
    {
        $user = new User(id: 1, name: 'John');
        $this->registry->store($user, 'john-doe');

        self::assertSame('john-doe', $this->registry->getNameFor($user));
    }

    #[Test]
    public function it_throws_when_getting_name_for_unstored_object(): void
    {
        $user = new User(id: 1, name: 'John');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Object is not stored in the registry.');

        $this->registry->getNameFor($user);
    }
}

final class User
{
    public function __construct(
        public int|string $id,
        public string $name,
    ) {
    }
}

final class Post
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}

class ProxyableUser
{
    public int $id = 1;
}

/** @implements Proxy<ProxyableUser> */
final class FakeUserProxy implements Proxy
{
    public function __construct(private readonly ProxyableUser $real)
    {
    }

    public function _real(bool $withAutoRefresh = true): object
    {
        return $this->real;
    }

    public function _enableAutoRefresh(): static
    {
        return $this;
    }

    public function _disableAutoRefresh(): static
    {
        return $this;
    }

    public function _withoutAutoRefresh(callable $callback): static
    {
        return $this;
    }

    public function _save(): static
    {
        return $this;
    }

    public function _refresh(): static
    {
        return $this;
    }

    public function _delete(): static
    {
        return $this;
    }

    public function _get(string $property): mixed
    {
        return null;
    }

    public function _set(string $property, mixed $value): static
    {
        return $this;
    }

    public function _assertPersisted(string $message = '{entity} is not persisted.'): static
    {
        return $this;
    }

    public function _assertNotPersisted(string $message = '{entity} is persisted but it should not be.'): static
    {
        return $this;
    }

    public function _repository(): ProxyRepositoryDecorator
    {
        throw new \BadMethodCallException('Not supported by this fake.');
    }

    public function _initializeLazyObject(): void
    {
    }
}

/** @extends ObjectFactory<User> */
final class UserFactory extends ObjectFactory
{
    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array
    {
        return [
            'id' => 1,
            'name' => 'John Doe',
        ];
    }
}
