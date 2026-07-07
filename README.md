# Filament Tutorials

Interactive tutorials for Filament panels, powered by Driver.js and stable Filament-aware targets.

This repository starts as a package scaffold with Workbench-ready tests and a GSD implementation plan in `.planning/`.

## Intended API

Register the plugin in a panel:

```php
use CoringaWc\FilamentTutorials\FilamentTutorialsPlugin;

$panel->plugin(
    FilamentTutorialsPlugin::make()
        ->discoverTutorials()
);
```

By default, `discoverTutorials()` scans `app/Filament/{PanelId}/Tutorials`, using the panel id in StudlyCase and the application's namespace. For example, a normal Laravel app with the `app` panel uses `App\Filament\App\Tutorials`, and the `admin` panel uses `App\Filament\Admin\Tutorials`.

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

You can also register a tutorial explicitly from the panel provider:

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

For a small page-only guide, return an inline tutorial from the page/resource:

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

## Workbench

The repository uses `coringawc/filament-plugin-workbench`.

```bash
./packages/workbench/bin/workbench up -d
./packages/workbench/bin/sail php vendor/bin/pest
```
