<?php

declare(strict_types=1);

it('documents the complete first tutorial workflow', function (): void {
    $readme = file_get_contents(__DIR__.'/../../README.md');

    expect($readme)
        ->toContain('does not currently provide a dedicated')
        ->toContain('php artisan make:class Filament/App/Tutorials/ContractingListTutorial --no-interaction')
        ->toContain('app/Filament/App/Tutorials/ContractingListTutorial.php')
        ->toContain('namespace App\Filament\App\Tutorials;')
        ->toContain('No additional registration is necessary for this class.')
        ->toContain('## Troubleshooting');
});

it('distinguishes tutorial keys step keys and target keys', function (): void {
    $readme = file_get_contents(__DIR__.'/../../README.md');

    expect($readme)
        ->toContain("TutorialStep::make('contracting-draft-page')")
        ->toContain('You do **not** need to write `page` inside `make()`')
        ->toContain('| Tutorial key |')
        ->toContain('| Step key |')
        ->toContain('| Target key |');

    expect(str_contains((string) $readme, "TutorialStep::make('page')"))->toBeFalse();
});

it('uses valid stable keys throughout the documented tutorial examples', function (): void {
    $readme = file_get_contents(__DIR__.'/../../README.md');

    preg_match_all("/TutorialStep::make\('([^']+)'\)/", (string) $readme, $matches);

    expect($matches[1])->not->toBeEmpty();

    foreach ($matches[1] as $stepKey) {
        expect($stepKey)->toMatch('/^[a-z0-9][a-z0-9._-]*$/');
    }
});

it('keeps every Markdown code fence balanced', function (): void {
    $readme = file_get_contents(__DIR__.'/../../README.md');

    preg_match_all('/^```/m', (string) $readme, $matches);

    expect(count($matches[0]) % 2)->toBe(0);
});
