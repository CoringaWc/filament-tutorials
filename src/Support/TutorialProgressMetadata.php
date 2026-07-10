<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Support;

use Illuminate\Support\Str;

final class TutorialProgressMetadata
{
    private const MaximumSourceLength = 64;

    private const MaximumStepCount = 1000;

    private const MaximumTriggerLength = 255;

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, int|string>
     */
    public static function sanitize(array $metadata): array
    {
        $allowedMetadata = [];

        if (is_string($metadata['source'] ?? null)) {
            $allowedMetadata['source'] = self::truncate($metadata['source'], self::MaximumSourceLength);
        }

        if (is_int($metadata['step_count'] ?? null) || (is_string($metadata['step_count'] ?? null) && ctype_digit($metadata['step_count']))) {
            $allowedMetadata['step_count'] = min((int) $metadata['step_count'], self::MaximumStepCount);
        }

        if (is_string($metadata['trigger'] ?? null)) {
            $allowedMetadata['trigger'] = self::truncate($metadata['trigger'], self::MaximumTriggerLength);
        }

        return $allowedMetadata;
    }

    private static function truncate(string $value, int $maximumLength): string
    {
        return Str::limit($value, $maximumLength, '');
    }
}
