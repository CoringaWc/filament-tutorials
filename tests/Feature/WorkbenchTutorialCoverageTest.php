<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Filament\Resources\TutorialRecords\TutorialRecordResource;
use Workbench\App\Models\TutorialRecord;

use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('renders dashboard tutorial runtime and stable coverage targets', function (): void {
    followingRedirects()
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-filament-tutorials-runtime', false)
        ->assertSee('data-filament-tutorials-launcher', false)
        ->assertSee('data-tour="workbench.dashboard.body"', false)
        ->assertSee('data-tour="workbench.schema.card"', false)
        ->assertSee('data-tour="workbench.table.wrapper"', false)
        ->assertSee('data-tour="workbench.dropdown.trigger"', false)
        ->assertSee('data-tour="workbench.collapsible.trigger"', false)
        ->assertSee('data-tour="filament-tutorials.render-hook.page.header-widgets.before"', false)
        ->assertSee('data-tour="filament-tutorials.render-hook.global-search.before"', false)
        ->assertSee('data-tour="filament-tutorials.render-hook.sidebar.nav.start"', false);
});

it('renders resource tutorial targets and related page targets', function (): void {
    $record = TutorialRecord::query()->create([
        'title' => 'Registro de teste',
        'status' => 'draft',
        'summary' => 'Registro criado para teste.',
    ]);

    get(TutorialRecordResource::getUrl(panel: 'admin'))
        ->assertOk()
        ->assertSee('data-filament-tutorials-runtime', false)
        ->assertSee('data-filament-tutorials-launcher', false)
        ->assertSee('data-tour="workbench.resource.table.title"', false)
        ->assertSee('data-tour="filament-tutorials.render-hook.resource.list.table.before"', false);

    get(TutorialRecordResource::getUrl('relations', ['record' => $record], panel: 'admin'))
        ->assertOk()
        ->assertSee('data-tour="workbench.relation.context"', false)
        ->assertSee('data-tour="workbench.relation.table"', false);
});
