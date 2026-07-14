<?php

declare(strict_types=1);

it('keeps the runtime on public APIs and away from fragile selectors', function (): void {
    $runtime = file_get_contents(__DIR__.'/../../resources/js/filament-tutorials.js');
    $styles = file_get_contents(__DIR__.'/../../resources/css/filament-tutorials.css');
    $targetMarkerView = file_get_contents(__DIR__.'/../../resources/views/target-marker.blade.php');

    expect($runtime)
        ->toContain("from 'driver.js'")
        ->toContain('MutationObserver')
        ->toContain(':not(.filament-tutorials-popover)')
        ->toContain('window.setTimeout(finish, 100)')
        ->toContain('const actionTargetTimeout = 10000')
        ->toContain('waitForElement(modalSelector, actionTargetTimeout, visibleStructuralElement)')
        ->toContain('waitForElement(parameters.selector, actionTargetTimeout)')
        ->toContain("currentElement.getAttribute('aria-hidden') === 'true'")
        ->toContain("styles.visibility === 'hidden'")
        ->toContain('Number.parseFloat(styles.opacity) === 0')
        ->toContain('const escapeHtml')
        ->toContain('title: escapeHtml(step.title)')
        ->toContain('description: escapeHtml(step.description)')
        ->toContain('doneBtnText: escapedLabels.done')
        ->toContain('meta[name="csrf-token"]')
        ->toContain('keepalive: true')
        ->toContain('responsePayload?.recorded !== true')
        ->toContain("'filament-tutorials:progress-failed'")
        ->toContain("document.addEventListener('livewire:navigating', destroyActiveDriver)");

    expect(str_contains((string) $runtime, 'progress.csrfToken'))->toBeFalse();

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
        ->toContain('@import "driver.js/dist/driver.css"')
        ->toContain('var(--primary-700')
        ->toContain('var(--gray-900')
        ->toContain('[data-filament-tutorials-target-marker]')
        ->toContain('.filament-tutorials-popover .filament-tutorials-skip-btn')
        ->toContain(':is(.driver-popover-prev-btn, .filament-tutorials-skip-btn)');

    expect(str_contains((string) $styles, '.fi-'))->toBeFalse()
        ->and(str_contains((string) $targetMarkerView, 'style='))->toBeFalse();
});

it('keeps the progress identity index compatible with MySQL utf8mb4 limits', function (): void {
    $migration = file_get_contents(__DIR__.'/../../database/migrations/create_filament_tutorial_progress_table.php');

    expect($migration)
        ->toContain("string('user_type', 191)")
        ->toContain("string('user_id', 191)")
        ->toContain("string('panel_id', 64)")
        ->toContain("string('tutorial_key', 191)");
});

it('builds distributable package assets', function (): void {
    expect(file_exists(__DIR__.'/../../resources/dist/filament-tutorials.js'))
        ->toBeTrue()
        ->and(file_exists(__DIR__.'/../../resources/dist/filament-tutorials.css'))
        ->toBeTrue();
});
