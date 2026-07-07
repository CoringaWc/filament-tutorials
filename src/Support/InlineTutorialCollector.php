<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Support;

use CoringaWc\FilamentTutorials\Contracts\HasFilamentTutorials;
use CoringaWc\FilamentTutorials\FilamentTutorial;
use Filament\Panel;
use InvalidArgumentException;

class InlineTutorialCollector
{
    /**
     * @return list<FilamentTutorial>
     */
    public function collect(Panel $panel): array
    {
        $tutorials = [];

        foreach ($this->getTutorialOwnerClasses($panel) as $ownerClass) {
            foreach ($this->normalizeTutorials($ownerClass::tutorials(), $ownerClass) as $tutorial) {
                $tutorials[] = $tutorial;
            }
        }

        return $tutorials;
    }

    /**
     * @return list<class-string<HasFilamentTutorials>>
     */
    protected function getTutorialOwnerClasses(Panel $panel): array
    {
        $ownerClasses = [
            ...$panel->getPages(),
            ...$panel->getResources(),
        ];

        foreach ($panel->getPageConfigurations() as $configuration) {
            $ownerClasses[] = $configuration->getPage();
        }

        foreach ($panel->getResourceConfigurations() as $configuration) {
            $ownerClasses[] = $configuration->getResource();
        }

        $ownerClasses = array_unique($ownerClasses);

        return array_values(array_filter(
            $ownerClasses,
            static fn (string $ownerClass): bool => is_subclass_of($ownerClass, HasFilamentTutorials::class),
        ));
    }

    /**
     * @param  FilamentTutorial|class-string<FilamentTutorial>|array<int, FilamentTutorial|class-string<FilamentTutorial>>|null  $tutorials
     * @param  class-string  $ownerClass
     * @return list<FilamentTutorial>
     */
    protected function normalizeTutorials(FilamentTutorial|string|array|null $tutorials, string $ownerClass): array
    {
        if ($tutorials === null) {
            return [];
        }

        $tutorials = is_array($tutorials) ? $tutorials : [$tutorials];

        return array_map(
            fn (FilamentTutorial|string $tutorial): FilamentTutorial => $this->resolveTutorial($tutorial, $ownerClass),
            $tutorials,
        );
    }

    /**
     * @param  FilamentTutorial|class-string<FilamentTutorial>  $tutorial
     * @param  class-string  $ownerClass
     */
    protected function resolveTutorial(FilamentTutorial|string $tutorial, string $ownerClass): FilamentTutorial
    {
        if (is_string($tutorial)) {
            if (! is_subclass_of($tutorial, FilamentTutorial::class)) {
                throw new InvalidArgumentException(sprintf(
                    'Tutorial class [%s] must extend [%s].',
                    $tutorial,
                    FilamentTutorial::class,
                ));
            }

            $tutorial = app($tutorial);
        }

        if ($tutorial->getPage() === null) {
            $tutorial->forPage($ownerClass);
        }

        return $tutorial;
    }
}
