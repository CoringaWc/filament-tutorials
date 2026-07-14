<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\Actions\RecordTutorialProgressAction;
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\Models\FilamentTutorialProgress;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use CoringaWc\FilamentTutorials\Support\TutorialPayloadFactory;
use CoringaWc\FilamentTutorials\Support\TutorialProgressMetadata;
use CoringaWc\FilamentTutorials\TutorialStep;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
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
        ->not->toBeNull()
        ->and($completed->dismissed_at)
        ->toBeNull();

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
        ->not->toBeNull()
        ->and($dismissed->completed_at)
        ->toBeNull();

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

    expect(fn () => app(RecordTutorialProgressAction::class)->handle(
        authUser: $authUser,
        panelId: 'admin',
        tutorialKey: 'workbench-dashboard',
        event: 'started',
        stepKey: 'dashboard-intro',
        stepIndex: TutorialProgressMetadata::MaximumStepCount + 1,
    ))->toThrow(ValidationException::class);

    expect(FilamentTutorialProgress::query()->count())->toBe(0);
});

it('rejects progress for tutorials that are not registered in the panel', function (): void {
    $authUser = User::query()->create([
        'name' => 'Usuário não registrado',
        'email' => 'unregistered@example.test',
        'password' => 'password',
    ]);

    expect(fn () => app(RecordTutorialProgressAction::class)->handle(
        authUser: $authUser,
        panelId: 'admin',
        tutorialKey: 'unregistered-tutorial',
        event: 'started',
    ))->toThrow(ValidationException::class);

    expect(FilamentTutorialProgress::query()->count())->toBe(0);
});

it('limits the persisted tutorial progress metadata to supported bounded values', function (): void {
    $authUser = User::query()->create([
        'name' => 'Usuário de metadados',
        'email' => 'metadata@example.test',
        'password' => 'password',
    ]);

    $progress = app(RecordTutorialProgressAction::class)->handle(
        authUser: $authUser,
        panelId: 'admin',
        tutorialKey: 'workbench-dashboard',
        event: 'started',
        metadata: [
            'source' => str_repeat('s', 65),
            'step_count' => '1001',
            'trigger' => str_repeat('t', 256),
            'unexpected' => ['ignored'],
        ],
    );

    expect($progress->metadata)
        ->toBe([
            'source' => str_repeat('s', 64),
            'step_count' => 1000,
            'trigger' => str_repeat('t', 255),
        ]);

    $progress = app(RecordTutorialProgressAction::class)->handle(
        authUser: $authUser,
        panelId: 'admin',
        tutorialKey: 'workbench-dashboard',
        event: 'started',
        metadata: ['step_count' => -10],
    );

    expect($progress->metadata)->toBe(['step_count' => 0]);
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

it('does not record endpoint progress when persistence is disabled', function (): void {
    config()->set('filament-tutorials.progress.enabled', false);

    $authUser = User::query()->create([
        'name' => 'Usuário sem persistência',
        'email' => 'disabled-progress@example.test',
        'password' => 'password',
    ]);

    actingAs($authUser);

    postJson(route('filament-tutorials.progress'), [
        'panel_id' => 'admin',
        'tutorial_key' => 'workbench-dashboard',
        'event' => 'started',
    ])
        ->assertOk()
        ->assertJson(['recorded' => false]);

    expect(FilamentTutorialProgress::query()->count())->toBe(0);
});

it('rejects endpoint progress for a tutorial that is not registered in the panel', function (): void {
    $authUser = User::query()->create([
        'name' => 'Usuário endpoint inválido',
        'email' => 'invalid-endpoint@example.test',
        'password' => 'password',
    ]);

    actingAs($authUser);

    postJson(route('filament-tutorials.progress'), [
        'panel_id' => 'admin',
        'tutorial_key' => 'unregistered-tutorial',
        'event' => 'started',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('tutorial_key');

    expect(FilamentTutorialProgress::query()->count())->toBe(0);
});

it('rejects malformed endpoint progress without coercing values', function (): void {
    $authUser = User::query()->create([
        'name' => 'Usuário payload inválido',
        'email' => 'malformed@example.test',
        'password' => 'password',
    ]);

    actingAs($authUser);

    postJson(route('filament-tutorials.progress'), [
        'panel_id' => 'admin',
        'tutorial_key' => 'workbench-dashboard',
        'event' => 'started',
        'step_index' => 1.5,
        'metadata' => 'invalid',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['step_index', 'metadata']);

    expect(FilamentTutorialProgress::query()->count())->toBe(0);
});

it('does not fall back to another authenticated guard for panel progress', function (): void {
    config()->set('auth.guards.tutorial-test', [
        'driver' => 'session',
        'provider' => 'users',
    ]);

    Filament::getPanel('admin')->authGuard('tutorial-test');

    $authUser = User::query()->create([
        'name' => 'Usuário de outro guard',
        'email' => 'other-guard@example.test',
        'password' => 'password',
    ]);

    actingAs($authUser);

    postJson(route('filament-tutorials.progress'), [
        'panel_id' => 'admin',
        'tutorial_key' => 'workbench-dashboard',
        'event' => 'started',
    ])
        ->assertOk()
        ->assertJson(['recorded' => false]);

    expect(FilamentTutorialProgress::query()->count())->toBe(0);
});

it('rejects progress when the authenticated user cannot access the requested panel', function (): void {
    $authUser = new class extends User implements FilamentUser
    {
        public function canAccessPanel(Panel $panel): bool
        {
            return false;
        }
    };

    Filament::getPanel('admin')->auth()->setUser($authUser);

    postJson(route('filament-tutorials.progress'), [
        'panel_id' => 'admin',
        'tutorial_key' => 'workbench-dashboard',
        'event' => 'started',
    ])
        ->assertOk()
        ->assertJson(['recorded' => false]);

    expect(FilamentTutorialProgress::query()->count())->toBe(0);
});

it('rate limits progress writes per browser session', function (): void {
    config()->set('filament-tutorials.progress.rate_limit.max_attempts', 2);
    config()->set('filament-tutorials.progress.rate_limit.decay_seconds', 60);

    $authUser = User::query()->create([
        'name' => 'Usuário limitado',
        'email' => 'limited@example.test',
        'password' => 'password',
    ]);

    actingAs($authUser);

    $payload = [
        'panel_id' => 'admin',
        'tutorial_key' => 'workbench-dashboard',
        'event' => 'started',
    ];

    postJson(route('filament-tutorials.progress'), $payload)->assertOk();
    postJson(route('filament-tutorials.progress'), $payload)->assertOk();
    postJson(route('filament-tutorials.progress'), $payload)->assertTooManyRequests();
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
        ->and($payload['progress'])
        ->not->toHaveKey('csrfToken')
        ->and($tutorial['autoStart'] ?? null)
        ->toBeFalse()
        ->and($tutorial['progressStatus'] ?? null)
        ->toBe(FilamentTutorialProgress::StatusCompleted);
});
