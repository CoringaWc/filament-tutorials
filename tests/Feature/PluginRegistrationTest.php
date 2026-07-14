<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\FilamentTutorialsPlugin;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Workbench\App\Filament\Pages\WorkbenchDashboard;

it('registers tutorials for the current panel', function (): void {
    $panel = Filament::getCurrentPanel();

    assert($panel instanceof Panel);

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

it('keeps the panel tutorial registry after scoped instances are flushed', function (): void {
    $panel = Filament::getCurrentPanel();

    assert($panel instanceof Panel);

    expect(app(TutorialManager::class)->forPanel($panel))
        ->toHaveKey('workbench-dashboard');

    app()->forgetScopedInstances();

    expect(app(TutorialManager::class)->forPanel($panel))
        ->toHaveKey('workbench-dashboard');
});

it('does not duplicate inline tutorials when the panel boots again', function (): void {
    $panel = Filament::getCurrentPanel();

    assert($panel instanceof Panel);

    $plugin = $panel->getPlugin('filament-tutorials');

    expect(fn () => $plugin->boot($panel))
        ->not->toThrow(Throwable::class)
        ->and(app(TutorialManager::class)->forPanel($panel))
        ->toHaveKey('workbench-dashboard')
        ->toHaveKey('tutorial-record-list');
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

it('protects the progress endpoint with the web stack and a named rate limiter', function (): void {
    expect(config('filament-tutorials.progress.middleware'))
        ->toBe(['web', 'throttle:filament-tutorials-progress'])
        ->and(config('filament-tutorials.progress.rate_limit'))
        ->toBe([
            'max_attempts' => 120,
            'decay_seconds' => 60,
        ]);
});

it('enables dismissal reminder by default and allows panel-level overrides', function (): void {
    expect(config('filament-tutorials.dismissal_reminder'))
        ->toMatchArray([
            'enabled' => true,
            'step_key' => 'reopen-page-tutorial',
            'skip_label' => 'Ignorar',
            'title' => 'Você pode voltar quando quiser',
            'description' => 'Para rever este guia, clique no ícone de interrogação no topo da página.',
        ]);

    $plugin = FilamentTutorialsPlugin::make()
        ->dismissalReminder(
            skipLabel: 'Agora não',
            title: 'Retome quando precisar',
            description: 'Abra novamente pelo botão de ajuda.',
        );

    expect($plugin->getDismissalReminderPayload())
        ->toMatchArray([
            'enabled' => true,
            'selector' => '[data-tour="tutorial.launcher"]',
            'stepKey' => 'reopen-page-tutorial',
            'skipLabel' => 'Agora não',
            'title' => 'Retome quando precisar',
            'description' => 'Abra novamente pelo botão de ajuda.',
        ])
        ->and($plugin->withoutDismissalReminder()->getDismissalReminderPayload()['enabled'])
        ->toBeFalse();
});
