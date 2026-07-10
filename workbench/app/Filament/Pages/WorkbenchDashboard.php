<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Pages;

use BackedEnum;
use CoringaWc\FilamentTutorials\Contracts\HasFilamentTutorials;
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\Support\TutorialTargetKeys;
use CoringaWc\FilamentTutorials\TutorialStep;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Workbench\App\Filament\Widgets\TutorialStatsWidget;

class WorkbenchDashboard extends Page implements HasFilamentTutorials
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected string $view = 'filament-panels::pages.page';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Superfícies do painel')
                    ->description('Use estes elementos para validar tutoriais em páginas, schemas, tabelas, dropdowns e áreas dinâmicas.')
                    ->extraAttributes([
                        'data-tour' => 'workbench.dashboard.intro',
                    ])
                    ->schema([
                        Html::make(<<<'HTML'
                            <style>
                                .tutorial-lab-grid {
                                    display: grid;
                                    gap: 1rem;
                                }

                                @media (min-width: 768px) {
                                    .tutorial-lab-grid {
                                        grid-template-columns: repeat(2, minmax(0, 1fr));
                                    }
                                }

                                .tutorial-lab-card,
                                .tutorial-lab-menu,
                                .tutorial-lab-panel,
                                .tutorial-lab-table {
                                    border: 1px solid rgb(229 231 235);
                                    background: #fff;
                                    color: rgb(55 65 81);
                                    font-size: .875rem;
                                }

                                .tutorial-lab-card,
                                .tutorial-lab-panel {
                                    border-radius: .75rem;
                                    padding: 1rem;
                                }

                                .tutorial-lab-table {
                                    margin-top: 1rem;
                                    overflow: hidden;
                                    border-radius: .75rem;
                                }

                                .tutorial-lab-table table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    text-align: left;
                                }

                                .tutorial-lab-table th,
                                .tutorial-lab-table td {
                                    padding: .5rem .75rem;
                                }

                                .tutorial-lab-table thead {
                                    background: rgb(249 250 251);
                                    color: rgb(75 85 99);
                                }

                                .tutorial-lab-actions {
                                    display: flex;
                                    flex-wrap: wrap;
                                    gap: .75rem;
                                    margin-top: 1rem;
                                }

                                .tutorial-lab-button {
                                    display: inline-flex;
                                    min-height: 2.25rem;
                                    align-items: center;
                                    justify-content: center;
                                    border-radius: .5rem;
                                    border: 1px solid rgb(209 213 219);
                                    padding: .5rem .75rem;
                                    color: rgb(55 65 81);
                                    font-size: .875rem;
                                    font-weight: 600;
                                }

                                .tutorial-lab-menu {
                                    width: 16rem;
                                    margin-top: .75rem;
                                    border-radius: .75rem;
                                    padding: .75rem;
                                    box-shadow: 0 1px 2px rgb(0 0 0 / .05);
                                }

                                .tutorial-lab-modal-backdrop {
                                    position: fixed;
                                    inset: 0;
                                    z-index: 50;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    background: rgb(3 7 18 / .55);
                                    padding: 1.5rem;
                                }

                                .tutorial-lab-modal-window {
                                    width: min(calc(100vw - 2rem), 32rem);
                                    border-radius: .75rem;
                                    border: 1px solid rgb(3 7 18 / .05);
                                    background: #fff;
                                    padding: 1.5rem;
                                    box-shadow: 0 20px 25px -5px rgb(0 0 0 / .1), 0 8px 10px -6px rgb(0 0 0 / .1);
                                    color: rgb(17 24 39);
                                }

                                .tutorial-lab-modal-title {
                                    margin: 0;
                                    color: rgb(17 24 39);
                                    font-size: 1rem;
                                    font-weight: 600;
                                }

                                .tutorial-lab-modal-description {
                                    margin: .5rem 0 0;
                                    color: rgb(75 85 99);
                                    font-size: .875rem;
                                }

                                .tutorial-lab-menu[hidden],
                                .tutorial-lab-panel[hidden],
                                .tutorial-lab-modal-backdrop[hidden] {
                                    display: none !important;
                                }

                                .dark .tutorial-lab-card,
                                .dark .tutorial-lab-menu,
                                .dark .tutorial-lab-panel,
                                .dark .tutorial-lab-table {
                                    border-color: rgb(255 255 255 / .1);
                                    background: rgb(24 24 27);
                                    color: rgb(229 231 235);
                                }

                                .dark .tutorial-lab-table thead {
                                    background: rgb(255 255 255 / .05);
                                    color: rgb(209 213 219);
                                }

                                .dark .tutorial-lab-button {
                                    border-color: rgb(255 255 255 / .1);
                                    color: rgb(229 231 235);
                                }

                                .dark .tutorial-lab-modal-window {
                                    border-color: rgb(255 255 255 / .1);
                                    background: rgb(24 24 27);
                                    color: rgb(255 255 255);
                                }

                                .dark .tutorial-lab-modal-title {
                                    color: rgb(255 255 255);
                                }

                                .dark .tutorial-lab-modal-description {
                                    color: rgb(209 213 219);
                                }
                            </style>

                            <div class="tutorial-lab-grid">
                                <div data-tour="workbench.dashboard.body" class="tutorial-lab-card">
                                    Conteúdo principal da página.
                                </div>

                                <div data-tour="workbench.schema.card" class="tutorial-lab-card">
                                    Área representando schema e infolist.
                                </div>
                            </div>

                            <div data-tour="workbench.table.wrapper" class="tutorial-lab-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Coluna</th>
                                            <th>Finalidade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Tabela</td>
                                            <td>Valida alvo interno de tabela.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tutorial-lab-actions">
                                <button type="button" data-tour="workbench.dropdown.trigger" data-lab-dropdown-trigger class="tutorial-lab-button">
                                    Abrir menu
                                </button>
                                <button type="button" data-tour="workbench.profile.trigger" data-lab-profile-trigger class="tutorial-lab-button">
                                    Menu do usuário
                                </button>
                                <button type="button" data-tour="workbench.collapsible.trigger" data-lab-collapsible-trigger class="tutorial-lab-button">
                                    Abrir seção
                                </button>
                                <button type="button" data-tour="workbench.dashboard.modal-trigger" data-lab-modal-trigger class="tutorial-lab-button">
                                    Abrir modal
                                </button>
                            </div>

                            <div data-tour="workbench.dropdown.menu" data-lab-dropdown-menu hidden class="tutorial-lab-menu">
                                Menu aberto por pre-action antes do step.
                            </div>

                            <div data-tour="workbench.profile.menu" data-lab-profile-menu hidden class="tutorial-lab-menu">
                                Menu de perfil aberto por pre-action.
                            </div>

                            <div data-tour="workbench.collapsible.panel" data-lab-collapsible-panel hidden class="tutorial-lab-panel">
                                Seção colapsável revelada pelo tutorial.
                            </div>

                            <div data-lab-modal hidden class="tutorial-lab-modal-backdrop">
                                <div data-tour="workbench.dashboard.modal" role="dialog" aria-modal="true" aria-labelledby="workbench-dashboard-modal-heading" class="tutorial-lab-modal-window">
                                    <h2 id="workbench-dashboard-modal-heading" class="tutorial-lab-modal-title">Modal do laboratório</h2>
                                    <p class="tutorial-lab-modal-description">Conteúdo renderizado dentro do modal.</p>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('click', (event) => {
                                    if (event.target.closest('[data-lab-dropdown-trigger]')) {
                                        document.querySelector('[data-lab-dropdown-menu]')?.removeAttribute('hidden')
                                    }

                                    if (event.target.closest('[data-lab-profile-trigger]')) {
                                        document.querySelector('[data-lab-profile-menu]')?.removeAttribute('hidden')
                                    }

                                    if (event.target.closest('[data-lab-collapsible-trigger]')) {
                                        document.querySelector('[data-lab-collapsible-panel]')?.removeAttribute('hidden')
                                    }

                                    if (event.target.closest('[data-lab-modal-trigger]')) {
                                        document.querySelector('[data-lab-modal]')?.removeAttribute('hidden')
                                    }
                                })
                            </script>
                        HTML),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('openLabModal')
                ->label('Abrir modal')
                ->extraAttributes([
                    'data-tour' => TutorialTargetKeys::action('openLabModal', static::class),
                ])
                ->action(static fn (): null => null),
        ];
    }

    /**
     * @return array<int, class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            TutorialStatsWidget::class,
        ];
    }

    public static function tutorials(): FilamentTutorial
    {
        return FilamentTutorial::make('workbench-dashboard')
            ->forPage(static::class)
            ->steps([
                TutorialStep::make('dashboard-intro')
                    ->target('workbench.dashboard.intro')
                    ->title('Painel do laboratório')
                    ->description('Esta página mostra como um tutorial pode guiar áreas comuns do painel.'),
                TutorialStep::make('sidebar-navigation')
                    ->targetNavigation(static::class)
                    ->title('Menu lateral')
                    ->description('O menu lateral pode ser aberto antes do destaque para orientar a navegação.')
                    ->beforeOpenSidebar(),
                TutorialStep::make('global-search')
                    ->target('tutorial.launcher')
                    ->title('Botão de ajuda')
                    ->description('Use este botão para abrir novamente o guia disponível para a página atual.'),
                TutorialStep::make('header-action')
                    ->targetAction('openLabModal', static::class)
                    ->title('Ação da página')
                    ->description('Ações do cabeçalho também podem ser destacadas.'),
                TutorialStep::make('widget')
                    ->targetComponent('workbench.widget.stats')
                    ->title('Widget')
                    ->description('Widgets do dashboard entram no mesmo contrato de targets.'),
                TutorialStep::make('body')
                    ->target('workbench.dashboard.body')
                    ->title('Conteúdo da página')
                    ->description('Targets manuais são usados onde o componente dono conhece o elemento correto.'),
                TutorialStep::make('schema')
                    ->target('workbench.schema.card')
                    ->title('Schema e infolist')
                    ->description('A mesma estratégia funciona para schemas, infolists e componentes próprios.'),
                TutorialStep::make('table')
                    ->target('workbench.table.wrapper')
                    ->title('Tabela')
                    ->description('Tabelas podem expor alvos estáveis para guiar ações do usuário.'),
                TutorialStep::make('dropdown')
                    ->target('workbench.dropdown.menu')
                    ->title('Menu aberto')
                    ->description('Pre-actions revelam menus antes do destaque.')
                    ->beforeOpenDropdown('[data-lab-dropdown-trigger]')
                    ->afterHide('[data-lab-dropdown-menu]'),
                TutorialStep::make('profile-menu')
                    ->target('workbench.profile.menu')
                    ->title('Menu de perfil')
                    ->description('Menus de perfil podem ser abertos antes do destaque quando o gatilho é conhecido.')
                    ->beforeOpenProfileMenu('[data-lab-profile-trigger]')
                    ->afterHide('[data-lab-profile-menu]'),
                TutorialStep::make('collapsible')
                    ->target('workbench.collapsible.panel')
                    ->title('Seção aberta')
                    ->description('Seções colapsáveis podem ser abertas antes do step.')
                    ->beforeOpenCollapsible('[data-lab-collapsible-trigger]', '[data-lab-collapsible-panel]')
                    ->afterHide('[data-lab-collapsible-panel]'),
                TutorialStep::make('missing-modal-state')
                    ->target('workbench.dashboard.missing-modal-state')
                    ->title('Estado opcional ausente')
                    ->description('Steps opcionais ausentes devem ser ignorados rapidamente.')
                    ->beforeOpenModal(['selector' => '[data-lab-modal-trigger]'])
                    ->optional(),
                TutorialStep::make('missing-modal-confirmation')
                    ->target('workbench.dashboard.missing-modal-confirmation')
                    ->title('Outro estado opcional ausente')
                    ->description('O mesmo preparo de modal não deve ser executado repetidamente.')
                    ->beforeOpenModal(['selector' => '[data-lab-modal-trigger]'])
                    ->optional(),
                TutorialStep::make('modal')
                    ->target('workbench.dashboard.modal')
                    ->title('Modal aberto')
                    ->description('O tutorial pode abrir modais antes de destacar o conteúdo.')
                    ->beforeOpenModal(['selector' => '[data-lab-modal-trigger]'])
                    ->afterHide('[data-lab-modal]'),
            ]);
    }
}
