<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Zenstruck\Foundry\Story\Event\StateAddedToStory;
use Zenstruck\Foundry\Test\Behat\FactoryShortNameResolver;
use Zenstruck\Foundry\Test\Behat\FoundryAssertionContext;
use Zenstruck\Foundry\Test\Behat\FoundryContext;
use Zenstruck\Foundry\Test\Behat\FoundryCreationContext;
use Zenstruck\Foundry\Test\Behat\FoundryPlaceholderContext;
use Zenstruck\Foundry\Test\Behat\ObjectRegistry;

return static function(ContainerConfigurator $container): void {
    $container->services()
        ->set('.zenstruck_foundry.behat.factory_resolver', FactoryShortNameResolver::class)
        ->args([
            tagged_iterator('foundry.factory'),
        ])
        ->public()

        ->set('.zenstruck_foundry.behat.object_registry', ObjectRegistry::class)
        ->args([
            service('.zenstruck_foundry.behat.factory_resolver'),
            service('.zenstruck_foundry.persistence_manager'),
        ])
        ->tag('kernel.event_listener', ['method' => 'storeAfterStateAddedToStory', 'event' => StateAddedToStory::class])
        ->public()

        // public autowire aliases so custom contexts composed from the step traits can be autowired
        ->alias(FactoryShortNameResolver::class, '.zenstruck_foundry.behat.factory_resolver')->public()
        ->alias(ObjectRegistry::class, '.zenstruck_foundry.behat.object_registry')->public()

        ->set(FoundryContext::class, FoundryContext::class)
        ->args([
            service('.zenstruck_foundry.behat.factory_resolver'),
            service('.zenstruck_foundry.behat.object_registry'),
        ])
        ->public()
        ->autowire()
        ->autoconfigure()

        ->set(FoundryCreationContext::class, FoundryCreationContext::class)
        ->args([
            service('.zenstruck_foundry.behat.factory_resolver'),
            service('.zenstruck_foundry.behat.object_registry'),
        ])
        ->public()
        ->autowire()
        ->autoconfigure()

        ->set(FoundryAssertionContext::class, FoundryAssertionContext::class)
        ->args([
            service('.zenstruck_foundry.behat.factory_resolver'),
            service('.zenstruck_foundry.behat.object_registry'),
        ])
        ->public()
        ->autowire()
        ->autoconfigure()

        ->set(FoundryPlaceholderContext::class, FoundryPlaceholderContext::class)
        ->args([
            service('.zenstruck_foundry.behat.object_registry'),
        ])
        ->public()
        ->autowire()
        ->autoconfigure()
    ;
};
