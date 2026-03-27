<?php

declare(strict_types=1);

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\PHPUnit;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use DAMA\DoctrineTestBundle\PHPUnit\PHPUnitExtension;
use Doctrine\DBAL\Driver\Connection;

/**
 * @internal
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 */
final class SharedStoryState
{
    private const SAVEPOINT_NAME = 'foundry_shared';

    /** @var class-string|null */
    private static ?string $testClassName = null;

    /** @var array<string, Connection> */
    private static array $savedConnections = [];

    private static ?\ReflectionProperty $connectionsProperty = null;

    /** @param class-string $testClassName */
    public static function activate(string $testClassName): void
    {
        self::$testClassName = $testClassName;
    }

    public static function deactivate(): void
    {
        self::$testClassName = null;
        self::$savedConnections = [];
    }

    public static function isActive(): bool
    {
        return null !== self::$testClassName;
    }

    /** @return class-string */
    public static function testClassName(): string
    {
        return self::$testClassName ?? throw new \LogicException('No shared story is active.');
    }

    /**
     * Create a SAVEPOINT on all DAMA static connections.
     * Called once after loading shared stories.
     */
    public static function createSavepoint(): void
    {
        foreach (self::getDamaConnections() as $connection) {
            $connection->exec(\sprintf('SAVEPOINT %s', self::SAVEPOINT_NAME));
        }
    }

    /**
     * ROLLBACK TO SAVEPOINT on all saved connections.
     * Called before each test to clean up previous test's data while preserving shared story data.
     */
    public static function rollbackToSavepoint(): void
    {
        foreach (self::$savedConnections as $connection) {
            $connection->exec(\sprintf('ROLLBACK TO SAVEPOINT %s', self::SAVEPOINT_NAME));
        }
    }

    /**
     * Save DAMA's static connections, then clear them and reset $transactionStarted.
     * This neutralizes DAMA's per-test rollBack/beginTransaction (they operate on an empty array).
     */
    public static function saveAndClearDamaConnections(): void
    {
        $property = self::connectionsProperty();
        self::$savedConnections = $property->getValue(null);
        $property->setValue(null, []);

        PHPUnitExtension::$transactionStarted = false;
    }

    /**
     * Restore DAMA's static connections from the saved state.
     */
    public static function restoreConnections(): void
    {
        self::connectionsProperty()->setValue(null, self::$savedConnections);
    }

    /** @return array<string, Connection> */
    private static function getDamaConnections(): array
    {
        return self::connectionsProperty()->getValue(null);
    }

    private static function connectionsProperty(): \ReflectionProperty
    {
        return self::$connectionsProperty ??= new \ReflectionProperty(StaticDriver::class, 'connections');
    }
}
