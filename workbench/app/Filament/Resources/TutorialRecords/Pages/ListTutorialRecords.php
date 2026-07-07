<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\TutorialRecords\Pages;

use CoringaWc\FilamentTutorials\Contracts\HasFilamentTutorials;
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;
use CoringaWc\FilamentTutorials\TutorialTargetAttributes;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Workbench\App\Filament\Resources\TutorialRecords\TutorialRecordResource;

class ListTutorialRecords extends ListRecords implements HasFilamentTutorials
{
    protected static string $resource = TutorialRecordResource::class;

    /**
     * @return array<int, CreateAction>
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->extraAttributes(TutorialTargetAttributes::action('create', static::class))
                ->extraModalWindowAttributes([
                    'data-tour' => 'workbench.resource.create-modal',
                ], merge: true),
        ];
    }

    public static function tutorials(): FilamentTutorial
    {
        return FilamentTutorial::make('tutorial-record-list')
            ->forPage(static::class)
            ->steps([
                TutorialStep::make('list-page')
                    ->targetPage(static::class)
                    ->title('Lista de registros')
                    ->description('A lista reúne registros de teste para validar tutoriais em Resources.'),
                TutorialStep::make('list-table')
                    ->targetRenderHook('resource.list.table.before')
                    ->title('Tabela do Resource')
                    ->description('Este alvo usa um render hook público antes da tabela do Resource.'),
                TutorialStep::make('create-action')
                    ->targetAction('create', static::class)
                    ->title('Novo registro')
                    ->description('A ação abre o cadastro em modal, como acontece nos fluxos modernos do painel.'),
                TutorialStep::make('create-modal')
                    ->target('workbench.resource.create-modal')
                    ->title('Modal de cadastro')
                    ->description('O tutorial aguarda a abertura do modal antes de destacar o formulário.')
                    ->beforeOpenModal(['selector' => TutorialTargetAttributes::selector(TutorialTargetAttributes::action('create', static::class))]),
                TutorialStep::make('form-field')
                    ->targetComponent('workbench.resource.form.title')
                    ->title('Campo do formulário')
                    ->description('Campos internos usam data-tour explícito no componente dono do campo.')
                    ->beforeOpenModal(['selector' => TutorialTargetAttributes::selector(TutorialTargetAttributes::action('create', static::class))]),
            ]);
    }
}
