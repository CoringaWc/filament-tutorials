<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Support;

use CoringaWc\FilamentTutorials\FilamentTutorial;
use Filament\Panel;
use InvalidArgumentException;
use LogicException;

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
            $tutorial = $this->resolveTutorial($tutorial);
            $key = $tutorial->getKey();

            if (isset($this->tutorialsByPanel[$panelId][$key])) {
                throw new LogicException("Tutorial [{$key}] is already registered for panel [{$panelId}].");
            }

            $this->tutorialsByPanel[$panelId][$key] = $tutorial;
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

    /**
     * @param  FilamentTutorial|class-string<FilamentTutorial>  $tutorial
     */
    protected function resolveTutorial(FilamentTutorial|string $tutorial): FilamentTutorial
    {
        if ($tutorial instanceof FilamentTutorial) {
            return $tutorial;
        }

        if (! is_subclass_of($tutorial, FilamentTutorial::class)) {
            throw new InvalidArgumentException(sprintf(
                'Tutorial class [%s] must extend [%s].',
                $tutorial,
                FilamentTutorial::class,
            ));
        }

        return app($tutorial);
    }
}
