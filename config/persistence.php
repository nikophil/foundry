<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

<<<<<<< Updated upstream
||||||| Stash base
use Zenstruck\Foundry\Command\LoadStoryCommand;
=======
use Zenstruck\Foundry\Command\LintDeprecationsCommand;
use Zenstruck\Foundry\Command\LoadStoryCommand;
>>>>>>> Stashed changes
use Zenstruck\Foundry\Persistence\PersistenceManager;
use Zenstruck\Foundry\Persistence\ResetDatabase\ResetDatabaseManager;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.zenstruck_foundry.persistence_manager', PersistenceManager::class)
            ->args([
                tagged_iterator('.foundry.persistence_strategy'),
                service('.zenstruck_foundry.persistence.reset_database_manager'),
            ])
        ->set('.zenstruck_foundry.persistence.reset_database_manager', ResetDatabaseManager::class)
            ->args([
                tagged_iterator('.foundry.persistence.database_resetter'),
                tagged_iterator('.foundry.persistence.schema_resetter'),
            ])
<<<<<<< Updated upstream
||||||| Stash base

        ->set('.zenstruck_foundry.story.load_story-command', LoadStoryCommand::class)
            ->arg('$databaseResetters', tagged_iterator('.foundry.persistence.database_resetter'))
            ->arg('$kernel', service('kernel'))
            ->tag('console.command', [
                'command' => 'foundry:load-stories',
                'aliases' => ['foundry:load-fixtures'],
                'description' => 'Load stories which are marked with #[AsFixture] attribute.',
            ])
=======

        ->set('.zenstruck_foundry.story.load_story-command', LoadStoryCommand::class)
            ->arg('$databaseResetters', tagged_iterator('.foundry.persistence.database_resetter'))
            ->arg('$kernel', service('kernel'))
            ->tag('console.command', [
                'command' => 'foundry:load-stories',
                'aliases' => ['foundry:load-fixtures'],
                'description' => 'Load stories which are marked with #[AsFixture] attribute.',
            ])

        ->set('.zenstruck_foundry.lint-deprecatios-command', LintDeprecationsCommand::class)
            ->args([
                param('kernel.project_dir')
            ])
            ->tag('console.command', [
                'command' => 'symfony:lint-deprecations',
            ])
>>>>>>> Stashed changes
    ;
};
