<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\FilamentTutorialsPlugin;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use Filament\Facades\Filament;
use Workbench\App\Filament\Pages\WorkbenchDashboard;

it('registers tutorials for the current panel', function (): void {
    $panel = Filament::getCurrentPanel();

    $tutorials = app(TutorialManager::class)->forPanel($panel);

    expect($tutorials)
        ->toHaveKey('workbench-dashboard')
        ->toHaveKey('discovered-workbench')
        ->toHaveKey('explicit-workbench-tutorial')
        ->toHaveKey('tutorial-record-resource')
        ->toHaveKey('tutorial-record-list')
        ->toHaveKey('tutorial-record-relations')
        ->and($tutorials['workbench-dashboard']->getPage())
        ->toBe(WorkbenchDashboard::class)
        ->and($tutorials['discovered-workbench']->getPage())
        ->toBe(WorkbenchDashboard::class)
        ->and($tutorials['explicit-workbench-tutorial']->getPage())
        ->toBe(WorkbenchDashboard::class)
        ->and(FilamentTutorialsPlugin::make()->getId())
        ->toBe('filament-tutorials');
});
