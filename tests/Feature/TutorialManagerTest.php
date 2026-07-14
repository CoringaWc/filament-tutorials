<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use CoringaWc\FilamentTutorials\TutorialStep;

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

it('fails fast when a tutorial progress key cannot be persisted', function (): void {
    app(TutorialManager::class)->register('admin', [
        FilamentTutorial::make('Invalid tutorial key'),
    ]);
})->throws(InvalidArgumentException::class, 'Tutorial [Invalid tutorial key] has an invalid progress key.');

it('fails fast when a tutorial step progress key cannot be persisted', function (): void {
    app(TutorialManager::class)->register('admin', [
        FilamentTutorial::make('valid-tutorial')->steps([
            TutorialStep::make('Invalid step key'),
        ]),
    ]);
})->throws(InvalidArgumentException::class, 'Tutorial step [Invalid step key] has an invalid progress key.');

it('fails fast when a tutorial contains duplicated step keys', function (): void {
    app(TutorialManager::class)->register('admin', [
        FilamentTutorial::make('valid-tutorial')->steps([
            TutorialStep::make('duplicated-step'),
            TutorialStep::make('duplicated-step'),
        ]),
    ]);
})->throws(LogicException::class, 'Tutorial step [duplicated-step] is duplicated in tutorial [valid-tutorial].');
