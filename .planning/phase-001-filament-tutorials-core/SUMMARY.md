# Phase 001 Summary: Core Plugin Contract

Completed on 2026-07-07.

## Delivered

- Public tutorial API tests for `FilamentTutorial`, `TutorialStep`, and `TutorialTarget`.
- Panel-scoped `TutorialManager` with duplicate key fail-fast behavior.
- `InlineTutorialCollector` for Page/Resource classes implementing `HasFilamentTutorials`.
- Plugin boot integration that collects inline tutorials after the panel is fully configured.
- Default discovery namespace based on `app()->getNamespace()`, so Workbench and normal Laravel apps both work.
- Workbench examples for explicit, discovered, and inline tutorial registration.
- CI gates for Composer validation, Pest, PHPStan on PRs, and non-mutating Pint checks.
- PHPStan coverage expanded to `workbench/app`.
- README updated with discovery, explicit registration, and inline Page examples.

## Verification

```bash
./packages/workbench/bin/workbench up -d
./packages/workbench/bin/sail composer validate --strict
./packages/workbench/bin/sail php vendor/bin/pest --compact
./packages/workbench/bin/sail pint --dirty
./packages/workbench/bin/sail phpstan --memory-limit=1G
```

All commands passed.

## Next

Execute Phase 002: Driver.js runtime, panel-scoped assets, and stable `data-tour` target materialization.
