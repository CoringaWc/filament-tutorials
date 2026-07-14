<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials;

use CoringaWc\FilamentTutorials\Support\InlineTutorialCollector;
use CoringaWc\FilamentTutorials\Support\TutorialDiscovery;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use CoringaWc\FilamentTutorials\Support\TutorialPayloadFactory;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->app->singleton(TutorialManager::class);
        $this->app->scoped(TutorialPayloadFactory::class);
    }

    public function packageBooted(): void
    {
        RateLimiter::for('filament-tutorials-progress', function (Request $request): Limit {
            $maximumAttempts = max(1, (int) config('filament-tutorials.progress.rate_limit.max_attempts', 120));
            $decaySeconds = max(1, (int) config('filament-tutorials.progress.rate_limit.decay_seconds', 60));

            return Limit::perSecond($maximumAttempts, $decaySeconds)
                ->by(hash('sha256', (string) $request->ip()));
        });
    }
}
