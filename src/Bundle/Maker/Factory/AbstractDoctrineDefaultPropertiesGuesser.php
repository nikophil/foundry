<?php

namespace Zenstruck\Foundry\Bundle\Maker\Factory;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;

/** @internal  */
abstract class AbstractDoctrineDefaultPropertiesGuesser implements DefaultPropertiesGuesser
{
    public function __construct(protected ManagerRegistry $managerRegistry, private FactoryFinder $factoryFinder, private FactoryGenerator $factoryGenerator)
    {
    }

    /** @param class-string $fieldClass */
    protected function addDefaultValueUsingFactory(MakeFactoryData $makeFactoryData, MakeFactoryQuery $makeFactoryQuery, string $fieldName, string $fieldClass, bool $isMultiple = false): void
    {
        if (!$factoryClass = $this->factoryFinder->getFactoryForClass($fieldClass)) {
            $factoryClass = $this->factoryGenerator->generateFactory($fieldClass, $makeFactoryQuery);
        }

        $factoryMethod = $isMultiple ? 'new()->many(5)' : 'new()';

        $makeFactoryData->addUse($factoryClass);

        $factoryShortName = \mb_substr($factoryClass, \mb_strrpos($factoryClass, '\\') + 1);
        $makeFactoryData->addDefaultProperty(\lcfirst($fieldName), "{$factoryShortName}::{$factoryMethod},");
    }

    protected function getClassMetadata(MakeFactoryData $makeFactoryData): ClassMetadata
    {
        $class = $makeFactoryData->getObjectFullyQualifiedClassName();

        foreach ($this->managerRegistry->getManagers() as $manager) {
            try {
                $classMetadata = $manager->getClassMetadata($class);
            } catch (\Throwable) {
            }
        }

        if (!isset($classMetadata)) {
            throw new \InvalidArgumentException("\"{$class}\" is not a valid Doctrine class name.");
        }

        return $classMetadata;
    }
}
