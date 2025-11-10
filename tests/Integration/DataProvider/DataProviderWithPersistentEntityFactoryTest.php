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

namespace Zenstruck\Foundry\Tests\Integration\DataProvider;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresEnvironmentVariable;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\Attributes\RequiresPhpunitExtension;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\PHPUnit\FoundryExtension;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\GenericEntityFactory;
use Zenstruck\Foundry\Tests\Fixture\Model\GenericModel;
use Zenstruck\Foundry\Tests\Integration\RequiresORM;

/**
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 * @requires PHPUnit >=12
 */
#[RequiresPhpunit('>=12')]
#[RequiresPhp('>=8.4')]
#[RequiresPhpunitExtension(FoundryExtension::class)]
#[RequiresEnvironmentVariable('USE_PHP_84_LAZY_OBJECTS', '1')]
final class DataProviderWithPersistentEntityFactoryTest extends DataProviderWithPersistentFactoryTestCase
{
    use RequiresORM;

    #[Test]
    #[DataProvider('createOneObjectInDataProvider')]
    public function assert_it_can_create_one_object_in_data_provider(?GenericModel $providedData): void
    {
        GenericEntityFactory::assert()->count(1);

        self::assertNotNull($providedData);
        self::assertFalse((new \ReflectionClass($providedData))->isUninitializedLazyObject($providedData));
    }

    public static function createOneObjectInDataProvider(): iterable
    {
        yield 'createOne()' => [
            GenericEntityFactory::createOne(['prop1' => 'value set in data provider']),
        ];
    }

    protected static function factory(): PersistentObjectFactory
    {
        return GenericEntityFactory::new();
    }
}
