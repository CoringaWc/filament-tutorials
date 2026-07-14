# Filament Tutorials

Interactive tutorials for Filament v5 panels, powered by Driver.js and stable,
Filament-aware targets.

The package registers its assets only on panels where the plugin is enabled,
discovers tutorial classes by panel, renders the tutorial launcher only on pages
that have a guide, and can persist first-run progress per authenticated user.

## Installation

Install the package and publish its configuration and migration:

```bash
composer require coringawc/filament-tutorials
php artisan vendor:publish --tag=filament-tutorials-config
php artisan vendor:publish --tag=filament-tutorials-migrations
php artisan migrate
php artisan filament:assets
```

The migration is required while `filament-tutorials.progress.enabled` is
`true`. Progress persistence is enabled by default.

## Quick Start

### 1. Register the plugin in the panel

Open the panel provider where the tutorial should be available, such as
`app/Providers/Filament/AppPanelProvider.php`, and register the plugin in the
`panel()` method:

```php
use CoringaWc\FilamentTutorials\FilamentTutorialsPlugin;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        ->id('app')
        ->plugin(
            FilamentTutorialsPlugin::make()
        );
}
```

The value passed to `Panel::id()` determines the default tutorial directory and
namespace. It is not derived from the provider class name.

| Panel id | Default directory | Default namespace |
| --- | --- | --- |
| `app` | `app/Filament/App/Tutorials` | `App\Filament\App\Tutorials` |
| `admin` | `app/Filament/Admin/Tutorials` | `App\Filament\Admin\Tutorials` |

For another panel id, convert it to StudlyCase. A panel with id
`agency-portal`, for example, discovers classes from
`app/Filament/AgencyPortal/Tutorials` under the
`App\Filament\AgencyPortal\Tutorials` namespace.

### 2. Create the tutorial class

The package does not currently provide a dedicated
`make:filament-tutorial` Artisan command. Create the class manually, or use
Laravel's generic `make:class` command:

```bash
php artisan make:class Filament/App/Tutorials/ContractingListTutorial --no-interaction
```

For the `app` panel, the resulting file must be:

```text
app/Filament/App/Tutorials/ContractingListTutorial.php
```

Replace the generated class with a tutorial that extends `FilamentTutorial`:

```php
<?php

declare(strict_types=1);

namespace App\Filament\App\Tutorials;

use App\Filament\App\Resources\ContractingDraftResource;
use App\Filament\App\Resources\ContractingDraftResource\Pages\ListContractingDrafts;
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;

final class ContractingListTutorial extends FilamentTutorial
{
    protected static ?string $page = ListContractingDrafts::class;

    /**
     * @param  list<TutorialStep>|null  $steps
     * @return ($steps is null ? list<TutorialStep> : static)
     */
    public function steps(?array $steps = null): array|static
    {
        if ($steps !== null) {
            return parent::steps($steps);
        }

        return [
            TutorialStep::make('contracting-drafts.page-overview')
                ->targetPage(ListContractingDrafts::class)
                ->title(__('Contractings'))
                ->description(__('Review the main information available on this page.')),

            TutorialStep::make('contracting-drafts.navigation-item')
                ->targetNavigation(ContractingDraftResource::class)
                ->beforeOpenSidebar()
                ->title(__('Contractings menu'))
                ->description(__('Use this menu to return to your contractings.')),
        ];
    }
}
```

No additional registration is necessary for this class. Because the file and
namespace follow the convention for the `app` panel, the plugin discovers it
automatically.

### 3. Open the page

Open the page represented by `ListContractingDrafts::class`. The question-mark
launcher appears beside the Filament user menu because that page now has a
tutorial. It is intentionally hidden on pages without a registered tutorial.

