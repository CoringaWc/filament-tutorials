<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Support;

use Illuminate\Support\Str;

final class TutorialTargetKeys
{
    public static function page(?string $page): string
    {
        return 'filament-tutorials.page.'.self::classKey($page ?? 'current-page');
    }

    public static function navigation(string $resourceOrPage): string
    {
        return 'filament-tutorials.navigation.'.self::classKey($resourceOrPage);
    }

    public static function action(string $action, ?string $owner): string
    {
        $ownerKey = $owner === null ? 'current' : self::classKey($owner);

        return "filament-tutorials.action.{$ownerKey}.".Str::of($action)->kebab()->toString();
    }

    public static function renderHook(string $key): string
    {
        return 'filament-tutorials.render-hook.'.Str::of($key)->replace([':', '_'], '.')->kebab()->toString();
    }

    private static function classKey(string $class): string
    {
        return Str::of($class)
            ->classBasename()
            ->kebab()
            ->append('-', substr(sha1($class), 0, 8))
            ->toString();
    }
}
