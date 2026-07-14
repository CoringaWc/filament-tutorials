# Filament Tutorials

Interactive tutorials for Filament panels, powered by Driver.js and stable Filament-aware targets.

The package is built for Filament v5 panels. It registers panel-scoped assets, renders one runtime bridge, exposes a generic launcher only on pages with tutorials, and can persist first-run progress per authenticated user.

## Installation

```bash
composer require coringawc/filament-tutorials
php artisan vendor:publish --tag=filament-tutorials-config
php artisan vendor:publish --tag=filament-tutorials-migrations
php artisan migrate
php artisan filament:assets
```

The migration is required only when `filament-tutorials.progress.enabled` is `true`.

## Panel Registration

Register the plugin in a panel:

```php
use CoringaWc\FilamentTutorials\FilamentTutorialsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(
            FilamentTutorialsPlugin::make()
        );
}
```

By default, the plugin discovers tutorials in `app/Filament/{PanelId}/Tutorials`, using the panel id in StudlyCase and the application namespace. For example, the `app` panel scans `App\Filament\App\Tutorials`, and the `admin` panel scans `App\Filament\Admin\Tutorials`.

The page tutorial launcher is rendered by default immediately before the Filament user menu, placing the question icon to the left of the profile dropdown in the topbar. You may disable or customize that launcher from the panel declaration:

```php
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;

FilamentTutorialsPlugin::make()
    ->launcher(
        renderHook: PanelsRenderHook::USER_MENU_BEFORE,
        icon: Heroicon::OutlinedQuestionMarkCircle,
        tooltip: 'Abrir guia da página',
        label: 'Abrir guia da página',
    );

FilamentTutorialsPlugin::make()
    ->withoutLauncher();
```

Shortcut methods are also available: `launcherRenderHook()`, `launcherIcon()`, `launcherTooltip()`, and `launcherLabel()`.

You may override discovery:

```php
FilamentTutorialsPlugin::make()
    ->discoverTutorials(
        in: app_path('Filament/App/ProductGuides'),
        for: 'App\\Filament\\App\\ProductGuides',
    );
```

Create a tutorial class when the flow deserves its own file:

```php
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;

final class ContractingListTutorial extends FilamentTutorial
{
    protected static ?string $page = ListContractingDrafts::class;

    public function steps(?array $steps = null): array|static
    {
        return [
            TutorialStep::make('sidebar-contractings')
                ->targetNavigation(ContractingDraftResource::class)
                ->beforeOpenSidebar(),
        ];
    }
}
```

You can also register a tutorial explicitly from the panel provider, which is useful for dashboards or package-defined pages:

```php
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;

$panel->plugin(
    FilamentTutorialsPlugin::make()
        ->tutorials(
            FilamentTutorial::make('contracts-overview')
                ->steps([
                    TutorialStep::make('intro')->targetPage(),
                ]),
        )
);
```

For a small page-only guide, return an inline tutorial from a Page or Resource:

```php
use CoringaWc\FilamentTutorials\Contracts\HasFilamentTutorials;
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;
use Filament\Pages\Page;

final class ListContractingDrafts extends Page implements HasFilamentTutorials
{
    public static function tutorials(): FilamentTutorial
    {
        return FilamentTutorial::make()
            ->steps([
                TutorialStep::make('intro')
                    ->targetPage()
                    ->title('Overview')
                    ->description('See the main information on this page.'),
            ]);
    }
}
```

## Targets

Use package helpers instead of Filament internal CSS classes. The runtime targets stable `data-tour` attributes.

```php
TutorialStep::make('page')
    ->targetPage(ListContractingDrafts::class);

TutorialStep::make('navigation')
    ->targetNavigation(ContractingDraftResource::class);

TutorialStep::make('render-hook')
    ->targetRenderHook('global-search.before');

TutorialStep::make('action')
    ->targetAction('create', ListContractingDrafts::class);

TutorialStep::make('field')
    ->targetComponent('contracting.form.title');
```

When a host component needs to expose a target explicitly, use `TutorialTargetAttributes`:

```php
use CoringaWc\FilamentTutorials\TutorialTargetAttributes;

TextInput::make('title')
    ->extraInputAttributes(
        TutorialTargetAttributes::component('contracting.form.title')
    );

CreateAction::make()
    ->extraAttributes(
        TutorialTargetAttributes::action('create', ListContractingDrafts::class)
    );
```

