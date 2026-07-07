# Filament Tutorials

## Objective

Build a reusable Filament v5 plugin that provides interactive tutorials for panels, pages, resources, actions, navigation items, forms, tables, and other Filament UI concepts using Driver.js and stable targets.

## Core Decisions

- The package name is `coringawc/filament-tutorials`.
- The public entrypoint is `FilamentTutorialsPlugin::make()` registered on a Filament panel.
- The plugin discovers tutorial classes from the panel convention by default, such as `app/Filament/App/Tutorials`.
- Pages and resources may expose small inline tutorials through `HasFilamentTutorials::tutorials()`.
- Complex flows should live in dedicated classes extending `FilamentTutorial`.
- Driver.js remains the tour engine. Alpine may coordinate local lifecycle only after implementation research confirms that it improves integration.
- Stable targets are owned by this plugin and must not rely on Filament internal `.fi-*` selectors.

