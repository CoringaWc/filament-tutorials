<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials;

final readonly class TutorialTarget
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function __construct(
        private string $type,
        private ?string $key = null,
        private array $parameters = [],
    ) {}

    public static function custom(string $key): static
    {
        return new self('custom', $key);
    }

    public static function renderHook(string $key): static
    {
        return new self('renderHook', $key);
    }

    public static function component(string $key): static
    {
        return new self('component', $key);
    }

    /**
     * @param  class-string  $resourceOrPage
     */
    public static function navigation(string $resourceOrPage): static
    {
        return new self('navigation', $resourceOrPage);
    }

    /**
     * @param  class-string|null  $owner
     */
    public static function action(string $action, ?string $owner = null): static
    {
        return new self('action', $action, [
            'owner' => $owner,
        ]);
    }

    /**
     * @param  class-string|null  $page
     */
    public static function page(?string $page = null): static
    {
        return new self('page', $page);
    }

    /**
     * @return array{type: string, key: string|null, parameters: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'key' => $this->key,
            'parameters' => $this->parameters,
        ];
    }
}
