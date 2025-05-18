<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\DebugClassLoader;
use Symfony\Component\Filesystem\Filesystem;

final class LintDeprecationsCommand extends Command
{
    public function __construct(private string $kernelRootDir)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerConfiguration = $this->getComposerConfiguration();

        $psr4Config = array_merge(
            $composerConfiguration['autoload']['psr-4'] ?? [],
            $composerConfiguration['autoload-dev']['psr-4'] ?? []
        );

        $fs = new Filesystem();
        $debugClassLoader = new DebugClassLoader(fn() => null);

        $deprecations = [];
        $this->setErrorHandler($deprecations);

        foreach ($psr4Config as $namespacePrefix => $rootFragment) {
            if (!$fs->exists($rootPath = "{$this->kernelRootDir}/{$rootFragment}")) {
                continue;
            }

            $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootPath));

            /** @var \SplFileInfo $phpFile */
            foreach (new \RegexIterator($allFiles, '/\.php$/') as $phpFile) {
                $class = $this->toPSR4($rootPath, $phpFile, $namespacePrefix);

                $shortName = substr(strrchr($class, '\\'), 1);

                $fileContent = $fs->readFile($phpFile->getRealPath());
                if (!str_contains($fileContent, "class $shortName") && !str_contains($fileContent, "interface $shortName") && !str_contains($fileContent, "enum $shortName")) {
                    continue;
                }

                try {
                    class_exists($class);
                } catch (\Throwable) {
                    continue;
                }

                $debugClassLoader->loadClass($class);
            }
        }
        dump($deprecations);die;

        return 0;
    }

    /**
     * @return array<string, mixed>
     */
    private function getComposerConfiguration(): array
    {
        $composerConfigFilePath = "{$this->kernelRootDir}/composer.json";
        if (!\is_file($composerConfigFilePath)) {
            return [];
        }

        return \json_decode((string) \file_get_contents($composerConfigFilePath), true, 512, \JSON_THROW_ON_ERROR);
    }

    private function toPSR4(string $rootPath, \SplFileInfo $fileInfo, string $namespacePrefix): string
    {
        // /app/src/Bundle/Maker/Factory/NoPersistenceObjectsAutoCompleter.php => /Bundle/Maker/Factory/NoPersistenceObjectsAutoCompleter
        $relativeFileNameWithoutExtension = \str_replace([$rootPath, '.php'], ['', ''], $fileInfo->getRealPath());

        return $namespacePrefix.\str_replace('/', '\\', $relativeFileNameWithoutExtension);
    }

    private function setErrorHandler(array &$deprecations): void
    {
        $previousHandler = set_error_handler(function ($type, $message, $file, $line) use (&$deprecations, &$previousHandler) {
            if (\E_USER_DEPRECATED !== $type && \E_DEPRECATED !== $type) {
                return $previousHandler ? $previousHandler($type, $message, $file, $line) : false;
            }

            if (isset($deprecations[$message])) {
                ++$deprecations[$message]['count'];

                return null;
            }

            $backtrace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            // Clean the trace by removing first frames added by the error handler itself.
            for ($i = 0; isset($backtrace[$i]); ++$i) {
                if (isset($backtrace[$i]['file'], $backtrace[$i]['line']) && $backtrace[$i]['line'] === $line && $backtrace[$i]['file'] === $file) {
                    $backtrace = \array_slice($backtrace, 1 + $i);
                    break;
                }
            }

            $deprecations[$message] = [
                'type' => $type,
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'trace' => $backtrace,
                'count' => 1,
            ];

            return null;
        });
    }
}