If the launcher does not appear, see [Troubleshooting](#troubleshooting).

## Understanding Tutorial And Step Keys

The value passed to `TutorialStep::make()` is the step's own identifier. It is
not a target type, CSS selector, translation key, title, or reserved word.

For example, this is valid:

```php
TutorialStep::make('contracting-draft-page')
    ->targetPage(ListContractingDrafts::class);
```

You do **not** need to write `page` inside `make()` when using `targetPage()`.
Names such as `page`, `navigation`, `action`, and `field` in short examples are
only illustrative keys. The method called after `make()` determines what the
step highlights.

Prefer descriptive, stable keys:

```php
TutorialStep::make('contracting-drafts.page-overview');
TutorialStep::make('contracting-drafts.navigation-item');
TutorialStep::make('contracting-drafts.create-action');
TutorialStep::make('contracting-drafts.title-field');
```

There are three separate identifiers to understand:

| Identifier | Example | Purpose |
| --- | --- | --- |
| Tutorial key | `contracting-list` | Identifies the complete tutorial and its saved progress. |
| Step key | `contracting-drafts.title-field` | Identifies one step inside that tutorial. |
| Target key | `contracting.form.title` | Connects a target helper to a rendered `data-tour` attribute. |

For discovered tutorial classes, the tutorial key is derived from the class
name. `ContractingListTutorial` becomes `contracting-list`. You may override it
with `->key('another-stable-key')` when registering an object directly.

For inline tutorials, always provide the tutorial key explicitly:

```php
FilamentTutorial::make('contracting-drafts.list');
```

Keys used for persisted progress must:

- start with a lowercase letter or number;
- contain only lowercase letters, numbers, `.`, `_`, and `-`;
- remain stable after release;
- be unique within their scope. Step keys must be unique inside their tutorial.

Use keys as technical identifiers. Put user-facing copy in `title()` and
`description()` instead of using translated text as a key.

## Choosing A Registration Style

### Discovered class

Use a discovered class for normal page tutorials and any flow with multiple
steps. Place it in the panel's `Tutorials` directory as shown in the quick
start. This keeps Page and Resource classes focused on their Filament concerns.

### Inline tutorial

For a very small guide, a Filament Page, Resource, or Resource Page can implement
`HasFilamentTutorials` directly:

```php
use CoringaWc\FilamentTutorials\Contracts\HasFilamentTutorials;
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;
use Filament\Pages\Page;

final class ListContractingDrafts extends Page implements HasFilamentTutorials
{
    public static function tutorials(): FilamentTutorial
    {
        return FilamentTutorial::make('contracting-drafts.list')
            ->steps([
                TutorialStep::make('contracting-drafts.page-overview')
                    ->targetPage()
                    ->title(__('Contractings'))
                    ->description(__('Review the main information available on this page.')),
            ]);
    }
}
```

When an inline tutorial does not call `forPage()`, the plugin associates it with
the Page or Resource that returned it.

### Explicit panel registration

Explicit registration is useful for tutorials supplied by another package or
for a dashboard that does not need its own discovered class:

```php
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\FilamentTutorialsPlugin;
use CoringaWc\FilamentTutorials\TutorialStep;

FilamentTutorialsPlugin::make()
    ->tutorials(
        FilamentTutorial::make('contracts-overview')
            ->forPage(ContractsDashboard::class)
            ->steps([
                TutorialStep::make('contracts-overview.page')
                    ->targetPage(ContractsDashboard::class),
            ]),
    );
```

You may also pass a tutorial class:

```php
FilamentTutorialsPlugin::make()
    ->tutorials(ContractingListTutorial::class);
```

## Targets

Targets tell Driver.js which rendered element should be highlighted. Use the
package helpers instead of Filament's internal `.fi-*` CSS classes.

The step key in `make()` remains a free, descriptive identifier in every
example below:

```php
TutorialStep::make('contracting-drafts.page-overview')
    ->targetPage(ListContractingDrafts::class);

TutorialStep::make('contracting-drafts.navigation-item')
    ->targetNavigation(ContractingDraftResource::class);

TutorialStep::make('contracting-drafts.global-search')
    ->targetRenderHook('global-search.before');

TutorialStep::make('contracting-drafts.create-action')
    ->targetAction('create', ListContractingDrafts::class);

TutorialStep::make('contracting-drafts.title-field')
    ->targetComponent('contracting.form.title');
```

### Targets provided automatically

The plugin exposes these targets without modifying the related Page or Resource:

- `targetPage()` targets the current or supplied Filament Page class.
- `targetNavigation()` targets the navigation item belonging to a registered
  Filament Page or Resource.
- `targetRenderHook()` targets a key configured in
  `filament-tutorials.render_hook_targets`.

The published configuration already includes keys such as
`global-search.before`, `sidebar.nav.start`, `topbar.start`,
`user-menu.before`, and `resource.list.table.before`.

To register another render-hook target, map your stable key to a Filament render
hook in `config/filament-tutorials.php`:

```php
use Filament\View\PanelsRenderHook;

'render_hook_targets' => [
    'global-search.before' => PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
    'page.header.actions.after' => PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER,
],
```

The string passed to `targetRenderHook()` must match the configuration key:

```php
TutorialStep::make('contracting-drafts.header-actions')
    ->targetRenderHook('page.header.actions.after');
```

### Targets that must be exposed on a component

Actions, fields, schema components, widgets, and custom elements need a stable
`data-tour` attribute on the component that will be highlighted. Generate that
attribute with `TutorialTargetAttributes` so it always matches the target
helper.

For a Filament Action:

```php
use CoringaWc\FilamentTutorials\TutorialTargetAttributes;
use Filament\Actions\CreateAction;

CreateAction::make()
    ->extraAttributes(
        TutorialTargetAttributes::action('create', ListContractingDrafts::class)
    );
```

The matching tutorial step is:

```php
TutorialStep::make('contracting-drafts.create-action')
    ->targetAction('create', ListContractingDrafts::class);
```

For a form field:

```php
use CoringaWc\FilamentTutorials\TutorialTargetAttributes;
use Filament\Forms\Components\TextInput;

TextInput::make('title')
    ->extraInputAttributes(
        TutorialTargetAttributes::component('contracting.form.title')
    );
```

The matching tutorial step is:

```php
TutorialStep::make('contracting-drafts.title-field')
    ->targetComponent('contracting.form.title');
```

`TextInput::make('title')` defines the Filament field name.
`TutorialStep::make('contracting-drafts.title-field')` defines the tutorial step
key. `targetComponent('contracting.form.title')` identifies the rendered target.
These values have different responsibilities and do not need to be equal.

For a completely custom target:

```php
$attributes = TutorialTargetAttributes::custom('contracting.summary');

TutorialStep::make('contracting-drafts.summary')
    ->target('contracting.summary');
```

Apply `$attributes` to the element through the component's supported
`extraAttributes()` API.

## Preparing Hidden Or Dynamic Targets

Lifecycle actions make an element visible before Driver.js tries to highlight
it.

### Sidebar navigation

Use `beforeOpenSidebar()` when a navigation item may be hidden by the responsive
sidebar:

```php
TutorialStep::make('contracting-drafts.navigation-item')
    ->targetNavigation(ContractingDraftResource::class)
    ->beforeOpenSidebar();
```

### Modal content

Expose both the action that opens the modal and the field that will be
highlighted. Then use the generated action selector in `beforeOpenModal()`:

```php
TutorialStep::make('contracting-drafts.modal-title-field')
    ->targetComponent('contracting.form.title')
    ->beforeOpenModal([
        'selector' => TutorialTargetAttributes::selector(
            TutorialTargetAttributes::action(
                'create',
                ListContractingDrafts::class,
            ),
        ),
    ]);
```

### Dropdown and collapsible content

```php
TutorialStep::make('contracting-drafts.filters-menu')
    ->target('contracting.filters.menu')
    ->beforeOpenDropdown('[data-tour="contracting.filters.trigger"]');

TutorialStep::make('contracting-drafts.advanced-panel')
    ->target('contracting.advanced.panel')
    ->beforeOpenCollapsible(
        trigger: '[data-tour="contracting.advanced.trigger"]',
        panel: '[data-tour="contracting.advanced.panel"]',
    );
```

Available lifecycle helpers include:

- `beforeOpenSidebar()` and `afterOpenSidebar()`;
- `beforeOpenProfileMenu()`;
- `beforeOpenModal()`;
- `beforeOpenDropdown()`;
- `beforeOpenCollapsible()`;
- `beforeClick()` and `afterClick()`;
- `beforeWaitFor()`;
- `afterHide()`.

Use `->optional()` when a valid page state may legitimately omit the target.
Optional steps are skipped instead of stopping the tutorial.

## First-Run Tutorials

Use `autoStart()` when a tutorial should open automatically the first time the
authenticated user reaches the page:

```php
FilamentTutorial::make('first-run-contracting')
    ->forPage(ListContractingDrafts::class)
    ->autoStart()
    ->steps([
        TutorialStep::make('first-run-contracting.page-overview')
            ->targetPage(ListContractingDrafts::class)
            ->title(__('Contractings'))
            ->description(__('Review and create contractings from this page.')),
    ]);
```

The tutorial stops opening automatically after that user completes or dismisses
it. The launcher remains available so the tutorial can be opened again.

When the dismissal reminder is enabled, the first step includes an Ignore
action. Ignoring jumps to a final reminder that points to the launcher. Configure
this behavior from the panel:

```php
FilamentTutorialsPlugin::make()
    ->dismissalReminder(
        skipLabel: __('Ignore'),
        title: __('You can return whenever you need'),
        description: __('Use the question-mark button to open this guide again.'),
    );
```

Disable it with `->withoutDismissalReminder()`.

## Launcher Configuration

The launcher is rendered by default immediately before the Filament user menu.
You may change its render hook, icon, tooltip, and accessible label:

```php
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;

FilamentTutorialsPlugin::make()
    ->launcher(
        renderHook: PanelsRenderHook::USER_MENU_BEFORE,
        icon: Heroicon::OutlinedQuestionMarkCircle,
        tooltip: __('Open page guide'),
        label: __('Open page guide'),
    );
```

Shortcut methods are also available: `launcherRenderHook()`, `launcherIcon()`,
`launcherTooltip()`, and `launcherLabel()`. Disable the launcher with
`->withoutLauncher()`.

## Custom Discovery Directory

Override the default directory and namespace when the application's structure
requires it:

```php
FilamentTutorialsPlugin::make()
    ->discoverTutorials(
        in: app_path('Filament/App/ProductGuides'),
        for: 'App\\Filament\\App\\ProductGuides',
    );
```

Both `in` and `for` are required when providing a custom location. Disable class
discovery with `->discoverTutorials(enabled: false)` and register tutorials
explicitly when full manual control is preferred.

## Progress And Security

Progress is recorded per authenticated user, panel, and tutorial. The browser
never sends a user id; the endpoint resolves the authenticated user server-side.

The runtime records:

- `started` when the tutorial starts;
- `completed` when the user finishes;
- `dismissed` when the user closes early;
- `restarted` when a completed or dismissed tutorial is opened again.

The progress endpoint uses the panel's authentication guard. Outside the local
environment, the authenticated model must implement Filament's `FilamentUser`
contract and pass `canAccessPanel()` for the requested panel.

The default middleware stack is:

```php
['web', 'throttle:filament-tutorials-progress']
```

Keep the `web` middleware because the endpoint requires session authentication
and CSRF validation. The limiter defaults to 120 requests per 60 seconds per
client IP and can be adjusted in
`filament-tutorials.progress.rate_limit`.

Progress metadata is sanitized by allowlist before persistence. Do not place
credentials, cookies, tokens, browser fingerprints, or portal session material
in tutorial keys, targets, copy, or metadata.

If persistence fails, the tutorial remains usable and dispatches an event:

```js
document.addEventListener('filament-tutorials:progress-failed', (event) => {
  const { event: progressEvent, status } = event.detail
})
```

## Troubleshooting

### The launcher does not appear

Check these points in order:

1. Confirm `FilamentTutorialsPlugin::make()` is registered on the current panel.
2. Confirm the tutorial file is inside the directory derived from the panel id.
3. Confirm the PHP namespace matches that directory exactly.
4. Confirm the tutorial class extends `FilamentTutorial`.
5. Confirm `$page` or `forPage()` references the Filament Page currently open.
6. Run `php artisan optimize:clear` and `php artisan filament:assets` after
   installing or updating the package.

### The tutorial opens but a step cannot find its target

- Confirm the target helper and `TutorialTargetAttributes` use exactly the same
  action owner or component key.
- Add the appropriate lifecycle helper when the target starts hidden.
- Mark the step `optional()` only when the missing target is a valid page state.
- Do not target Filament's generated `.fi-*` classes or DOM position.

### Registration fails with an invalid or duplicated key

Use lowercase stable keys containing only letters, numbers, `.`, `_`, and `-`.
Ensure tutorial keys are unique inside the panel and step keys are unique inside
their tutorial.

## Package Development

The repository uses `coringawc/filament-plugin-workbench`:

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
