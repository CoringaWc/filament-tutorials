<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials;

use CoringaWc\FilamentTutorials\Support\TutorialDiscovery;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTutorialsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-tutorials';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        $this->app->scoped(TutorialDiscovery::class);
        $this->app->scoped(TutorialManager::class);
    }
}
