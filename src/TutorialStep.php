<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials;

class TutorialStep
{
    protected ?string $key = null;

    protected ?TutorialTarget $target = null;

    protected ?string $title = null;

    protected ?string $description = null;

    /** @var list<array{action: string, parameters: array<string, mixed>}> */
    protected array $before = [];

    /** @var list<array{action: string, parameters: array<string, mixed>}> */
    protected array $after = [];

    protected bool $isOptional = false;

    public static function make(?string $key = null): static
    {
        $step = app(static::class);

        if ($key !== null) {
            $step->key($key);
        }

        return $step;
    }

    public function key(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function target(string|TutorialTarget $target): static
    {
        $this->target = is_string($target) ? TutorialTarget::custom($target) : $target;

        return $this;
    }

    /**
     * @param  class-string  $resourceOrPage
     */
    public function targetNavigation(string $resourceOrPage): static
    {
        return $this->target(TutorialTarget::navigation($resourceOrPage));
    }

    public function targetRenderHook(string $key): static
    {
        return $this->target(TutorialTarget::renderHook($key));
    }

    public function targetComponent(string $key): static
    {
        return $this->target(TutorialTarget::component($key));
    }

    /**
     * @param  class-string|null  $owner
     */
    public function targetAction(string $action, ?string $owner = null): static
    {
        return $this->target(TutorialTarget::action($action, $owner));
    }

    /**
     * @param  class-string|null  $page
     */
    public function targetPage(?string $page = null): static
    {
        return $this->target(TutorialTarget::page($page));
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function optional(bool $condition = true): static
    {
        $this->isOptional = $condition;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function before(string $action, array $parameters = []): static
    {
        $this->before[] = ['action' => $action, 'parameters' => $parameters];

        return $this;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function after(string $action, array $parameters = []): static
    {
        $this->after[] = ['action' => $action, 'parameters' => $parameters];

        return $this;
    }

    public function beforeOpenSidebar(?string $selector = null): static
    {
        return $this->before('sidebar.open', array_filter([
            'selector' => $selector,
        ]));
    }

    public function afterOpenSidebar(): static
    {
        return $this->after('sidebar.opened');
    }

    public function beforeOpenProfileMenu(?string $selector = null): static
    {
        return $this->before('profile-menu.open', array_filter([
            'selector' => $selector,
        ]));
    }

    public function beforeClick(string $selector): static
    {
        return $this->before('click', ['selector' => $selector]);
    }

    public function beforeWaitFor(string $selector): static
    {
        return $this->before('wait-for', ['selector' => $selector]);
    }

    public function afterClick(string $selector): static
    {
        return $this->after('click', ['selector' => $selector]);
    }

    public function afterHide(string $selector): static
    {
        return $this->after('hide', ['selector' => $selector]);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function beforeOpenModal(array $parameters = []): static
    {
        return $this->before('modal.open', $parameters);
    }

    public function beforeOpenDropdown(string $selector): static
    {
        return $this->before('dropdown.open', ['selector' => $selector]);
    }

    public function beforeOpenCollapsible(string $trigger, string $panel): static
    {
        return $this->before('collapsible.open', [
            'trigger' => $trigger,
            'panel' => $panel,
        ]);
    }

    /**
     * @return array{key: string|null, target: array<string, mixed>|null, title: string|null, description: string|null, before: list<array{action: string, parameters: array<string, mixed>}>, after: list<array{action: string, parameters: array<string, mixed>}>, optional: bool}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'target' => $this->target?->toArray(),
            'title' => $this->title,
            'description' => $this->description,
            'before' => $this->before,
            'after' => $this->after,
            'optional' => $this->isOptional,
        ];
    }
}
