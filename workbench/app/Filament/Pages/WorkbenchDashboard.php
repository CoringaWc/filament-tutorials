<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Pages;

use BackedEnum;
use CoringaWc\FilamentTutorials\Contracts\HasFilamentTutorials;
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class WorkbenchDashboard extends Page implements HasFilamentTutorials
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected string $view = 'filament-tutorials::workbench-dashboard';

    public static function tutorials(): FilamentTutorial
    {
        return FilamentTutorial::make('workbench-dashboard')
            ->forPage(static::class)
            ->steps([
                TutorialStep::make('dashboard-intro')
                    ->targetPage(static::class)
                    ->title('Workbench dashboard')
                    ->description('This page proves inline tutorial registration from a Filament page.'),
            ]);
    }
}
