<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\TutorialTarget;
use Workbench\App\Filament\Pages\WorkbenchDashboard;

it('serializes custom targets', function (): void {
    expect(TutorialTarget::custom('custom.key')->toArray())->toBe([
        'type' => 'custom',
        'key' => 'custom.key',
        'parameters' => [],
    ]);
});

it('serializes navigation targets', function (): void {
    expect(TutorialTarget::navigation(WorkbenchDashboard::class)->toArray())->toBe([
        'type' => 'navigation',
        'key' => WorkbenchDashboard::class,
        'parameters' => [],
    ]);
});

it('serializes render hook targets', function (): void {
    expect(TutorialTarget::renderHook('global-search.before')->toArray())->toBe([
        'type' => 'renderHook',
        'key' => 'global-search.before',
        'parameters' => [],
    ]);
});

it('serializes action targets', function (): void {
    expect(TutorialTarget::action('create', WorkbenchDashboard::class)->toArray())->toBe([
        'type' => 'action',
        'key' => 'create',
        'parameters' => [
            'owner' => WorkbenchDashboard::class,
        ],
    ]);
});

it('serializes page targets', function (): void {
    expect(TutorialTarget::page(WorkbenchDashboard::class)->toArray())->toBe([
        'type' => 'page',
        'key' => WorkbenchDashboard::class,
        'parameters' => [],
    ]);
});
