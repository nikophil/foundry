<?php

namespace Zenstruck\Foundry\Command;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Composer\Factory;
use Composer\IO\NullIO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\DebugClassLoader;
use Symfony\Component\Filesystem\Filesystem;

final class LintDeprecationsCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composer = Factory::create(new NullIO(), dirname(__DIR__, 2).'/composer.json');

        /**
         * @param array{"psr-4"?: array<string, string|list<string>>, "psr-0"?: array<string, string|list<string>>} $autoload
         */
        $config = array_merge_recursive(
            $composer->getPackage()->getAutoload(),
            $composer->getPackage()->getDevAutoload()
        );

        $fs = new Filesystem();

        $classMaps = [];
        foreach ($config['psr-4'] as $paths) {
            $paths = (array) $paths;

            foreach ($paths as $path) {
                if (!$fs->exists($path)) {
                    continue;
                }

                $classMaps[] = ClassMapGenerator::createMap($path);
            }
        }
        
        $classNames = array_keys(array_merge(...$classMaps));

        DebugClassLoader::enable();

        $deprecations = [];
        set_error_handler(static function(int $errorNumber, string $errorString) use (&$deprecations){
            $deprecations[$errorString] ??= 0;
            $deprecations[$errorString]++;
        }, E_USER_DEPRECATED | E_DEPRECATED);

        foreach ($classNames as $className) {
            try {
                class_exists($className);
            } catch (\Throwable $e) {
            }
        }
        
        dump($deprecations);die;

        return self::SUCCESS;
    }
}
