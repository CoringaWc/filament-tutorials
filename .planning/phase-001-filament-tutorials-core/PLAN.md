# Phase 001 Plan: Filament Tutorials Core

## Goal

Create the initial plugin foundation so the package can be installed, tested through Workbench, and evolved through small verified phases.

## Implementation Strategy

1. Keep the package structure aligned with `coringawc/filament-action-approvals` and `coringawc/filament-acl`.
2. Use `spatie/laravel-package-tools` for package registration.
3. Use `coringawc/filament-plugin-workbench` as the local runtime/test harness.
4. Expose `FilamentTutorialsPlugin::make()` as the Filament plugin entrypoint.
5. Expose `FilamentTutorial`, `TutorialStep`, `TutorialTarget`, and `HasFilamentTutorials` as the initial authoring API.
6. Support three registration paths:
   - auto-discovered tutorial classes by panel convention;
   - inline page/resource tutorials through `HasFilamentTutorials`;
   - explicit tutorials passed to the plugin for dashboards or special cases.

## Spike Questions To Resolve Before Phase 002

1. Can `NavigationItem::configureUsing()` safely inject `data-tour` attributes while preserving the originating resource/page context?
2. Which Filament component classes expose enough context to resolve stable targets centrally?
3. Should Driver.js be booted by a vanilla module only, or should Alpine own a small lifecycle shell?
4. What is the cleanest package-level way to register panel-scoped Vite assets while preserving host-app override capability?
5. How should tutorial progress be modeled so it works with any authenticatable user model?

## Acceptance Criteria

- Composer metadata points to `coringawc/filament-tutorials`.
- Package service provider is auto-discovered by Laravel.
- `FilamentTutorialsPlugin::make()` can be registered on a panel.
- Workbench panel registers the plugin.
- At least one inline tutorial is registered through the workbench.
- Unit tests cover `TutorialStep` serialization.
- Feature tests cover plugin registration into the current panel manager.
- GSD roadmap is present in `.planning/`.

## Verification Commands

```bash
./packages/workbench/bin/workbench up -d
./packages/workbench/bin/sail php vendor/bin/pest
./packages/workbench/bin/sail pint --dirty
./packages/workbench/bin/sail phpstan --memory-limit=1G
```
