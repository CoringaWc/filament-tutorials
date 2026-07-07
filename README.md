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

Create a tutorial class when the flow deserves its own file:

```php
use CoringaWc\FilamentTutorials\FilamentTutorial;
use CoringaWc\FilamentTutorials\TutorialStep;

final class ContractingListTutorial extends FilamentTutorial
{
    protected static string $page = ListContractingDrafts::class;

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

For a small page-only guide, return an inline tutorial from the page/resource:

```php
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
```

## Workbench

The repository uses `coringawc/filament-plugin-workbench`.

```bash
./packages/workbench/bin/workbench up -d
./packages/workbench/bin/sail php vendor/bin/pest
```
