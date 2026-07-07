<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\TutorialRecords;

use BackedEnum;
use CoringaWc\FilamentTutorials\Contracts\HasFilamentTutorials;
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;
use CoringaWc\FilamentTutorials\TutorialTargetAttributes;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\TutorialRecords\Pages\ListTutorialRecords;
use Workbench\App\Filament\Resources\TutorialRecords\Pages\ManageTutorialRecordRelations;
use Workbench\App\Models\TutorialRecord;

class TutorialRecordResource extends Resource implements HasFilamentTutorials
{
    protected static ?string $model = TutorialRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    public static function getNavigationLabel(): string
    {
        return 'Laboratório de tutoriais';
    }

    public static function getModelLabel(): string
    {
        return 'Registro de tutorial';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Registros de tutorial';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados principais')
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->extraInputAttributes(TutorialTargetAttributes::component('workbench.resource.form.title')),
                        Select::make('status')
                            ->label('Situação')
                            ->options([
                                'draft' => 'Rascunho',
                                'ready' => 'Pronto',
                                'archived' => 'Arquivado',
                            ])
                            ->required()
                            ->extraInputAttributes(TutorialTargetAttributes::component('workbench.resource.form.status')),
                        Textarea::make('summary')
                            ->label('Resumo')
                            ->columnSpanFull()
                            ->extraInputAttributes(TutorialTargetAttributes::component('workbench.resource.form.summary')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->extraHeaderAttributes(TutorialTargetAttributes::component('workbench.resource.table.title')),
                TextColumn::make('status')
                    ->label('Situação')
                    ->badge()
                    ->extraHeaderAttributes(TutorialTargetAttributes::component('workbench.resource.table.status')),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->recordActions([
                EditAction::make()
                    ->extraAttributes(['data-tour' => 'workbench.resource.row-action']),
            ]);
    }

    public static function tutorials(): FilamentTutorial
    {
        return FilamentTutorial::make('tutorial-record-resource')
            ->forPage(static::class)
            ->steps([
                TutorialStep::make('resource-navigation')
                    ->targetRenderHook('sidebar.nav.start')
                    ->title('Navegação do módulo')
                    ->description('Use a navegação lateral para acessar as áreas do painel.'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTutorialRecords::route('/'),
            'relations' => ManageTutorialRecordRelations::route('/{record}/relacionados'),
        ];
    }
}
