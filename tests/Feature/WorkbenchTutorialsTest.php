<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\Support\TutorialManager;
use Filament\Facades\Filament;
use Filament\Panel;

it('exposes explicit discovered and inline tutorials in the workbench panel', function (): void {
    $panel = Filament::getCurrentPanel();

    assert($panel instanceof Panel);

    $tutorials = app(TutorialManager::class)->forPanel($panel);

    expect(array_keys($tutorials))->toContain(
        'explicit-workbench-tutorial',
        'discovered-workbench',
        'workbench-dashboard',
    );
});
