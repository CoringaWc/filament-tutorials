<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Contracts;

use CoringaWc\FilamentTutorials\FilamentTutorial;

interface HasFilamentTutorials
{
    /**
     * @return FilamentTutorial|class-string<FilamentTutorial>|array<int, FilamentTutorial|class-string<FilamentTutorial>>|null
     */
    public static function tutorials(): FilamentTutorial|string|array|null;
}
