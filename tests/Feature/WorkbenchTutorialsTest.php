<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\Support\TutorialManager;
use Filament\Facades\Filament;

it('exposes explicit discovered and inline tutorials in the workbench panel', function (): void {
    $tutorials = app(TutorialManager::class)->forPanel(Filament::getCurrentPanel());

    expect(array_keys($tutorials))->toContain(
        'explicit-workbench-tutorial',
        'discovered-workbench',
        'workbench-dashboard',
    );
});
