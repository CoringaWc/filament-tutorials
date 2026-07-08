<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\TutorialStep;
use Workbench\App\Filament\Pages\WorkbenchDashboard;

it('serializes stable target and lifecycle hooks', function (): void {
    $step = TutorialStep::make('create-action')
        ->targetAction('create', WorkbenchDashboard::class)
        ->title('Create')
        ->description('Start a new record.')
        ->optional()
        ->beforeOpenSidebar()
        ->afterOpenSidebar()
        ->beforeOpenProfileMenu()
        ->beforeOpenModal(['trigger' => 'create']);

    expect($step->toArray())
        ->toMatchArray([
            'key' => 'create-action',
            'target' => [
                'type' => 'action',
                'key' => 'create',
                'parameters' => [
                    'owner' => WorkbenchDashboard::class,
                ],
            ],
            'before' => [
                ['action' => 'sidebar.open', 'parameters' => []],
                ['action' => 'profile-menu.open', 'parameters' => []],
                ['action' => 'modal.open', 'parameters' => ['trigger' => 'create']],
            ],
            'after' => [
                ['action' => 'sidebar.opened', 'parameters' => []],
            ],
            'optional' => true,
        ]);
});

it('serializes navigation and page helper targets', function (): void {
    expect(TutorialStep::make('navigation')->targetNavigation(WorkbenchDashboard::class)->toArray())
        ->toMatchArray([
            'target' => [
                'type' => 'navigation',
                'key' => WorkbenchDashboard::class,
                'parameters' => [],
            ],
        ])
        ->and(TutorialStep::make('page')->targetPage(WorkbenchDashboard::class)->toArray())
        ->toMatchArray([
            'target' => [
                'type' => 'page',
                'key' => WorkbenchDashboard::class,
                'parameters' => [],
            ],
        ])
        ->and(TutorialStep::make('component')->targetComponent('workbench.card')->toArray())
        ->toMatchArray([
            'target' => [
                'type' => 'component',
                'key' => 'workbench.card',
                'parameters' => [],
            ],
        ]);
});
