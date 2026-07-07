<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\Support\TutorialManager;

it('keeps tutorial registrations isolated by panel id', function (): void {
    $manager = app(TutorialManager::class);

    $manager->register('first-panel', [
        FilamentTutorial::make('shared-key'),
    ]);

    $manager->register('second-panel', [
        FilamentTutorial::make('shared-key'),
    ]);

    expect($manager->forPanel('first-panel'))
        ->toHaveKey('shared-key')
        ->and($manager->forPanel('second-panel'))
        ->toHaveKey('shared-key')
        ->and($manager->find('missing-panel', 'shared-key'))
        ->toBeNull();
});

it('fails fast when a tutorial key is duplicated inside one panel', function (): void {
    $manager = app(TutorialManager::class);

    $manager->register('duplicate-panel', [
        FilamentTutorial::make('duplicated'),
    ]);

    $manager->register('duplicate-panel', [
        FilamentTutorial::make('duplicated'),
    ]);
})->throws(LogicException::class, 'Tutorial [duplicated] is already registered for panel [duplicate-panel].');
