# Phase 003: Expanded Target Injection

## Goal

Expand the package target contract so tutorials can address common Filament and workbench surfaces without fragile selectors at each call site.

## Scope

- Add component target helpers for reusable schemas, widgets, tables, and form fields.
- Add public target attribute helpers for host applications that need explicit `data-tour` attributes.
- Add lifecycle helpers for sidebar, profile menu, dropdown, modal, click, wait, and hide flows.
- Update the workbench to cover profile menu, component targets, action targets, and dynamic surfaces.
- Prove the contract with unit, feature, architecture, build, and browser coverage.

## Verification

- `./packages/workbench/bin/sail npm run check`
- `./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Unit tests/Feature/TutorialRuntimePayloadTest.php tests/Feature/WorkbenchTutorialCoverageTest.php tests/Architecture/RuntimeAssetContractTest.php`
- `./packages/workbench/bin/sail npm run build`
- `./packages/workbench/bin/sail composer run build`
- `./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Browser/WorkbenchTutorialBrowserTest.php`
- `./packages/workbench/bin/sail pint --dirty --format agent`