## Lifecycle Actions

Lifecycle actions prepare the UI before a step is highlighted. They are intentionally small and explicit:

```php
TutorialStep::make('open-modal')
    ->target('contracting.create-modal')
    ->beforeOpenModal([
        'selector' => TutorialTargetAttributes::selector(
            TutorialTargetAttributes::action('create', ListContractingDrafts::class),
        ),
    ]);

TutorialStep::make('open-dropdown')
    ->target('contracting.filters.menu')
    ->beforeOpenDropdown('[data-tour="contracting.filters.trigger"]');

TutorialStep::make('open-collapsible')
    ->target('contracting.advanced.panel')
    ->beforeOpenCollapsible(
        trigger: '[data-tour="contracting.advanced.trigger"]',
        panel: '[data-tour="contracting.advanced.panel"]',
    );
```

Available helpers include `beforeOpenSidebar()`, `beforeOpenProfileMenu()`, `beforeClick()`, `beforeWaitFor()`, `afterClick()`, and `afterHide()`.

## Progress

Progress is recorded per authenticated user, panel, and tutorial. The browser never sends a user id; the endpoint resolves the authenticated user server-side.

The runtime records:

- `started` when the tutorial starts.
- `completed` when the user finishes.
- `dismissed` when the user closes early.
- `restarted` when a completed or dismissed tutorial is opened again from the launcher.

If a tutorial has `->autoStart()`, it starts automatically until that user completes or dismisses it:

```php
FilamentTutorial::make('first-run-contracting')
    ->forPage(ListContractingDrafts::class)
    ->autoStart()
    ->steps([
        TutorialStep::make('intro')
            ->targetPage(ListContractingDrafts::class)
            ->title('Contractings')
            ->description('Review and create contractings from this page.'),
    ]);
```

Progress metadata is sanitized by allowlist before persistence. Do not send credentials, cookies, tokens, raw browser fingerprints, or portal session material in tutorial metadata.

Tutorial, panel, and explicit step keys used by progress persistence must start with a lowercase letter or number and may contain only lowercase letters, numbers, `.`, `_`, and `-`. Invalid or duplicated keys fail during panel registration so a tutorial cannot appear to work while silently losing progress.

### Progress endpoint security

The progress endpoint uses the requested panel's configured authentication guard and never accepts a user id from JavaScript. In non-local environments, the authenticated model must implement Filament's `FilamentUser` contract and pass `canAccessPanel()` for that panel. This mirrors Filament's own panel access rule and prevents a session from writing progress for a panel it cannot open.

The default middleware stack is:

```php
['web', 'throttle:filament-tutorials-progress']
```

Keep the `web` middleware, because the runtime reads Laravel's standard CSRF meta token and the endpoint requires session authentication and CSRF validation. The named limiter defaults to 120 requests per 60 seconds per client IP and can be adjusted in `filament-tutorials.progress.rate_limit`. If you replace the middleware array, provide equivalent session, CSRF, and rate-limit protection.

When persistence is disabled, unavailable, rejected, or rate-limited, the runtime leaves the tutorial usable and dispatches a browser event instead of exposing response details in the console:

```js
document.addEventListener('filament-tutorials:progress-failed', (event) => {
  const { event: progressEvent, status } = event.detail
})
```

## Workbench

The repository uses `coringawc/filament-plugin-workbench`.

```bash
./packages/workbench/bin/workbench up -d
./packages/workbench/bin/sail php vendor/bin/pest
```

Useful package gates:

```bash
./packages/workbench/bin/sail composer validate --strict
./packages/workbench/bin/sail composer audit
./packages/workbench/bin/sail npm run check
./packages/workbench/bin/sail npm audit --audit-level=moderate
./packages/workbench/bin/sail npm run build
./packages/workbench/bin/sail composer run build
./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Architecture tests/Feature tests/Unit
./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Browser
./packages/workbench/bin/sail php vendor/bin/phpstan analyse --memory-limit=1G
./packages/workbench/bin/sail php vendor/bin/rector process --dry-run --no-progress-bar
./packages/workbench/bin/sail pint --dirty --format agent
```
