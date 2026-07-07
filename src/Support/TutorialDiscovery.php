<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Support;

use CoringaWc\FilamentTutorials\FilamentTutorial;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class TutorialDiscovery
{
    /**
     * @return list<class-string<FilamentTutorial>>
     */
    public function discover(string $path, string $namespace): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $tutorials = [];
        $basePath = rtrim(realpath($path) ?: $path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath, '', $file->getRealPath() ?: $file->getPathname());
            $class = rtrim($namespace, '\\').'\\'.str_replace(
                ['/', '.php'],
                ['\\', ''],
                $relativePath,
            );

            if (! is_subclass_of($class, FilamentTutorial::class)) {
                continue;
            }

            /** @var class-string<FilamentTutorial> $class */
            $tutorials[] = $class;
        }

        sort($tutorials);

        return $tutorials;
    }
}
