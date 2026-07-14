<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Support;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\PanelRegistry;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Resolve a user from the requested panel guard while enforcing Filament panel access.
 */
final readonly class PanelAuthenticatedUserResolver
{
    public function __construct(
        private PanelRegistry $panelRegistry,
    ) {}

    public function resolve(string $panelId): ?Authenticatable
    {
        $panel = $this->panelRegistry->get($panelId, false);

        if (! $panel instanceof Panel) {
            return null;
        }

        $user = $this->userForPanel($panel);

        if (! $user instanceof Authenticatable) {
            return null;
        }

        if ($user instanceof FilamentUser) {
            return $user->canAccessPanel($panel) ? $user : null;
        }

        return app()->environment('local') ? $user : null;
    }

    /**
     * The authenticated model is defined by each host application's panel guard.
     */
    private function userForPanel(Panel $panel): mixed
    {
        return $panel->auth()->user();
    }
}
