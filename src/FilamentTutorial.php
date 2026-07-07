<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials;

use Illuminate\Support\Str;

class FilamentTutorial
{
    protected ?string $key = null;

    /** @var class-string|null */
    protected static ?string $page = null;

    /** @var class-string|null */
    protected ?string $pageOverride = null;

    /** @var list<TutorialStep> */
    protected array $configuredSteps = [];

    protected bool $shouldAutoStart = false;

    protected ?string $adapter = null;

    public static function make(?string $key = null): static
    {
        $tutorial = app(static::class);

        if ($key !== null) {
            $tutorial->key($key);
        }

        return $tutorial;
    }

    public function key(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    public function getKey(): string
    {
        if ($this->key !== null) {
            return $this->key;
        }

        return Str::of(static::class)
            ->classBasename()
            ->beforeLast('Tutorial')
            ->kebab()
            ->toString();
    }

    /**
     * @param  class-string  $page
     */
    public function forPage(string $page): static
    {
        $this->pageOverride = $page;

        return $this;
    }

    /**
     * @return class-string|null
     */
    public function getPage(): ?string
    {
        return $this->pageOverride ?? static::$page;
    }

    /**
     * @param  list<TutorialStep>|null  $steps
     * @return list<TutorialStep>|static
     */
    public function steps(?array $steps = null): array|static
    {
        if ($steps === null) {
            return $this->configuredSteps;
        }

        $this->configuredSteps = $steps;

        return $this;
    }

    /**
     * @return list<TutorialStep>
     */
    public function getSteps(): array
    {
        $steps = $this->steps();

        return is_array($steps) ? $steps : [];
    }

    public function autoStart(bool $condition = true): static
    {
        $this->shouldAutoStart = $condition;

        return $this;
    }

    public function shouldAutoStart(): bool
    {
        return $this->shouldAutoStart;
    }

    public function adapter(?string $adapter): static
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function getAdapter(): ?string
    {
        return $this->adapter;
    }

    /**
     * @return array{key: string, page: class-string|null, autoStart: bool, adapter: string|null, steps: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'page' => $this->getPage(),
            'autoStart' => $this->shouldAutoStart(),
            'adapter' => $this->getAdapter(),
            'steps' => array_map(
                static fn (TutorialStep $step): array => $step->toArray(),
                $this->getSteps(),
            ),
        ];
    }
}
