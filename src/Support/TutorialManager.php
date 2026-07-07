<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Support;

use CoringaWc\FilamentTutorials\FilamentTutorial;
use Filament\Panel;

class TutorialManager
{
    /** @var array<string, array<string, FilamentTutorial>> */
    protected array $tutorialsByPanel = [];

    /**
     * @param  iterable<FilamentTutorial|class-string<FilamentTutorial>>  $tutorials
     */
    public function register(string $panelId, iterable $tutorials): void
    {
        foreach ($tutorials as $tutorial) {
            $tutorial = is_string($tutorial) ? app($tutorial) : $tutorial;
            $this->tutorialsByPanel[$panelId][$tutorial->getKey()] = $tutorial;
        }
    }

    /**
     * @return array<string, FilamentTutorial>
     */
    public function forPanel(string|Panel $panel): array
    {
        $panelId = $panel instanceof Panel ? $panel->getId() : $panel;

        return $this->tutorialsByPanel[$panelId] ?? [];
    }

    public function find(string|Panel $panel, string $key): ?FilamentTutorial
    {
        return $this->forPanel($panel)[$key] ?? null;
    }
}
