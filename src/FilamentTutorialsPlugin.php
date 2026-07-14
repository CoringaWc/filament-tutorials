<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials;

use BackedEnum;
use CoringaWc\FilamentTutorials\Support\InlineTutorialCollector;
use CoringaWc\FilamentTutorials\Support\TutorialDiscovery;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use CoringaWc\FilamentTutorials\Support\TutorialPayloadFactory;
use CoringaWc\FilamentTutorials\Support\TutorialTargetKeys;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Str;

class FilamentTutorialsPlugin implements Plugin
{
    protected bool $shouldDiscoverTutorials = true;

    /** @var array<int, array{path: string, namespace: string}> */
    protected array $discoveryLocations = [];

    /** @var list<FilamentTutorial|class-string<FilamentTutorial>> */
    protected array $tutorials = [];

    protected ?bool $isLauncherEnabled = null;

    protected ?string $launcherRenderHook = null;

    protected string|BackedEnum|null $launcherIcon = null;

    protected ?string $launcherLabel = null;

    protected ?string $launcherTooltip = null;

    protected ?bool $isDismissalReminderEnabled = null;

    protected ?string $dismissalReminderSkipLabel = null;

    protected ?string $dismissalReminderTitle = null;

    protected ?string $dismissalReminderDescription = null;

    /** @var array<string, true> */
    protected array $bootedPanels = [];

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

    public function launcher(
        bool $enabled = true,
        ?string $renderHook = null,
        string|BackedEnum|null $icon = null,
        ?string $tooltip = null,
        ?string $label = null,
    ): static {
        $this->isLauncherEnabled = $enabled;

        if ($renderHook !== null) {
            $this->launcherRenderHook = $renderHook;
        }

        if ($icon !== null) {
            $this->launcherIcon = $icon;
        }

        if ($tooltip !== null) {
            $this->launcherTooltip = $tooltip;
        }

        if ($label !== null) {
            $this->launcherLabel = $label;
        }

        return $this;
    }

    public function withoutLauncher(): static
    {
        $this->isLauncherEnabled = false;

        return $this;
    }

    public function launcherRenderHook(string $renderHook): static
    {
        $this->launcherRenderHook = $renderHook;

        return $this;
    }

    public function launcherIcon(string|BackedEnum $icon): static
    {
        $this->launcherIcon = $icon;

        return $this;
    }

    public function launcherLabel(string $label): static
    {
        $this->launcherLabel = $label;

        return $this;
    }

    public function launcherTooltip(?string $tooltip): static
    {
        $this->launcherTooltip = $tooltip;

        return $this;
    }

    public function dismissalReminder(
        bool $enabled = true,
        ?string $skipLabel = null,
        ?string $title = null,
        ?string $description = null,
    ): static {
        $this->isDismissalReminderEnabled = $enabled;

        if ($skipLabel !== null) {
            $this->dismissalReminderSkipLabel = $skipLabel;
        }

        if ($title !== null) {
            $this->dismissalReminderTitle = $title;
        }

        if ($description !== null) {
            $this->dismissalReminderDescription = $description;
        }

        return $this;
    }

