<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\TutorialRecords\Pages;

use CoringaWc\FilamentTutorials\Contracts\HasFilamentTutorials;
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Workbench\App\Filament\Resources\TutorialRecords\TutorialRecordResource;

class ManageTutorialRecordRelations extends Page implements HasFilamentTutorials
{
    protected static string $resource = TutorialRecordResource::class;

    protected string $view = 'filament-panels::pages.page';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contexto relacionado')
                    ->description('Esta página simula uma área relacionada ou aninhada de um Resource.')
                    ->schema([
                        Html::make(<<<'HTML'
                            <div data-tour="workbench.relation.context" class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-700 dark:border-white/10 dark:bg-gray-900 dark:text-gray-200">
                                Registro principal carregado para o laboratório de tutoriais.
                            </div>
                            <div data-tour="workbench.relation.table" class="mt-4 overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 text-gray-600 dark:bg-white/5 dark:text-gray-300">
                                        <tr>
                                            <th class="px-3 py-2">Item relacionado</th>
                                            <th class="px-3 py-2">Situação</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                        <tr>
                                            <td class="px-3 py-2">Vínculo de exemplo</td>
                                            <td class="px-3 py-2">Ativo</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        HTML),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function tutorials(): FilamentTutorial
    {
        return FilamentTutorial::make('tutorial-record-relations')
            ->forPage(static::class)
            ->steps([
                TutorialStep::make('relation-context')
                    ->target('workbench.relation.context')
                    ->title('Contexto relacionado')
                    ->description('Páginas relacionadas também podem ter tutoriais próprios.'),
                TutorialStep::make('relation-table')
                    ->target('workbench.relation.table')
                    ->title('Tabela relacionada')
                    ->description('Use este padrão para páginas de relacionamento ou nested pages.'),
            ]);
    }
}
