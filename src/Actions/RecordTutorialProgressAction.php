<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Actions;

use CoringaWc\FilamentTutorials\Models\FilamentTutorialProgress;
use CoringaWc\FilamentTutorials\Support\TutorialProgressMetadata;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Registra eventos de progresso de tutoriais para o usuário autenticado sem aceitar identidade enviada pelo navegador.
 */
final class RecordTutorialProgressAction
{
    /**
     * Recebe o evento do runtime JavaScript e persiste apenas quando há usuário autenticado e tabela disponível.
     *
     * @throws ValidationException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser instanceof Authenticatable || ! $this->progressTableExists()) {
            return response()->json(['recorded' => false]);
        }

        $progress = $this->handle(
            authUser: $authUser,
            panelId: (string) $request->input('panel_id'),
            tutorialKey: (string) $request->input('tutorial_key'),
            event: (string) $request->input('event'),
            stepKey: is_string($request->input('step_key')) ? $request->input('step_key') : null,
            stepIndex: is_numeric($request->input('step_index')) ? (int) $request->input('step_index') : null,
            metadata: is_array($request->input('metadata')) ? $request->input('metadata') : [],
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

        $now = Carbon::now();
        $identity = [
            'user_type' => $authUser::class,
            'user_id' => (string) $authUser->getAuthIdentifier(),
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
            ],
            'completed' => [
                ...$updates,
                'status' => FilamentTutorialProgress::StatusCompleted,
                'completed_at' => $now,
            ],
            'dismissed' => [
                ...$updates,
                'status' => FilamentTutorialProgress::StatusDismissed,
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
     * Garante que o runtime informe somente chaves e eventos seguros antes de qualquer escrita.
     *
     * @throws ValidationException
     */
    private function validateInput(string $panelId, string $tutorialKey, string $event, ?string $stepKey, ?int $stepIndex): void
    {
        $messages = [];

        if (! $this->isValidKey($panelId)) {
            $messages['panel_id'] = __('The panel is invalid.');
        }

        if (! $this->isValidKey($tutorialKey)) {
            $messages['tutorial_key'] = __('The tutorial is invalid.');
        }

        if (! in_array($event, ['started', 'completed', 'dismissed', 'restarted'], true)) {
            $messages['event'] = __('The progress event is invalid.');
        }

        if ($stepKey !== null && ! $this->isValidKey($stepKey)) {
            $messages['step_key'] = __('The tutorial step is invalid.');
        }

        if ($stepIndex !== null && $stepIndex < 0) {
            $messages['step_index'] = __('The tutorial step is invalid.');
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }

    private function isValidKey(string $key): bool
    {
        return strlen($key) <= 255
            && preg_match('/^[a-z0-9][a-z0-9._-]*$/', $key) === 1;
    }
}
