<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\FilamentTutorialsPlugin;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
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

it('uses the user menu launcher position by default', function (): void {
    expect(config('filament-tutorials.launcher'))
        ->toMatchArray([
            'enabled' => true,
            'render_hook' => PanelsRenderHook::USER_MENU_BEFORE,
            'icon' => Heroicon::QuestionMarkCircle,
            'label' => 'Abrir tutorial da página',
            'tooltip' => 'Abrir tutorial da página',
        ])
        ->and(config('filament-tutorials.launcher_render_hook'))
        ->toBeNull();
});
