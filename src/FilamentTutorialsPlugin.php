<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials;

use CoringaWc\FilamentTutorials\Support\InlineTutorialCollector;
use CoringaWc\FilamentTutorials\Support\TutorialDiscovery;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use CoringaWc\FilamentTutorials\Support\TutorialPayloadFactory;
use CoringaWc\FilamentTutorials\Support\TutorialTargetKeys;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
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
        $panel->assets([
            Css::make('filament-tutorials', __DIR__.'/../resources/dist/filament-tutorials.css'),
            Js::make('filament-tutorials', __DIR__.'/../resources/dist/filament-tutorials.js')->module(),
        ], 'coringawc/filament-tutorials');

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
        app(TutorialManager::class)->register(
            $panel->getId(),
            app(InlineTutorialCollector::class)->collect($panel),
        );

        $this->registerRuntimeHook($panel);
        $this->registerLauncherHook($panel);
        $this->registerPageTargetHook($panel);
        $this->registerConfiguredRenderHookTargets($panel);
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
        $appNamespace = rtrim(app()->getNamespace(), '\\');

        return [[
            'path' => app_path("Filament/{$panelNamespace}/{$pathSuffix}"),
            'namespace' => "{$appNamespace}\\Filament\\{$panelNamespace}\\{$namespaceSuffix}",
        ]];
    }

    protected function registerRuntimeHook(Panel $panel): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            function (array $scopes = []) use ($panel): string {
                if (Filament::getCurrentPanel()->getId() !== $panel->getId()) {
                    return '';
                }

                return view('filament-tutorials::runtime', [
                    'payload' => app(TutorialPayloadFactory::class)->forPanelAndScopes($panel->getId(), $scopes),
                ])->render();
            },
        );
    }

    protected function registerLauncherHook(Panel $panel): void
    {
        /** @var string $renderHook */
        $renderHook = config('filament-tutorials.launcher_render_hook', PanelsRenderHook::GLOBAL_SEARCH_BEFORE);

        FilamentView::registerRenderHook(
            $renderHook,
            function (array $scopes = []) use ($panel): string {
                if (Filament::getCurrentPanel()->getId() !== $panel->getId()) {
                    return '';
                }

                if (app(TutorialManager::class)->forPanel($panel->getId()) === []) {
                    return '';
                }

                return view('filament-tutorials::launcher')->render();
            },
        );
    }

    protected function registerPageTargetHook(Panel $panel): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_START,
            function (array $scopes = []) use ($panel): string {
                if (Filament::getCurrentPanel()->getId() !== $panel->getId()) {
                    return '';
                }

                foreach ($scopes as $scope) {
                    if (! class_exists($scope)) {
                        continue;
                    }

                    return view('filament-tutorials::target-marker', [
                        'target' => TutorialTargetKeys::page($scope),
                    ])->render();
                }

                return '';
            },
        );
    }

    protected function registerConfiguredRenderHookTargets(Panel $panel): void
    {
        /** @var array<string, string> $renderHookTargets */
        $renderHookTargets = config('filament-tutorials.render_hook_targets', []);

        foreach ($renderHookTargets as $targetKey => $renderHook) {
            FilamentView::registerRenderHook(
                $renderHook,
                function () use ($panel, $targetKey): string {
                    if (Filament::getCurrentPanel()->getId() !== $panel->getId()) {
                        return '';
                    }

                    return view('filament-tutorials::target-marker', [
                        'target' => TutorialTargetKeys::renderHook($targetKey),
                    ])->render();
                },
            );
        }
    }
}
