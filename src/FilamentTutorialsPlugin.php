<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials;

use CoringaWc\FilamentTutorials\Support\TutorialDiscovery;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Str;

class FilamentTutorialsPlugin implements Plugin
{
    protected bool $shouldDiscoverTutorials = true;

    /** @var array<int, array{path: string, namespace: string}> */
    protected array $discoveryLocations = [];

    /** @var list<FilamentTutorial|class-string<FilamentTutorial>> */
    protected array $tutorials = [];

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-tutorials';
    }

    public function discoverTutorials(?string $in = null, ?string $for = null, bool $enabled = true): static
    {
        $this->shouldDiscoverTutorials = $enabled;

        if ($in !== null && $for !== null) {
            $this->discoveryLocations[] = [
                'path' => $in,
                'namespace' => $for,
            ];
        }

        return $this;
    }

    /**
     * @param  FilamentTutorial|class-string<FilamentTutorial>|array<int, FilamentTutorial|class-string<FilamentTutorial>>  $tutorials
     */
    public function tutorials(FilamentTutorial|string|array $tutorials): static
    {
        foreach (is_array($tutorials) ? $tutorials : [$tutorials] as $tutorial) {
            $this->tutorials[] = $tutorial;
        }

        return $this;
    }

    public function register(Panel $panel): void
    {
        $manager = app(TutorialManager::class);

        $manager->register($panel->getId(), $this->tutorials);

        if (! $this->shouldDiscoverTutorials) {
            return;
        }

        foreach ($this->getDiscoveryLocations($panel) as $location) {
            $manager->register(
                $panel->getId(),
                app(TutorialDiscovery::class)->discover($location['path'], $location['namespace']),
            );
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * @return array<int, array{path: string, namespace: string}>
     */
    protected function getDiscoveryLocations(Panel $panel): array
    {
        if ($this->discoveryLocations !== []) {
            return $this->discoveryLocations;
        }

        $panelNamespace = Str::studly($panel->getId());
        $pathSuffix = config('filament-tutorials.discovery.path_suffix', 'Tutorials');
        $namespaceSuffix = config('filament-tutorials.discovery.namespace_suffix', 'Tutorials');

        return [[
            'path' => app_path("Filament/{$panelNamespace}/{$pathSuffix}"),
            'namespace' => "App\\Filament\\{$panelNamespace}\\{$namespaceSuffix}",
        ]];
    }
}
