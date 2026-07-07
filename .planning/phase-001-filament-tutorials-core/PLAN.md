---
phase: filament-tutorials-core
plan: harden-core-plugin-contract
type: execute
wave: 1
depends_on: []
files_modified:
  - README.md
  - src/Contracts/HasFilamentTutorials.php
  - src/FilamentTutorial.php
  - src/FilamentTutorialsPlugin.php
  - src/FilamentTutorialsServiceProvider.php
  - src/Support/TutorialDiscovery.php
  - src/Support/TutorialManager.php
  - src/TutorialStep.php
  - src/TutorialTarget.php
  - workbench/app/Filament/Pages/WorkbenchDashboard.php
  - workbench/app/Filament/Tutorials/*
  - workbench/app/Providers/Filament/AdminPanelProvider.php
  - tests/Feature/*
  - tests/Unit/*
  - .github/workflows/*
  - composer.json
  - phpstan.neon
autonomous: true
requirements:
  - FT-001-package-skeleton
  - FT-002-public-authoring-api
  - FT-003-panel-discovery
  - FT-004-inline-page-resource-contract
  - FT-005-workbench-release-gates
must_haves:
  truths:
    - This package is a Filament plugin, not an application-specific tutorial registry.
    - Tutorial authors should be able to register tutorials through panel discovery, explicit plugin registration, or a Page/Resource contract.
    - Stable target keys are package contracts; internal Filament `.fi-*` selectors are never tutorial contracts.
    - Discovery and registration must be isolated by panel id.
    - Duplicate tutorial keys must have one documented and tested behavior.
    - README examples must compile against the current public API.
  artifacts:
    - Executable GSD task list for the core package contract.
    - Unit and feature tests proving explicit registration, discovery, inline contract, target serialization, duplicate handling, and panel isolation.
    - Workbench example covering discovered and inline tutorials without manually re-registering inline tutorial output as an explicit plugin tutorial.
    - CI/release gates that match the package workbench workflow.
  key_links:
    - src/FilamentTutorialsPlugin.php
    - src/Contracts/HasFilamentTutorials.php
    - src/Support/TutorialDiscovery.php
    - src/Support/TutorialManager.php
    - tests/Feature/PluginRegistrationTest.php
---

# Phase 001 Plan: Core Plugin Contract

## Goal

Harden the initial `coringawc/filament-tutorials` scaffold into a reliable Filament plugin foundation: stable public PHP API, panel-scoped discovery, inline Page/Resource tutorial contract, deterministic registration semantics, Workbench proof, and release gates.

## Review Status

Reviewed on 2026-07-07 with five GSD specialist subagents:

- Filament plugin architecture.
- Workbench, Composer, Testbench, CI, and release verification.
- Driver.js runtime, assets, render hooks, accessibility, and browser testing.
- Stable target injection, navigation, actions, modals, sidebar, and menus.
- Author DX, README/API examples, privacy, progress, and SiasgFacil compatibility.

All reviewers rejected the original narrative plan as non-executable. This plan incorporates the blockers that must be resolved before implementation proceeds.

## Decisions

- Phase 001 stays backend/package-contract focused. It must not implement Driver.js runtime yet.
- Phase 001 must still freeze target payload grammar enough that Phase 002/003 do not invent incompatible selectors.
- The default tutorial class discovery path is panel based: `app/Filament/{StudlyPanelId}/Tutorials`.
- Inline Page/Resource tutorials are first-class and must be collected by the plugin, not manually passed through `->tutorials(Page::tutorials())` in the host panel.
- Duplicate keys must be deterministic. The preferred behavior is fail fast with a clear exception unless a future explicit override API is added.
- `TutorialManager` lifecycle must be safe for Laravel/Filament and Octane. If it stays `scoped`, tests or documentation must prove the panel registration lifecycle still works.
- CI may run outside the local Docker workbench for GitHub Actions, but local verification and documented developer commands must use `./packages/workbench/bin/...`.

## Target Grammar Contract

The implementation must document and test these payload forms before Phase 002 consumes them:

| Target helper | Payload type | Stable key source |
| --- | --- | --- |
| `targetPage()` | `page` | current page class or explicit page class |
| `targetNavigation(ResourceOrPage::class)` | `navigation` | class-string for the resource/page |
| `targetAction('create', Owner::class)` | `action` | action name plus owner class |
| `target('custom.key')` | `custom` | explicit author-provided key |

The DOM selector produced later must be based on `data-tour`, not `.fi-*`.

<tasks>
<task type="auto" tdd="true">
  <name>Task 1: Stabilize public authoring API and README examples</name>
  <files>
    - README.md
    - src/FilamentTutorial.php
    - src/TutorialStep.php
    - src/TutorialTarget.php
    - tests/Unit/FilamentTutorialTest.php
    - tests/Unit/TutorialStepTest.php
    - tests/Unit/TutorialTargetTest.php
  </files>
  <action>
    Fix README examples so inherited static properties match the package API, especially `protected static ?string $page`. Add tests for `FilamentTutorial::getKey()`, `forPage()`, inherited static page resolution, `steps()`, `autoStart()`, `adapter()`, and `toArray()`. Add tests for every `TutorialTarget` helper and every lifecycle helper on `TutorialStep`.
  </action>
  <verify>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Unit</automated>
    <automated>./packages/workbench/bin/sail phpstan --memory-limit=1G</automated>
  </verify>
  <done>
    The public API examples compile conceptually against the class signatures, and unit tests lock the serialized tutorial payload that later phases consume.
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 2: Implement and prove panel tutorial registration paths</name>
  <files>
    - src/Contracts/HasFilamentTutorials.php
    - src/FilamentTutorialsPlugin.php
    - src/Support/TutorialDiscovery.php
    - src/Support/TutorialManager.php
    - tests/Feature/PluginRegistrationTest.php
    - tests/Feature/TutorialDiscoveryTest.php
    - tests/Feature/InlineTutorialRegistrationTest.php
  </files>
  <action>
    Make explicit plugin tutorials, discovered tutorial classes, and inline Page/Resource tutorials coexist in one panel. Inspect Filament v5 source/docs before choosing how to enumerate panel pages/resources. If Filament does not expose a stable public enumeration API, add a small collector abstraction and document the limitation in this plan's implementation notes instead of relying on private properties. Normalize `FilamentTutorial`, class-string, array, and null returns from `HasFilamentTutorials::tutorials()`.
  </action>
  <verify>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Feature/PluginRegistrationTest.php tests/Feature/TutorialDiscoveryTest.php tests/Feature/InlineTutorialRegistrationTest.php</automated>
  </verify>
  <done>
    Tests prove explicit registration, default discovery, custom discovery path/namespace, disabled discovery, absent discovery directory, and inline Page/Resource tutorials all work without manual re-registration of inline results.
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 3: Define duplicate key, ordering, and panel isolation behavior</name>
  <files>
    - src/Support/TutorialManager.php
    - src/Support/TutorialDiscovery.php
    - src/FilamentTutorialsPlugin.php
    - tests/Feature/TutorialManagerTest.php
    - tests/Feature/PluginRegistrationTest.php
  </files>
  <action>
    Define deterministic behavior for duplicate tutorial keys. Prefer failing fast with an exception that includes panel id and key. Preserve deterministic discovery ordering. Ensure tutorials registered for one panel are not visible from another panel and that manager lifecycle works with the service provider binding used by the package.
  </action>
  <verify>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Feature/TutorialManagerTest.php tests/Feature/PluginRegistrationTest.php</automated>
  </verify>
  <done>
    Tests prove duplicate key behavior, deterministic ordering, and panel isolation.
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 4: Expand Workbench proof for real package usage</name>
  <files>
    - workbench/app/Filament/Pages/WorkbenchDashboard.php
    - workbench/app/Filament/Tutorials/*
    - workbench/app/Providers/Filament/AdminPanelProvider.php
    - workbench/resources/views/*
    - tests/Feature/WorkbenchTutorialsTest.php
  </files>
  <action>
    Add one tutorial class under the default panel discovery path and one inline tutorial on a workbench Page or Resource implementing `HasFilamentTutorials`. Update the panel provider so workbench proves discovery and inline collection separately from explicit registration. Keep any manual explicit tutorial registration as a third, separate example if useful.
  </action>
  <verify>
    <automated>./packages/workbench/bin/workbench up -d</automated>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Feature/WorkbenchTutorialsTest.php</automated>
  </verify>
  <done>
    Workbench demonstrates the three registration modes the package promises, and tests prove the registered payloads come from the intended path.
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 5: Align CI and local release gates with package reality</name>
  <files>
    - composer.json
    - phpstan.neon
    - .github/workflows/run-tests.yml
    - .github/workflows/phpstan.yml
    - .github/workflows/fix-php-code-style-issues.yml
    - AGENTS.md
  </files>
  <action>
    Add release gates for `composer validate --strict`, Pest, PHPStan, and Pint. Ensure PR triggers cover tests/static analysis. Make the workflow style gate non-mutating for pull requests. Document local commands through `./packages/workbench/bin/...`. Reconcile the submodule workbench dependency versions with Laravel 13/Testbench 11 or document why the submodule's own composer metadata is not used by the package runtime.
  </action>
  <verify>
    <automated>./packages/workbench/bin/sail composer validate --strict</automated>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact</automated>
    <automated>./packages/workbench/bin/sail pint --dirty</automated>
    <automated>./packages/workbench/bin/sail phpstan --memory-limit=1G</automated>
  </verify>
  <done>
    Local gates pass, CI gates match the package support target, and the workbench version story is no longer ambiguous.
  </done>
</task>

<task type="auto" tdd="false">
  <name>Task 6: Record Phase 002 gates before runtime work starts</name>
  <files>
    - .planning/phase-002-runtime-and-targets/PLAN.md
    - README.md
  </files>
  <action>
    Keep runtime implementation out of Phase 001, but record the decisions Phase 002 must verify: official Driver.js API, panel-scoped Filament assets/render hook, vanilla module vs Alpine lifecycle, light/dark styling, keyboard/focus/reduced-motion behavior, browser smoke tests, and no `.fi-*` selector contracts.
  </action>
  <verify>
    <manual>Read the Phase 002 plan and confirm every HIGH/MEDIUM runtime review finding is either converted into a task or explicitly deferred with a reason.</manual>
  </verify>
  <done>
    Phase 002 can start without reopening the runtime strategy discussion from zero.
  </done>
</task>
</tasks>

## Final Verification

```bash
./packages/workbench/bin/workbench up -d
./packages/workbench/bin/sail composer validate --strict
./packages/workbench/bin/sail php vendor/bin/pest --compact
./packages/workbench/bin/sail pint --dirty
./packages/workbench/bin/sail phpstan --memory-limit=1G
```
