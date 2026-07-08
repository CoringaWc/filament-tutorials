<?php

declare(strict_types=1);

namespace Workbench\App\Providers\Filament;

use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\FilamentTutorialsPlugin;
use CoringaWc\FilamentTutorials\TutorialStep;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Workbench\App\Filament\Pages\WorkbenchDashboard;
use Workbench\App\Filament\Resources\TutorialRecords\TutorialRecordResource;
use Workbench\App\Http\Middleware\AuthenticateWorkbenchUser;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->globalSearch()
            ->pages([
                WorkbenchDashboard::class,
            ])
            ->resources([
                TutorialRecordResource::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateWorkbenchUser::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([])
            ->plugins([
                FilamentTutorialsPlugin::make()
                    ->discoverTutorials(
                        in: dirname(__DIR__, 2).'/Filament/Admin/Tutorials',
                        for: 'Workbench\\App\\Filament\\Admin\\Tutorials',
                    )
                    ->tutorials(
                        FilamentTutorial::make('explicit-workbench-tutorial')
                            ->forPage(WorkbenchDashboard::class)
                            ->steps([
                                TutorialStep::make('explicit-workbench-step')
                                    ->targetPage(WorkbenchDashboard::class)
                                    ->title('Tutorial registrado')
                                    ->description('Este guia foi registrado diretamente no plugin do painel.'),
                            ]),
                    ),
            ]);
    }
}
