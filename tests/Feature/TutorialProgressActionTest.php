<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\Actions\RecordTutorialProgressAction;
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\Models\FilamentTutorialProgress;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use CoringaWc\FilamentTutorials\Support\TutorialPayloadFactory;
use CoringaWc\FilamentTutorials\TutorialStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Workbench\App\Filament\Pages\WorkbenchDashboard;
use Workbench\App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('records tutorial progress lifecycle events for the authenticated user', function (): void {
    $authUser = User::query()->create([
        'name' => 'Usuário do laboratório',
        'email' => 'usuario@example.test',
        'password' => 'password',
    ]);

    $action = app(RecordTutorialProgressAction::class);

    $started = $action->handle(
        authUser: $authUser,
        panelId: 'admin',
        tutorialKey: 'workbench-dashboard',
        event: 'started',
        stepKey: 'dashboard-intro',
        stepIndex: 0,
        metadata: [
            'source' => 'runtime',
            'step_count' => 4,
            'token' => 'ignored',
        ],
    );

    expect($started->status)
        ->toBe(FilamentTutorialProgress::StatusStarted)
        ->and($started->metadata)
        ->toBe([
            'source' => 'runtime',
            'step_count' => 4,
        ]);

    $completed = $action->handle(
        authUser: $authUser,
        panelId: 'admin',
        tutorialKey: 'workbench-dashboard',
        event: 'completed',
        stepKey: 'summary',
        stepIndex: 3,
    );

    expect($completed->status)
        ->toBe(FilamentTutorialProgress::StatusCompleted)
        ->and($completed->completed_at)
        ->not->toBeNull();

    $dismissed = $action->handle(
        authUser: $authUser,
        panelId: 'admin',
        tutorialKey: 'workbench-dashboard',
        event: 'dismissed',
        stepKey: 'summary',
        stepIndex: 3,
    );

    expect($dismissed->status)
        ->toBe(FilamentTutorialProgress::StatusDismissed)
        ->and($dismissed->dismissed_at)
        ->not->toBeNull();

    $restarted = $action->handle(
        authUser: $authUser,
        panelId: 'admin',
        tutorialKey: 'workbench-dashboard',
        event: 'restarted',
        stepKey: 'dashboard-intro',
        stepIndex: 0,
    );

    expect($restarted->status)
        ->toBe(FilamentTutorialProgress::StatusStarted)
        ->and($restarted->completed_at)
        ->toBeNull()
        ->and($restarted->dismissed_at)
        ->toBeNull()
        ->and($restarted->restarted_at)
        ->not->toBeNull();

    $startedAgain = $action->handle(
        authUser: $authUser,
        panelId: 'admin',
        tutorialKey: 'workbench-dashboard',
        event: 'started',
        stepKey: 'dashboard-intro',
        stepIndex: 0,
    );

    expect($startedAgain->status)
        ->toBe(FilamentTutorialProgress::StatusStarted)
        ->and($startedAgain->completed_at)
        ->toBeNull()
        ->and($startedAgain->dismissed_at)
        ->toBeNull();
});

it('rejects invalid tutorial and step keys before writing progress', function (): void {
    $authUser = User::query()->create([
        'name' => 'Usuário inválido',
        'email' => 'invalid@example.test',
        'password' => 'password',
    ]);

    expect(fn () => app(RecordTutorialProgressAction::class)->handle(
        authUser: $authUser,
        panelId: 'admin',
        tutorialKey: '../bad',
        event: 'started',
        stepKey: 'step ok',
        stepIndex: -1,
    ))->toThrow(ValidationException::class);

    expect(FilamentTutorialProgress::query()->count())->toBe(0);
});

it('rejects oversized progress identifiers before writing progress', function (): void {
    $authUser = User::query()->create([
        'name' => 'Usuário com chave inválida',
        'email' => 'long-key@example.test',
        'password' => 'password',
    ]);

    expect(fn () => app(RecordTutorialProgressAction::class)->handle(
        authUser: $authUser,
        panelId: str_repeat('a', 256),
        tutorialKey: 'workbench-dashboard',
        event: 'started',
        stepKey: 'dashboard-intro',
        stepIndex: 0,
    ))->toThrow(ValidationException::class);

    expect(FilamentTutorialProgress::query()->count())->toBe(0);
});

it('isolates tutorial progress by user identity', function (): void {
    $firstUser = User::query()->create([
        'name' => 'Primeiro usuário',
        'email' => 'first@example.test',
        'password' => 'password',
    ]);

    $secondUser = User::query()->create([
        'name' => 'Segundo usuário',
        'email' => 'second@example.test',
        'password' => 'password',
    ]);

    $action = app(RecordTutorialProgressAction::class);

    $action->handle($firstUser, 'admin', 'workbench-dashboard', 'completed');
    $action->handle($secondUser, 'admin', 'workbench-dashboard', 'dismissed');

    expect(FilamentTutorialProgress::query()->count())->toBe(2)
        ->and(FilamentTutorialProgress::query()->where('user_id', (string) $firstUser->getKey())->first()?->status)
        ->toBe(FilamentTutorialProgress::StatusCompleted)
        ->and(FilamentTutorialProgress::query()->where('user_id', (string) $secondUser->getKey())->first()?->status)
        ->toBe(FilamentTutorialProgress::StatusDismissed);
});

it('records progress through the package endpoint without accepting browser identity', function (): void {
    $authUser = User::query()->create([
        'name' => 'Usuário endpoint',
        'email' => 'endpoint@example.test',
        'password' => 'password',
    ]);

    actingAs($authUser);

    postJson(route('filament-tutorials.progress'), [
        'panel_id' => 'admin',
        'tutorial_key' => 'workbench-dashboard',
        'event' => 'started',
        'step_key' => 'dashboard-intro',
        'step_index' => 0,
        'metadata' => [
            'source' => 'runtime',
            'user_id' => '999',
        ],
    ])
        ->assertOk()
        ->assertJson([
            'recorded' => true,
            'status' => FilamentTutorialProgress::StatusStarted,
        ]);

    $progress = FilamentTutorialProgress::query()->firstOrFail();

    expect($progress->user_id)
        ->toBe((string) $authUser->getKey())
        ->and($progress->metadata)
        ->toBe(['source' => 'runtime']);
});

it('does not auto-start tutorials completed by the authenticated user', function (): void {
    $authUser = User::query()->create([
        'name' => 'Usuário auto start',
        'email' => 'autostart@example.test',
        'password' => 'password',
    ]);

    actingAs($authUser);

    app(TutorialManager::class)->register('admin', [
        FilamentTutorial::make('auto-start-tutorial')
            ->forPage(WorkbenchDashboard::class)
            ->autoStart()
            ->steps([
                TutorialStep::make('intro')
                    ->targetPage(WorkbenchDashboard::class)
                    ->title('Introdução')
                    ->description('Primeiro passo.'),
            ]),
    ]);

    app(RecordTutorialProgressAction::class)->handle(
        authUser: $authUser,
        panelId: 'admin',
        tutorialKey: 'auto-start-tutorial',
        event: 'completed',
    );

    $payload = app(TutorialPayloadFactory::class)->forPanelAndScopes('admin', [
        WorkbenchDashboard::class,
    ]);

    $tutorial = collect($payload['tutorials'])
        ->firstWhere('key', 'auto-start-tutorial');

    expect($payload['progress']['endpoint'] ?? null)
        ->toBe('/filament-tutorials/progress')
        ->and($tutorial['autoStart'] ?? null)
        ->toBeFalse()
        ->and($tutorial['progressStatus'] ?? null)
        ->toBe(FilamentTutorialProgress::StatusCompleted);
});
