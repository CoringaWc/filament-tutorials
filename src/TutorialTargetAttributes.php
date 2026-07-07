<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials;

use CoringaWc\FilamentTutorials\Support\TutorialTargetKeys;

final class TutorialTargetAttributes
{
    /**
     * @return array{'data-tour': string}
     */
    public static function custom(string $key): array
    {
        return ['data-tour' => $key];
    }

    /**
     * @return array{'data-tour': string}
     */
    public static function component(string $key): array
    {
        return ['data-tour' => TutorialTargetKeys::component($key)];
    }

    /**
     * @param  class-string|null  $page
     * @return array{'data-tour': string}
     */
    public static function page(?string $page = null): array
    {
        return ['data-tour' => TutorialTargetKeys::page($page)];
    }

    /**
     * @param  class-string  $resourceOrPage
     * @return array{'data-tour': string}
     */
    public static function navigation(string $resourceOrPage): array
    {
        return ['data-tour' => TutorialTargetKeys::navigation($resourceOrPage)];
    }

    /**
     * @return array{'data-tour': string}
     */
    public static function renderHook(string $key): array
    {
        return ['data-tour' => TutorialTargetKeys::renderHook($key)];
    }

    /**
     * @param  class-string|null  $owner
     * @return array{'data-tour': string}
     */
    public static function action(string $action, ?string $owner = null): array
    {
        return ['data-tour' => TutorialTargetKeys::action($action, $owner)];
    }

    /**
     * @param  array{'data-tour': string}  $attributes
     */
    public static function selector(array $attributes): string
    {
        return sprintf('[data-tour="%s"]', addcslashes($attributes['data-tour'], '"\\'));
    }
}
