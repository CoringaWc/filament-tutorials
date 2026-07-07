<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Widgets;

use CoringaWc\FilamentTutorials\TutorialTargetAttributes;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TutorialStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Indicadores do laboratório';

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('Tutoriais', 8)
                ->description('Fluxos cobertos')
                ->extraAttributes(TutorialTargetAttributes::component('workbench.widget.stats')),
            Stat::make('Alvos', 18)
                ->description('Elementos rastreáveis'),
        ];
    }
}
