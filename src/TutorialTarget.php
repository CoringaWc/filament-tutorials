<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials;

final class TutorialTarget
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function __construct(
        protected string $type,
        protected ?string $key = null,
        protected array $parameters = [],
    ) {}

    public static function custom(string $key): static
    {
        return new self('custom', $key);
    }

    public static function renderHook(string $key): static
    {
        return new static('renderHook', $key);
    }

    /**
     * @param  class-string  $resourceOrPage
     */
    public static function navigation(string $resourceOrPage): static
    {
        return new static('navigation', $resourceOrPage);
    }

    /**
     * @param  class-string|null  $owner
     */
    public static function action(string $action, ?string $owner = null): static
    {
        return new static('action', $action, [
            'owner' => $owner,
        ]);
    }

    /**
     * @param  class-string|null  $page
     */
    public static function page(?string $page = null): static
    {
        return new static('page', $page);
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
