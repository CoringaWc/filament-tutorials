<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\FilamentTutorialsPlugin;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use Filament\Facades\Filament;

it('registers tutorials for the current panel', function (): void {
    $panel = Filament::getCurrentPanel();

    $tutorials = app(TutorialManager::class)->forPanel($panel);

    expect($tutorials)
        ->toHaveKey('workbench-dashboard')
        ->and(FilamentTutorialsPlugin::make()->getId())
        ->toBe('filament-tutorials');
});
