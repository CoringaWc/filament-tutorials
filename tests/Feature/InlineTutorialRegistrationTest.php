<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\Support\InlineTutorialCollector;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use Filament\Facades\Filament;
use Workbench\App\Filament\Pages\WorkbenchDashboard;

it('collects inline tutorials from panel pages', function (): void {
    $tutorials = app(InlineTutorialCollector::class)->collect(Filament::getCurrentPanel());

    expect($tutorials)
        ->toHaveCount(1)
        ->and($tutorials[0]->getKey())
        ->toBe('workbench-dashboard')
        ->and($tutorials[0]->getPage())
        ->toBe(WorkbenchDashboard::class);
});

it('registers inline page tutorials during panel boot', function (): void {
    $tutorial = app(TutorialManager::class)->find(Filament::getCurrentPanel(), 'workbench-dashboard');

    expect($tutorial)
        ->not->toBeNull()
        ->and($tutorial?->getPage())
        ->toBe(WorkbenchDashboard::class);
});
