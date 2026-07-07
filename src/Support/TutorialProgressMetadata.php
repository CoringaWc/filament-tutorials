<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Support;

final class TutorialProgressMetadata
{
    /** @var list<string> */
    private const AllowedKeys = [
        'source',
        'step_count',
        'trigger',
    ];

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, bool|float|int|string|null>
     */
    public static function sanitize(array $metadata): array
    {
        $allowedMetadata = [];

        foreach (self::AllowedKeys as $key) {
            $value = $metadata[$key] ?? null;

            if ($value === null || is_scalar($value)) {
                $allowedMetadata[$key] = $value;
            }
        }

        return array_filter(
            $allowedMetadata,
            static fn (bool|float|int|string|null $value): bool => $value !== null,
        );
    }
}
