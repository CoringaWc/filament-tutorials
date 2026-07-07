<?php

declare(strict_types=1);

it('keeps the runtime on public APIs and away from fragile selectors', function (): void {
    $runtime = file_get_contents(__DIR__.'/../../resources/js/filament-tutorials.js');
    $styles = file_get_contents(__DIR__.'/../../resources/css/filament-tutorials.css');

    expect($runtime)
        ->toContain("from 'driver.js'")
        ->toContain('MutationObserver');

    foreach ([
        'setInterval',
        'wire:poll',
        '.fi-',
        'document.cookie',
        'localStorage',
        'sessionStorage',
        'access_token',
        'refresh_token',
    ] as $forbiddenFragment) {
        expect(str_contains((string) $runtime, $forbiddenFragment))->toBeFalse();
    }

    expect($styles)
        ->toContain('@import "driver.js/dist/driver.css"');

    expect(str_contains((string) $styles, '.fi-'))->toBeFalse();
});

it('builds distributable package assets', function (): void {
    expect(file_exists(__DIR__.'/../../resources/dist/filament-tutorials.js'))
        ->toBeTrue()
        ->and(file_exists(__DIR__.'/../../resources/dist/filament-tutorials.css'))
        ->toBeTrue();
});
