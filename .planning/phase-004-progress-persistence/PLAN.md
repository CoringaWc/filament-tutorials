# Phase 004: Progress Persistence

## Goal

Persist tutorial lifecycle state per authenticated user so first-run tutorials stop after completion or dismissal and can be restarted intentionally.

## Scope

- Add package config for progress route, middleware, table, and enablement.
- Add a package migration for tutorial progress records.
- Add an Eloquent model for progress state.
- Add a final action endpoint for runtime progress events without accepting browser-sent user identity.
- Add metadata sanitization to prevent accidental persistence of sensitive browser or portal data.
- Add payload state so auto-start is suppressed after completion or dismissal.
- Add runtime `fetch` calls for started, completed, and dismissed events.
- Prove user isolation, validation, route behavior, metadata allowlist, and auto-start state through tests.

## Verification

- `./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Feature/TutorialProgressActionTest.php tests/Feature/TutorialRuntimePayloadTest.php tests/Architecture/RuntimeAssetContractTest.php`
- `./packages/workbench/bin/sail npm run check`
- `./packages/workbench/bin/sail npm run build`
- `./packages/workbench/bin/sail composer run build`
- `./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Unit tests/Feature tests/Architecture/RuntimeAssetContractTest.php`
- `./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Browser/WorkbenchTutorialBrowserTest.php`
- `./packages/workbench/bin/sail pint --dirty --format agent`
- `./packages/workbench/bin/sail php vendor/bin/phpstan analyse --memory-limit=1G`
