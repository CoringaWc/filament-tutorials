<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\Tests\Fixtures\Tutorials\FixtureTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;
use Workbench\App\Filament\Pages\WorkbenchDashboard;

it('derives a stable key from the class name', function (): void {
    expect(FixtureTutorial::make()->getKey())->toBe('fixture');
});

it('serializes the public tutorial payload', function (): void {
    $tutorial = FilamentTutorial::make('contracting-list')
        ->forPage(WorkbenchDashboard::class)
        ->autoStart()
        ->adapter('contractingList')
        ->steps([
            TutorialStep::make('intro')
                ->targetPage()
                ->title('Intro')
                ->description('Start here.'),
        ]);

    expect($tutorial->toArray())->toMatchArray([
        'key' => 'contracting-list',
        'page' => WorkbenchDashboard::class,
        'autoStart' => true,
        'adapter' => 'contractingList',
        'steps' => [
            [
                'key' => 'intro',
                'target' => [
                    'type' => 'page',
                    'key' => null,
                    'parameters' => [],
                ],
                'title' => 'Intro',
                'description' => 'Start here.',
                'before' => [],
                'after' => [],
            ],
        ],
    ]);
});

it('uses the inherited static page when no page override is configured', function (): void {
    expect(FixtureTutorial::make()->getPage())->toBe(WorkbenchDashboard::class);
});
