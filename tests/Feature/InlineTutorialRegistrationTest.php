<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\Support\InlineTutorialCollector;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use Filament\Facades\Filament;
use Filament\Panel;
use Workbench\App\Filament\Pages\WorkbenchDashboard;
use Workbench\App\Filament\Resources\TutorialRecords\Pages\ListTutorialRecords;
use Workbench\App\Filament\Resources\TutorialRecords\Pages\ManageTutorialRecordRelations;
use Workbench\App\Filament\Resources\TutorialRecords\TutorialRecordResource;

it('collects inline tutorials from panel pages resources and resource pages', function (): void {
    $panel = Filament::getCurrentPanel();

    assert($panel instanceof Panel);

    $tutorials = app(InlineTutorialCollector::class)->collect($panel);

    $tutorialsByKey = collect($tutorials)->keyBy(fn ($tutorial) => $tutorial->getKey());

    expect($tutorialsByKey->keys()->all())
        ->toContain(
            'workbench-dashboard',
            'tutorial-record-resource',
            'tutorial-record-list',
            'tutorial-record-relations',
        )
        ->and($tutorialsByKey->get('workbench-dashboard')?->getPage())
        ->toBe(WorkbenchDashboard::class)
        ->and($tutorialsByKey->get('tutorial-record-resource')?->getPage())
        ->toBe(TutorialRecordResource::class)
        ->and($tutorialsByKey->get('tutorial-record-list')?->getPage())
        ->toBe(ListTutorialRecords::class)
        ->and($tutorialsByKey->get('tutorial-record-relations')?->getPage())
        ->toBe(ManageTutorialRecordRelations::class);
});

it('registers inline page tutorials during panel boot', function (): void {
    $panel = Filament::getCurrentPanel();

    assert($panel instanceof Panel);

    $tutorial = app(TutorialManager::class)->find($panel, 'workbench-dashboard');

    expect($tutorial)
        ->not->toBeNull()
        ->and($tutorial?->getPage())
        ->toBe(WorkbenchDashboard::class);
});
