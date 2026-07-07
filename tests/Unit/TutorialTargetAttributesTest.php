<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\TutorialTargetAttributes;
use Workbench\App\Filament\Pages\WorkbenchDashboard;

it('builds stable data-tour attributes for common targets', function (): void {
    expect(TutorialTargetAttributes::component('workbench.card'))
        ->toBe(['data-tour' => 'filament-tutorials.component.workbench.card'])
        ->and(TutorialTargetAttributes::action('create', WorkbenchDashboard::class))
        ->toHaveKey('data-tour')
        ->and(TutorialTargetAttributes::page(WorkbenchDashboard::class))
        ->toHaveKey('data-tour')
        ->and(TutorialTargetAttributes::renderHook('global-search.before'))
        ->toBe(['data-tour' => 'filament-tutorials.render-hook.global-search.before']);
});

it('builds a selector from a generated target attribute', function (): void {
    expect(TutorialTargetAttributes::selector(TutorialTargetAttributes::component('workbench.card')))
        ->toBe('[data-tour="filament-tutorials.component.workbench.card"]');
});
