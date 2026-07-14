<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Actions;

use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\Models\FilamentTutorialProgress;
use CoringaWc\FilamentTutorials\Support\PanelAuthenticatedUserResolver;
use CoringaWc\FilamentTutorials\Support\TutorialManager;
use CoringaWc\FilamentTutorials\Support\TutorialProgressKey;
use CoringaWc\FilamentTutorials\Support\TutorialProgressMetadata;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Registra eventos de progresso de tutoriais para o usuário autenticado sem aceitar identidade enviada pelo navegador.
 */
final readonly class RecordTutorialProgressAction
{
    public function __construct(
        private TutorialManager $tutorialManager,
        private PanelAuthenticatedUserResolver $authenticatedUserResolver,
    ) {}

    /**
     * Recebe o evento do runtime JavaScript e persiste apenas quando há usuário autenticado e tabela disponível.
     *
     * @throws ValidationException
     */
    public function __invoke(Request $request): JsonResponse
    {
        if (! (bool) config('filament-tutorials.progress.enabled', true)) {
            return response()->json(['recorded' => false]);
        }

        $panelId = $request->input('panel_id');

        if (! is_string($panelId)) {
            return response()->json(['recorded' => false]);
        }

        $authUser = $this->authenticatedUserResolver->resolve($panelId);

        if (! $authUser instanceof Authenticatable || ! $this->progressTableExists()) {
            return response()->json(['recorded' => false]);
        }

        /** @var array{panel_id: string, tutorial_key: string, event: string, step_key?: string|null, step_index?: int|null, metadata?: array<string, mixed>} $validated */
        $validated = $request->validate([
            'panel_id' => ['required', 'string', 'max:'.FilamentTutorialProgress::MaximumPanelIdLength, 'regex:/^[a-z0-9][a-z0-9._-]*$/'],
            'tutorial_key' => ['required', 'string', 'max:'.FilamentTutorialProgress::MaximumTutorialKeyLength, 'regex:/^[a-z0-9][a-z0-9._-]*$/'],
            'event' => ['required', 'string', Rule::in(['started', 'completed', 'dismissed', 'restarted'])],
            'step_key' => ['nullable', 'string', 'max:'.FilamentTutorialProgress::MaximumStepKeyLength, 'regex:/^[a-z0-9][a-z0-9._-]*$/'],
            'step_index' => ['nullable', 'integer', 'between:0,'.TutorialProgressMetadata::MaximumStepCount],
            'metadata' => ['sometimes', 'array'],
        ]);

        $progress = $this->handle(
            authUser: $authUser,
            panelId: $validated['panel_id'],
            tutorialKey: $validated['tutorial_key'],
            event: $validated['event'],
            stepKey: $validated['step_key'] ?? null,
            stepIndex: $validated['step_index'] ?? null,
            metadata: $validated['metadata'] ?? [],
        );

        return response()->json([
            'recorded' => true,
            'status' => $progress->status,
        ]);
    }

    /**
     * Atualiza o progresso de um tutorial mantendo uma linha por usuário, painel e tutorial.
     *
     * @param  array<string, mixed>  $metadata
     *
     * @throws ValidationException
     */
    public function handle(
        Authenticatable $authUser,
        string $panelId,
        string $tutorialKey,
        string $event,
        ?string $stepKey = null,
        ?int $stepIndex = null,
        array $metadata = [],
    ): FilamentTutorialProgress {
        $this->validateInput($panelId, $tutorialKey, $event, $stepKey, $stepIndex);
        $this->ensureTutorialIsRegistered($panelId, $tutorialKey);

        $now = Carbon::now();
        $userType = $authUser::class;
        $userId = (string) $authUser->getAuthIdentifier();

        $this->validateIdentity($userType, $userId);

        $identity = [
            'user_type' => $userType,
            'user_id' => $userId,
            'panel_id' => $panelId,
            'tutorial_key' => $tutorialKey,
        ];

        $updates = [
            'last_step_key' => $stepKey,
            'last_step_index' => $stepIndex,
            'metadata' => TutorialProgressMetadata::sanitize($metadata),
        ];

        $updates = match ($event) {
            'started' => [
                ...$updates,
                'status' => FilamentTutorialProgress::StatusStarted,
                'started_at' => $now,
                'completed_at' => null,
                'dismissed_at' => null,
                'restarted_at' => null,
            ],
            'completed' => [
                ...$updates,
                'status' => FilamentTutorialProgress::StatusCompleted,
                'completed_at' => $now,
                'dismissed_at' => null,
            ],
            'dismissed' => [
                ...$updates,
                'status' => FilamentTutorialProgress::StatusDismissed,
                'completed_at' => null,
                'dismissed_at' => $now,
            ],
            'restarted' => [
                ...$updates,
                'status' => FilamentTutorialProgress::StatusStarted,
                'started_at' => $now,
                'restarted_at' => $now,
                'completed_at' => null,
                'dismissed_at' => null,
            ],
            default => throw ValidationException::withMessages([
                'event' => __('The progress event is invalid.'),
            ]),
        };

        return FilamentTutorialProgress::query()
            ->updateOrCreate($identity, $updates)
            ->refresh();
    }

    private function progressTableExists(): bool
    {
        return Schema::hasTable(config('filament-tutorials.progress.table', 'filament_tutorial_progress'));
    }

    /**
     * @throws ValidationException
     */
    private function ensureTutorialIsRegistered(string $panelId, string $tutorialKey): void
    {
        if ($this->tutorialManager->find($panelId, $tutorialKey) instanceof FilamentTutorial) {
            return;
        }

        throw ValidationException::withMessages([
            'tutorial_key' => __('The tutorial is invalid.'),
        ]);
    }

    /**
     * Garante que o runtime informe somente chaves e eventos seguros antes de qualquer escrita.
     *
     * @throws ValidationException
     */
    private function validateInput(string $panelId, string $tutorialKey, string $event, ?string $stepKey, ?int $stepIndex): void
    {
        $messages = [];

        if (! TutorialProgressKey::isValid($panelId, FilamentTutorialProgress::MaximumPanelIdLength)) {
            $messages['panel_id'] = __('The panel is invalid.');
        }

        if (! TutorialProgressKey::isValid($tutorialKey, FilamentTutorialProgress::MaximumTutorialKeyLength)) {
            $messages['tutorial_key'] = __('The tutorial is invalid.');
        }

        if (! in_array($event, ['started', 'completed', 'dismissed', 'restarted'], true)) {
            $messages['event'] = __('The progress event is invalid.');
        }

        if ($stepKey !== null && ! TutorialProgressKey::isValid($stepKey, FilamentTutorialProgress::MaximumStepKeyLength)) {
            $messages['step_key'] = __('The tutorial step is invalid.');
        }

        if ($stepIndex !== null && ($stepIndex < 0 || $stepIndex > TutorialProgressMetadata::MaximumStepCount)) {
            $messages['step_index'] = __('The tutorial step is invalid.');
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }

    /**
     * @throws ValidationException
     */
    private function validateIdentity(string $userType, string $userId): void
    {
        if (strlen($userType) <= FilamentTutorialProgress::MaximumUserTypeLength
            && strlen($userId) <= FilamentTutorialProgress::MaximumUserIdLength) {
            return;
        }

        throw ValidationException::withMessages([
            'user' => __('The authenticated user identity is invalid.'),
        ]);
    }
}
