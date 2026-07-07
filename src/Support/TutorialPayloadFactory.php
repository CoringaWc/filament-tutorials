<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Support;

use CoringaWc\FilamentTutorials\FilamentTutorial;
use Filament\Pages\BasePage;

class TutorialPayloadFactory
{
    public function __construct(
        private readonly TutorialManager $tutorialManager,
    ) {}

    /**
     * @param  array<int, string>  $scopes
     * @return array{labels: array<string, string>, tutorials: list<array<string, mixed>>}
     */
    public function forPanelAndScopes(string $panelId, array $scopes): array
    {
        return [
            'labels' => [
                'next' => __('Próximo'),
                'previous' => __('Anterior'),
                'done' => __('Concluir'),
                'progress' => __('{{current}} de {{total}}'),
            ],
            'tutorials' => array_values(array_filter(array_map(
                fn (FilamentTutorial $tutorial): ?array => $this->tutorialPayload($tutorial, $scopes),
                $this->tutorialManager->forPanel($panelId),
            ))),
        ];
    }

    /**
     * @param  array<int, string>  $scopes
     * @return array<string, mixed>|null
     */
    private function tutorialPayload(FilamentTutorial $tutorial, array $scopes): ?array
    {
        if (! $this->tutorialMatchesScopes($tutorial, $scopes)) {
            return null;
        }

        return [
            'key' => $tutorial->getKey(),
            'autoStart' => $tutorial->shouldAutoStart(),
            'adapter' => $tutorial->getAdapter(),
            'steps' => array_map(
                fn (array $step): array => [
                    ...$step,
                    'selector' => $this->selectorForTarget($step['target'] ?? null, $scopes),
                ],
                array_map(
                    static fn ($step): array => $step->toArray(),
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
}
