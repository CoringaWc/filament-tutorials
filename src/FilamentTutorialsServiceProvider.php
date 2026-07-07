<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials;

use CoringaWc\FilamentTutorials\Support\InlineTutorialCollector;
use CoringaWc\FilamentTutorials\Support\TutorialDiscovery;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use CoringaWc\FilamentTutorials\Support\TutorialPayloadFactory;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTutorialsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-tutorials';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_filament_tutorial_progress_table')
            ->hasRoute('web');
    }

    public function packageRegistered(): void
    {
        $this->app->scoped(InlineTutorialCollector::class);
        $this->app->scoped(TutorialDiscovery::class);
        $this->app->scoped(TutorialManager::class);
        $this->app->scoped(TutorialPayloadFactory::class);
    }
}
