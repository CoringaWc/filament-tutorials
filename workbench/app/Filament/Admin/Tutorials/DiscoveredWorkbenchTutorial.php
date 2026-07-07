<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Admin\Tutorials;

use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;
use Workbench\App\Filament\Pages\WorkbenchDashboard;

class DiscoveredWorkbenchTutorial extends FilamentTutorial
{
    protected static ?string $page = WorkbenchDashboard::class;

    public function steps(?array $steps = null): array|static
    {
        if ($steps !== null) {
            return parent::steps($steps);
        }

        return [
            TutorialStep::make('discovered-workbench-step')
                ->targetPage(WorkbenchDashboard::class)
                ->title('Discovered tutorial')
                ->description('This tutorial proves panel convention discovery.'),
        ];
    }
}
