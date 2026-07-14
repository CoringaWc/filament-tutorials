<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\Actions\RecordTutorialProgressAction;
use Illuminate\Support\Facades\Route;

Route::middleware(config('filament-tutorials.progress.middleware', ['web', 'throttle:filament-tutorials-progress']))
    ->post(config('filament-tutorials.progress.route_path', 'filament-tutorials/progress'), RecordTutorialProgressAction::class)
    ->name('filament-tutorials.progress');
