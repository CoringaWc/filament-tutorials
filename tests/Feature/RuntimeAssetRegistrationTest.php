<?php

declare(strict_types=1);

use Filament\Support\Facades\FilamentAsset;

it('registers package assets on the plugin panel', function (): void {
    expect(collect(FilamentAsset::getScripts(['coringawc/filament-tutorials']))->map->getId()->all())
        ->toContain('filament-tutorials')
        ->and(collect(FilamentAsset::getStyles(['coringawc/filament-tutorials']))->map->getId()->all())
        ->toContain('filament-tutorials');
});
