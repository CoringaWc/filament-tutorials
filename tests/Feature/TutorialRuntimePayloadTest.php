<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\Support\TutorialPayloadFactory;
use Filament\Facades\Filament;
use Workbench\App\Filament\Pages\WorkbenchDashboard;
use Workbench\App\Filament\Resources\TutorialRecords\Pages\ListTutorialRecords;

it('builds localized payload for the current page scope only', function (): void {
    $payload = app(TutorialPayloadFactory::class)->forPanelAndScopes('admin', [
        WorkbenchDashboard::class,
    ]);

    expect($payload['labels'])
        ->toMatchArray([
            'next' => 'Próximo',
            'previous' => 'Anterior',
            'done' => 'Concluir',
            'progress' => '{{current}} de {{total}}',
        ]);

    $tutorialKeys = array_map(
        static fn (array $tutorial): mixed => $tutorial['key'] ?? null,
        $payload['tutorials'],
    );

    expect($tutorialKeys)->toContain('workbench-dashboard');
    expect(in_array('tutorial-record-list', $tutorialKeys, true))->toBeFalse();
});

it('resolves stable selectors for page action and render hook targets', function (): void {
    $payload = app(TutorialPayloadFactory::class)->forPanelAndScopes(Filament::getCurrentPanel()->getId(), [
        ListTutorialRecords::class,
    ]);

    $listTutorial = null;

    foreach ($payload['tutorials'] as $tutorial) {
        if (($tutorial['key'] ?? null) === 'tutorial-record-list') {
            $listTutorial = $tutorial;

            break;
        }
    }

    expect(is_array($listTutorial))->toBeTrue();

    $steps = is_array($listTutorial['steps'] ?? null) ? $listTutorial['steps'] : [];
    $selectors = array_map(
        static fn (array $step): mixed => $step['selector'] ?? null,
        $steps,
    );

    expect($selectors)
        ->toContain('[data-tour="filament-tutorials.render-hook.resource.list.table.before"]')
        ->toContain('[data-tour="workbench.resource.create-modal"]');
});
