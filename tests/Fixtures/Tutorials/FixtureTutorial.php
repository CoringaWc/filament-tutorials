<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Tests\Fixtures\Tutorials;

use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;
use Workbench\App\Filament\Pages\WorkbenchDashboard;

class FixtureTutorial extends FilamentTutorial
{
    protected static ?string $page = WorkbenchDashboard::class;

    public function steps(?array $steps = null): array|static
    {
        if ($steps !== null) {
            return parent::steps($steps);
        }

        return [
            TutorialStep::make('fixture-step')
                ->targetPage(WorkbenchDashboard::class),
        ];
    }
}
