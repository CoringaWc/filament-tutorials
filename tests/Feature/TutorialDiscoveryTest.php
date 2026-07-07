<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\FilamentTutorialsPlugin;
use CoringaWc\FilamentTutorials\Support\TutorialDiscovery;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use CoringaWc\FilamentTutorials\Tests\Fixtures\Tutorials\FixtureTutorial;
use Filament\Panel;
use Workbench\App\Filament\Admin\Tutorials\DiscoveredWorkbenchTutorial;

it('discovers tutorial classes from a path and namespace', function (): void {
    $tutorials = app(TutorialDiscovery::class)->discover(
        dirname(__DIR__).'/Fixtures/Tutorials',
        'CoringaWc\\FilamentTutorials\\Tests\\Fixtures\\Tutorials',
    );

    expect($tutorials)->toBe([FixtureTutorial::class]);
});

it('registers tutorials from a custom discovery location', function (): void {
    $panel = Panel::make()->id('custom-discovery');

    FilamentTutorialsPlugin::make()
        ->discoverTutorials(
            in: dirname(__DIR__).'/Fixtures/Tutorials',
            for: 'CoringaWc\\FilamentTutorials\\Tests\\Fixtures\\Tutorials',
        )
        ->register($panel);

    expect(app(TutorialManager::class)->forPanel($panel))
        ->toHaveKey('fixture');
});

it('does not register discovered tutorials when discovery is disabled', function (): void {
    $panel = Panel::make()->id('disabled-discovery');

    FilamentTutorialsPlugin::make()
        ->discoverTutorials(
            in: dirname(__DIR__).'/Fixtures/Tutorials',
            for: 'CoringaWc\\FilamentTutorials\\Tests\\Fixtures\\Tutorials',
            enabled: false,
        )
        ->register($panel);

    expect(app(TutorialManager::class)->forPanel($panel))
        ->not->toHaveKey('fixture')
        ->toBeEmpty();
});

it('uses the panel convention as the default discovery location', function (): void {
    $tutorials = app(TutorialDiscovery::class)->discover(
        dirname(__DIR__, 2).'/workbench/app/Filament/Admin/Tutorials',
        'Workbench\\App\\Filament\\Admin\\Tutorials',
    );

    expect($tutorials)->toBe([DiscoveredWorkbenchTutorial::class]);
});
