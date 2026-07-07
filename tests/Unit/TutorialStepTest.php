<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\TutorialStep;

it('serializes stable target and lifecycle hooks', function (): void {
    $step = TutorialStep::make('create-action')
        ->targetAction('create')
        ->title('Create')
        ->description('Start a new record.')
        ->beforeOpenSidebar()
        ->beforeOpenModal(['trigger' => 'create']);

    expect($step->toArray())
        ->toMatchArray([
            'key' => 'create-action',
            'target' => [
                'type' => 'action',
                'key' => 'create',
                'parameters' => [
                    'owner' => null,
                ],
            ],
            'before' => [
                ['action' => 'sidebar.open', 'parameters' => []],
                ['action' => 'modal.open', 'parameters' => ['trigger' => 'create']],
            ],
        ]);
});
