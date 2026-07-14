<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Support;

use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\FilamentTutorialsPlugin;
use CoringaWc\FilamentTutorials\Models\FilamentTutorialProgress;
use CoringaWc\FilamentTutorials\TutorialStep;
use Filament\Facades\Filament;
use Filament\Pages\BasePage;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class TutorialPayloadFactory
{
    public function __construct(
        private readonly TutorialManager $tutorialManager,
        private readonly PanelAuthenticatedUserResolver $authenticatedUserResolver,
    ) {}

    /**
     * @param  array<int, string>  $scopes
     * @return array{labels: array<string, string>, dismissalReminder: array<string, mixed>, progress: array<string, mixed>|null, tutorials: list<array<string, mixed>>}
     */
    public function forPanelAndScopes(string $panelId, array $scopes): array
    {
        $scopes = $this->normalizeScopes($scopes);
        $progressContext = $this->progressContext($panelId);

        return [
            'labels' => [
                'next' => __('Próximo'),
                'previous' => __('Anterior'),
                'done' => __('Concluir'),
                'progress' => __('{{current}} de {{total}}'),
            ],
            'dismissalReminder' => $this->dismissalReminderPayload($panelId),
            'progress' => $progressContext['endpoint'] === null ? null : [
                'endpoint' => $progressContext['endpoint'],
                'panelId' => $panelId,
            ],
            'tutorials' => array_values(array_filter(array_map(
                fn (FilamentTutorial $tutorial): ?array => $this->tutorialPayload($tutorial, $scopes, $progressContext['progress'][$tutorial->getKey()] ?? null),
                $this->tutorialManager->forPanel($panelId),
            ))),
        ];
    }

    /**
     * @param  array<int, string>  $scopes
     */
    public function hasTutorialForScopes(string $panelId, array $scopes): bool
    {
        $scopes = $this->normalizeScopes($scopes);

        return array_any($this->tutorialManager->forPanel($panelId), fn (FilamentTutorial $tutorial): bool => $this->tutorialMatchesScopes($tutorial, $scopes));
    }

    /**
     * @return array{enabled: bool, selector: string, stepKey: string, skipLabel: string, title: string, description: string}
     */
    private function dismissalReminderPayload(string $panelId): array
    {
        $panel = Filament::getPanel($panelId, false);
        $plugin = $panel->hasPlugin('filament-tutorials') ? $panel->getPlugin('filament-tutorials') : null;

        if ($plugin instanceof FilamentTutorialsPlugin) {
            return $plugin->getDismissalReminderPayload();
        }

        return [
            'enabled' => (bool) config('filament-tutorials.dismissal_reminder.enabled', true),
            'selector' => '[data-tour="tutorial.launcher"]',
            'stepKey' => (string) config('filament-tutorials.dismissal_reminder.step_key', 'reopen-page-tutorial'),
            'skipLabel' => (string) config('filament-tutorials.dismissal_reminder.skip_label', __('Ignorar')),
            'title' => (string) config('filament-tutorials.dismissal_reminder.title', __('Você pode voltar quando quiser')),
            'description' => (string) config('filament-tutorials.dismissal_reminder.description', __('Para rever este guia, clique no ícone de interrogação no topo da página.')),
        ];
    }

    /**
     * @param  array<int, string>  $scopes
     * @return array<string, mixed>|null
     */
    private function tutorialPayload(FilamentTutorial $tutorial, array $scopes, ?FilamentTutorialProgress $progress): ?array
    {
        if (! $this->tutorialMatchesScopes($tutorial, $scopes)) {
            return null;
        }

        return [
            'key' => $tutorial->getKey(),
            'autoStart' => $tutorial->shouldAutoStart() && ! in_array($progress?->status, [
                FilamentTutorialProgress::StatusCompleted,
                FilamentTutorialProgress::StatusDismissed,
            ], true),
            'adapter' => $tutorial->getAdapter(),
            'progressStatus' => $progress?->status,
            'steps' => array_map(
                fn (array $step): array => [
                    ...$step,
                    'selector' => $this->selectorForTarget($step['target'] ?? null, $scopes),
                ],
                array_map(
                    static fn (TutorialStep $step): array => $step->toArray(),
                    $tutorial->getSteps(),
                ),
            ),
        ];
    }

    /**
     * @param  array<int, string>  $scopes
     */
    private function tutorialMatchesScopes(FilamentTutorial $tutorial, array $scopes): bool
    {
        $page = $tutorial->getPage();

        return $page === null || in_array($page, $scopes, true);
    }

    /**
     * @param  array<string, mixed>|null  $target
     * @param  array<int, string>  $scopes
     */
    private function selectorForTarget(?array $target, array $scopes): ?string
    {
        if ($target === null) {
            return null;
        }

        $type = $target['type'] ?? null;
        $key = is_string($target['key'] ?? null) ? $target['key'] : null;
        $parameters = is_array($target['parameters'] ?? null) ? $target['parameters'] : [];

        $dataTour = match ($type) {
            'custom' => $key,
            'page' => TutorialTargetKeys::page($key ?? $this->currentPageFromScopes($scopes)),
            'navigation' => $key === null ? null : TutorialTargetKeys::navigation($key),
            'component' => $key === null ? null : TutorialTargetKeys::component($key),
            'action' => $key === null ? null : TutorialTargetKeys::action(
                $key,
                is_string($parameters['owner'] ?? null) ? $parameters['owner'] : null,
            ),
            'renderHook' => $key === null ? null : TutorialTargetKeys::renderHook($key),
            default => null,
        };

        return $dataTour === null ? null : sprintf('[data-tour="%s"]', addcslashes($dataTour, '"\\'));
    }

    /**
     * @param  array<int, string>  $scopes
     */
    private function currentPageFromScopes(array $scopes): ?string
    {
        foreach ($scopes as $scope) {
            if (is_subclass_of($scope, BasePage::class)) {
                return $scope;
            }
        }

        return null;
    }

    /**
     * Some topbar render hooks do not receive page scopes, so use the Filament page route action as the stable fallback.
     *
     * @param  array<int, string>  $scopes
     * @return array<int, string>
     */
    private function normalizeScopes(array $scopes): array
    {
        if ($scopes !== []) {
            return array_values(array_unique($scopes));
        }

        $routeAction = request()->route()?->getActionName();

        if (is_string($routeAction) && is_subclass_of($routeAction, BasePage::class)) {
            return [$routeAction];
        }

        return [];
    }

    /**
     * @return array{endpoint: string|null, progress: array<string, FilamentTutorialProgress>}
     */
    private function progressContext(string $panelId): array
    {
        if (! (bool) config('filament-tutorials.progress.enabled', true)) {
            return [
                'endpoint' => null,
                'progress' => [],
            ];
        }

        $authUser = $this->authenticatedUserResolver->resolve($panelId);

        if (! $authUser instanceof Authenticatable || ! $this->progressTableExists() || ! Route::has('filament-tutorials.progress')) {
            return [
                'endpoint' => null,
                'progress' => [],
            ];
        }

        $progress = FilamentTutorialProgress::query()
            ->where('user_type', $authUser::class)
            ->where('user_id', (string) $authUser->getAuthIdentifier())
            ->where('panel_id', $panelId)
            ->get()
            ->keyBy('tutorial_key')
            ->all();

        return [
            'endpoint' => route('filament-tutorials.progress', absolute: false),
            'progress' => $progress,
        ];
    }

    private function progressTableExists(): bool
    {
        return Schema::hasTable(config('filament-tutorials.progress.table', 'filament_tutorial_progress'));
    }
}
