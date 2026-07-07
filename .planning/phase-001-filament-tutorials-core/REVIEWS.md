# Phase 001 Review: Filament Tutorials Core

Date: 2026-07-07

## Method

Five GSD specialist subagents reviewed the original Phase 001 plan in parallel:

- Filament plugin architecture.
- Workbench, Composer, Testbench, CI, and release verification.
- Driver.js runtime, assets, render hooks, accessibility, and browser testing.
- Stable target injection for Filament primitives.
- Author DX, README/API examples, privacy, progress, and SiasgFacil compatibility.

## Verdict

Rejected before execution.

The original file was a useful project brief, but not an executable GSD plan. Every reviewer found the same primary blocker: missing GSD frontmatter, missing `must_haves`, missing `files_modified`, and zero `<task>` entries.

## Consolidated Findings

### HIGH

1. The original `PLAN.md` was not executable by GSD.
   - It had no structured task list.
   - It did not tell an executor which files to edit, how to verify changes, or when a task was done.

2. The promised Page/Resource inline contract was not actually planned.
   - `HasFilamentTutorials` existed, but the Workbench example manually passed page tutorial output through explicit plugin registration.
   - A page or resource implementing the contract could still be ignored by the package.

3. Panel discovery was not proven.
   - The plugin calculated a default `app/Filament/{Panel}/Tutorials` location.
   - Workbench disabled discovery, so the default behavior was not covered.

4. Stable target materialization was not planned.
   - `TutorialTarget` serialized data, but no plan connected target payloads to `data-tour` attributes.
   - No plan blocked `.fi-*` selectors as tutorial contracts.

5. Runtime/assets work had no executable gate.
   - Driver.js, Filament assets, render hooks, Alpine vs vanilla, accessibility, and browser tests were only listed as spike questions.

6. The README had an API example that could fail in PHP.
   - It used `protected static string $page`, while the base class declares `protected static ?string $page = null`.

7. CI/release gates were incomplete.
   - Composer validation, PR static analysis, non-mutating style checks, and clean Workbench smoke were not part of the plan.

### MEDIUM

1. Public API tests were too narrow.
   - Only `TutorialStep` serialization was covered.
   - `FilamentTutorial`, `TutorialTarget`, duplicate keys, panel isolation, and discovery variants needed tests.

2. Duplicate tutorial key behavior was undefined.
   - Current manager overwrote by key; the plan did not say whether that is intended.

3. Progress/privacy was deferred without a contract.
   - Even before persistence exists, the package should state that tutorial payloads and progress keys do not carry CPF, raw tenant identifiers, cookies, tokens, or browser/session storage data.

4. Browser coverage was placed too late.
   - The first phase that introduces real Driver.js/runtime behavior must include browser smoke and screenshots.

5. Workbench PHPStan coverage did not include the Workbench app.
   - If Workbench is the proof surface, important Workbench integration files need verification or an explicit reason to exclude them.

## Convergence Changes Applied To The Plan

- Replaced the narrative `PLAN.md` with GSD frontmatter and executable tasks.
- Added explicit tasks for public API tests, discovery, inline contract collection, duplicate key behavior, Workbench proof, and CI/release gates.
- Added Phase 002 as the runtime/targets plan instead of leaving runtime questions as unowned spikes.
- Added target grammar requirements that Phase 002/003 must preserve.
- Fixed the README static property type example.

## Remaining Risks After Convergence

- The exact Filament v5 API for enumerating panel pages/resources must be verified during implementation.
- The exact Filament v5 API for package assets/render hooks must be verified during Phase 002.
- Driver.js accessibility and focus behavior must be tested in a browser, not assumed from API docs.
- Dynamic targets for modals, menus, and sidebar may require MutationObserver or Filament/Livewire lifecycle hooks.

## Status

Plan convergence completed. Phase 001 is ready for implementation, but the implementation itself has not been executed in this review pass.
