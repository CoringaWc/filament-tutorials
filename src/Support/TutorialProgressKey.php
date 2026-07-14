<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Support;

final class TutorialProgressKey
{
    public static function isValid(string $key, int $maximumLength): bool
    {
        return strlen($key) <= $maximumLength
            && preg_match('/^[a-z0-9][a-z0-9._-]*$/', $key) === 1;
    }
}
