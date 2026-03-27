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

namespace Zenstruck\Foundry\Tests\Integration\Attribute\WithStory;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\Attributes\RequiresPhpunitExtension;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Attribute\ResetDatabase;
use Zenstruck\Foundry\Attribute\WithStory;
use Zenstruck\Foundry\PHPUnit\FoundryExtension;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\GenericEntityFactory;
use Zenstruck\Foundry\Tests\Fixture\Stories\EntityStory;
use Zenstruck\Foundry\Tests\Fixture\Stories\SharedEntityStory;
use Zenstruck\Foundry\Tests\Integration\RequiresORM;

/**
 * @author Nicolas PHILIPPE <nikophil@gmail.com>
 * @requires PHPUnit >=11.0
 */
#[RequiresPhpunit('>=11.0')]
#[RequiresPhpunitExtension(FoundryExtension::class)]
#[WithStory(SharedEntityStory::class, shared: true)]
#[ResetDatabase]
final class SharedStoryTest extends KernelTestCase
{
    use RequiresORM;

    #[Test]
    public function shared_story_data_is_available(): void
    {
        GenericEntityFactory::assert()->count(2);

        $this->assertSame('shared-foo', SharedEntityStory::foo()->getProp1());
        $this->assertSame('shared-bar', SharedEntityStory::bar()->getProp1());
    }

    #[Test]
    #[Depends('shared_story_data_is_available')]
    public function shared_story_data_persists_across_tests(): void
    {
        // Data from shared story should still be here (not rolled back by DAMA between tests)
        GenericEntityFactory::assert()->count(2);

        $this->assertSame('shared-foo', SharedEntityStory::foo()->getProp1());
    }

    #[Test]
    #[Depends('shared_story_data_persists_across_tests')]
    public function shared_story_is_built_only_once(): void
    {
        // build() should have been called exactly once for the whole class
        $this->assertSame(1, SharedEntityStory::$buildCount);

        // Data should still be available
        GenericEntityFactory::assert()->count(2);
    }

    #[Test]
    #[Depends('shared_story_data_persists_across_tests')]
    public function per_test_data_is_rolled_back_but_shared_data_remains(): void
    {
        // Create extra entity in this test
        GenericEntityFactory::createOne(['prop1' => 'per-test-entity']);
        GenericEntityFactory::assert()->count(3);
    }

    #[Test]
    #[Depends('per_test_data_is_rolled_back_but_shared_data_remains')]
    public function previous_test_extra_entity_was_rolled_back(): void
    {
        // The per-test entity from previous test should be gone (DAMA rollback)
        // but shared story data should still be here
        GenericEntityFactory::assert()->count(2);
    }

//    #[Test]
//    #[WithStory(EntityStory::class)]
//    public function method_level_story_is_loaded_even_with_shared_class_story(): void
//    {
//        // Shared story data (2 entities) + EntityStory data (2 entities) = 4
//        GenericEntityFactory::assert()->count(4);
//
//        // Shared story data should still be accessible
//        $this->assertSame('shared-foo', SharedEntityStory::foo()->getProp1());
//
//        // Method-level story data should also be accessible
//        $this->assertSame('foo', EntityStory::get('foo')->getProp1());
//    }
}
