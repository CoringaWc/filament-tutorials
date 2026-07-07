<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\Support\InlineTutorialCollector;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use Filament\Facades\Filament;
use Workbench\App\Filament\Pages\WorkbenchDashboard;
use Workbench\App\Filament\Resources\TutorialRecords\Pages\ListTutorialRecords;
use Workbench\App\Filament\Resources\TutorialRecords\Pages\ManageTutorialRecordRelations;
use Workbench\App\Filament\Resources\TutorialRecords\TutorialRecordResource;

it('collects inline tutorials from panel pages resources and resource pages', function (): void {
    $tutorials = app(InlineTutorialCollector::class)->collect(Filament::getCurrentPanel());

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
    $tutorial = app(TutorialManager::class)->find(Filament::getCurrentPanel(), 'workbench-dashboard');

    expect($tutorial)
        ->not->toBeNull()
        ->and($tutorial?->getPage())
        ->toBe(WorkbenchDashboard::class);
});