    public function withoutDismissalReminder(): static
    {
        $this->isDismissalReminderEnabled = false;

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

        if (! $this->shouldDiscoverTutorials || ! (bool) config('filament-tutorials.discovery.enabled', true)) {
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
        $panelId = $panel->getId();

        if (isset($this->bootedPanels[$panelId])) {
            return;
        }

        app(TutorialManager::class)->register(
            $panelId,
            app(InlineTutorialCollector::class)->collect($panel),
        );

        $this->registerRuntimeHook($panel);
        $this->registerLauncherHook($panel);
        $this->registerPageTargetHook($panel);
        $this->registerConfiguredRenderHookTargets($panel);
        $this->registerNavigationTargetAttributes($panel);

        $this->bootedPanels[$panelId] = true;
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
            PanelsRenderHook::PAGE_END,
            function (array $scopes = []) use ($panel): string {
                if (! $this->isCurrentPanel($panel)) {
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
        if (! $this->isLauncherEnabled()) {
            return;
        }

        $renderHook = $this->getLauncherRenderHook();

        FilamentView::registerRenderHook(
            $renderHook,
            function (array $scopes = []) use ($panel): string {
                if (! $this->isCurrentPanel($panel)) {
                    return '';
                }

                if (! app(TutorialPayloadFactory::class)->hasTutorialForScopes($panel->getId(), $scopes)) {
                    return '';
                }

                return view('filament-tutorials::launcher', [
                    'icon' => $this->getLauncherIcon(),
                    'label' => $this->getLauncherLabel(),
                    'tooltip' => $this->getLauncherTooltip(),
                ])->render();
            },
        );
    }

    protected function registerPageTargetHook(Panel $panel): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_START,
            function (array $scopes = []) use ($panel): string {
                if (! $this->isCurrentPanel($panel)) {
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
                    if (! $this->isCurrentPanel($panel)) {
                        return '';
                    }

                    return view('filament-tutorials::target-marker', [
                        'target' => TutorialTargetKeys::renderHook($targetKey),
                    ])->render();
                },
            );
        }
    }

    protected function registerNavigationTargetAttributes(Panel $panel): void
    {
        NavigationItem::configureUsing(function (NavigationItem $item) use ($panel): void {
            $item->extraAttributes(function () use ($item, $panel): array {
                if (! $this->isCurrentPanel($panel)) {
                    return [];
                }

                $target = $this->navigationTargetForItem($item, $panel);

                return $target === null ? [] : ['data-tour' => $target];
            }, merge: true);
        });
    }

    protected function navigationTargetForItem(NavigationItem $item, Panel $panel): ?string
    {
        $url = $this->normalizeNavigationUrl($item->getUrl());

        if ($url === null) {
            return null;
        }

        foreach ($panel->getPages() as $page) {
            if (! is_subclass_of($page, Page::class)) {
                continue;
            }

            if ($url === $this->normalizeNavigationUrl($page::getNavigationUrl())) {
                return TutorialTargetKeys::navigation($page);
            }
        }

        foreach ($panel->getResources() as $resource) {
            if (! is_subclass_of($resource, Resource::class)) {
                continue;
            }

            if ($url === $this->normalizeNavigationUrl($resource::getNavigationUrl())) {
                return TutorialTargetKeys::navigation($resource);
            }
        }

        return null;
    }

    protected function isCurrentPanel(Panel $panel): bool
    {
        return Filament::getCurrentPanel()?->getId() === $panel->getId();
    }

    protected function normalizeNavigationUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        return rtrim($url, '/');
    }

    /**
     * @return array{enabled: bool, selector: string, stepKey: string, skipLabel: string, title: string, description: string}
     */
    public function getDismissalReminderPayload(): array
    {
        return [
            'enabled' => $this->isDismissalReminderEnabled(),
            'selector' => '[data-tour="tutorial.launcher"]',
            'stepKey' => (string) config('filament-tutorials.dismissal_reminder.step_key', 'reopen-page-tutorial'),
            'skipLabel' => $this->getDismissalReminderSkipLabel(),
            'title' => $this->getDismissalReminderTitle(),
            'description' => $this->getDismissalReminderDescription(),
        ];
    }

    public function isDismissalReminderEnabled(): bool
    {
        return $this->isLauncherEnabled() && ($this->isDismissalReminderEnabled ?? (bool) config('filament-tutorials.dismissal_reminder.enabled', true));
    }

    protected function getDismissalReminderSkipLabel(): string
    {
        return $this->dismissalReminderSkipLabel
            ?? (string) config('filament-tutorials.dismissal_reminder.skip_label', __('Ignorar'));
    }

    protected function getDismissalReminderTitle(): string
    {
        return $this->dismissalReminderTitle
            ?? (string) config('filament-tutorials.dismissal_reminder.title', __('Você pode voltar quando quiser'));
    }

    protected function getDismissalReminderDescription(): string
    {
        return $this->dismissalReminderDescription
            ?? (string) config('filament-tutorials.dismissal_reminder.description', __('Para rever este guia, clique no ícone de interrogação no topo da página.'));
    }

    protected function isLauncherEnabled(): bool
    {
        return $this->isLauncherEnabled ?? (bool) config('filament-tutorials.launcher.enabled', true);
    }

    protected function getLauncherRenderHook(): string
    {
        /** @var string|null $configuredRenderHook */
        $configuredRenderHook = config('filament-tutorials.launcher.render_hook')
            ?? config('filament-tutorials.launcher_render_hook');

        return $this->launcherRenderHook
            ?? $configuredRenderHook
            ?? PanelsRenderHook::USER_MENU_BEFORE;
    }

    protected function getLauncherIcon(): string|BackedEnum
    {
        $configuredIcon = config('filament-tutorials.launcher.icon');

        return $this->launcherIcon
            ?? ($configuredIcon instanceof BackedEnum || is_string($configuredIcon) ? $configuredIcon : null)
            ?? Heroicon::QuestionMarkCircle;
    }

    protected function getLauncherLabel(): string
    {
        return $this->launcherLabel
            ?? (string) config('filament-tutorials.launcher.label', __('Abrir tutorial da página'));
    }

    protected function getLauncherTooltip(): ?string
    {
        if ($this->launcherTooltip !== null) {
            return $this->launcherTooltip;
        }

        $configuredTooltip = config('filament-tutorials.launcher.tooltip', __('Abrir tutorial da página'));

        return is_string($configuredTooltip) ? $configuredTooltip : null;
    }
}
