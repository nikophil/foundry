<?php

namespace Integration\Attribute\WithStory;

use DAMA\DoctrineTestBundle\PHPUnit\SkipDatabaseRollback;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Attribute\ResetDatabase;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\GenericEntityFactory;

#[RequiresPhpunit('>=11.0')]
#[ResetDatabase]
class SkipRollbackTest extends KernelTestCase
{
      #[Test]
      public function test(): void
      {
        dump(GenericEntityFactory::count());
        GenericEntityFactory::createOne();

        $this->assertTrue(true);
      }

      #[Test]
      #[SkipDatabaseRollback]
      public function test2(): void
      {
        dump(GenericEntityFactory::count());
        GenericEntityFactory::createOne();

        $this->assertTrue(true);
      }

      #[Test]
      #[SkipDatabaseRollback]
      public function test3(): void
      {
        dump(GenericEntityFactory::count());
        GenericEntityFactory::createOne();

        $this->assertTrue(true);
      }

      #[Test]
      public function test4(): void
      {
        dump(GenericEntityFactory::count());
        GenericEntityFactory::createOne();

        $this->assertTrue(true);
      }

      #[Test]
      public function test5(): void
      {
        dump(GenericEntityFactory::count());
        GenericEntityFactory::createOne();

        $this->assertTrue(true);
      }
}
