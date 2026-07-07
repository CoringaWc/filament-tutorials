---
phase: filament-tutorials-runtime-and-targets
plan: driver-runtime-panel-assets-and-stable-targets
type: execute
wave: 2
depends_on:
  - filament-tutorials-core
files_modified:
  - composer.json
  - package.json
  - vite.config.js
  - config/filament-tutorials.php
  - src/FilamentTutorialsPlugin.php
  - src/FilamentTutorialsServiceProvider.php
  - src/Support/*
  - resources/js/*
  - resources/css/*
  - resources/views/*
  - workbench/app/Filament/*
  - workbench/resources/views/*
  - tests/Feature/*
  - tests/Browser/*
  - tests/Architecture/*
autonomous: true
requirements:
  - FT-006-driver-runtime
  - FT-007-panel-scoped-assets
  - FT-008-stable-data-tour-targets
  - FT-009-runtime-accessibility
  - FT-010-browser-smoke
must_haves:
  truths:
    - Driver.js must be used through its official API.
    - Package JS and CSS must be scoped to panels that register `FilamentTutorialsPlugin::make()`.
    - The runtime must render exactly once per panel page and destroy any active driver before starting another tutorial.
    - Alpine may be used only as a small lifecycle shell if official docs/source review proves it is cleaner than a vanilla module.
    - Tutorial selectors must use `data-tour` attributes generated from stable target keys.
    - Internal Filament classes such as `.fi-*` must not be tutorial contracts.
    - Dynamic targets must wait for the target to exist without `setInterval`, `wire:poll`, or arbitrary sleeps.
  artifacts:
    - Driver.js runtime bridge.
    - Panel-scoped Filament asset registration.
    - Render hook for launcher/runtime payload.
    - Target resolver and data-tour injection strategy.
    - Light/dark Driver.js styling aligned with Filament.
    - Browser smoke test proving the tutorial opens from the Workbench panel.
  key_links:
    - src/FilamentTutorialsPlugin.php
    - src/Support/*
    - resources/js/*
    - resources/css/*
    - resources/views/*
---

# Phase 002 Plan: Runtime, Assets, And Stable Targets

## Goal

Implement the first usable tutorial runtime: a Filament-panel-scoped Driver.js launcher, stable `data-tour` target materialization, accessible popover styling, and browser smoke coverage through Workbench.

## Decisions From Phase 001 Review

- Do not target `.fi-*` classes.
- Do not make each Resource/Page manually define `extraAttributes()` for common Filament primitives.
- Prefer vanilla JavaScript for Driver lifecycle unless a documented Filament/Alpine integration point makes a tiny Alpine shell clearly better.
- Browser verification is mandatory in the same phase that introduces runtime behavior.
- Dynamic modal/menu/sidebar targets require event or `MutationObserver` based waiting, not polling loops.

<tasks>
<task type="auto" tdd="false">
  <name>Task 1: Verify official APIs before implementing runtime hooks</name>
  <files>
    - .planning/phase-002-runtime-and-targets/RESEARCH.md
    - src/FilamentTutorialsPlugin.php
    - src/FilamentTutorialsServiceProvider.php
  </files>
  <action>
    Read current installed Filament v5 source/docs for panel plugins, asset registration, render hooks, action configuration, navigation item attributes, and Livewire navigation lifecycle. Read current Driver.js docs/source for initialization, destroy, popover buttons, progress text, keyboard behavior, and styling hooks. Record the chosen APIs and rejected alternatives in `RESEARCH.md`.
  </action>
  <verify>
    <manual>`RESEARCH.md` names the exact Filament and Driver.js APIs to use and includes a decision on vanilla module vs Alpine shell.</manual>
  </verify>
  <done>
    No implementation starts from assumptions about asset/render hook APIs.
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 2: Register panel-scoped tutorial assets and runtime hook</name>
  <files>
    - composer.json
    - package.json
    - vite.config.js
    - src/FilamentTutorialsPlugin.php
    - src/FilamentTutorialsServiceProvider.php
    - resources/js/filament-tutorials.js
    - resources/css/filament-tutorials.css
    - resources/views/runtime.blade.php
    - tests/Feature/RuntimeAssetRegistrationTest.php
    - tests/Architecture/RuntimeAssetContractTest.php
  </files>
  <action>
    Add package assets and register them only for panels using the plugin. Render one runtime payload/hook per page. The payload must include tutorial metadata and translated labels only, not CPF, cookies, auth headers, tokens, `localStorage`, `sessionStorage`, or portal-specific data. Add architecture checks for no `wire:poll`, no `setInterval`, no `.fi-*` tutorial selectors, and no forbidden privacy strings.
  </action>
  <verify>
    <automated>./packages/workbench/bin/sail npm run build</automated>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Feature/RuntimeAssetRegistrationTest.php tests/Architecture/RuntimeAssetContractTest.php</automated>
  </verify>
  <done>
    A Workbench page contains exactly one tutorial runtime and loads package assets only when the plugin is registered.
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 3: Implement stable target resolver and data-tour injection MVP</name>
  <files>
    - src/Support/*
    - src/TutorialTarget.php
    - src/TutorialStep.php
    - tests/Unit/*
    - tests/Feature/StableTargetInjectionTest.php
    - workbench/app/Filament/*
  </files>
  <action>
    Implement an MVP resolver for supported targets: current page, navigation item, page action/table action when a stable owner/action pair is available, and custom target. Use central configuration hooks where Filament exposes stable public extension points. Preserve existing host attributes when injecting `data-tour`. If a target cannot be materialized through a stable public API, keep it unsupported in code and document it as deferred instead of using private `.fi-*` selectors.
  </action>
  <verify>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Unit tests/Feature/StableTargetInjectionTest.php</automated>
  </verify>
  <done>
    Workbench rendered DOM contains deterministic `data-tour` attributes for supported targets without manual `extraAttributes()` on those examples.
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 4: Build Driver.js runtime lifecycle and launcher behavior</name>
  <files>
    - resources/js/filament-tutorials.js
    - resources/views/runtime.blade.php
    - src/Support/*
    - tests/Feature/TutorialRuntimePayloadTest.php
  </files>
  <action>
    Add a generic page tutorial launcher that appears only when the current page has a tutorial. Starting a tutorial must destroy any existing Driver instance, resolve steps to `data-tour` selectors, skip or report missing targets according to the contract from Phase 001, and return focus to the launcher after close/done. Use `MutationObserver` for dynamic targets. Do not start feature-specific modals directly from the generic launcher.
  </action>
  <verify>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Feature/TutorialRuntimePayloadTest.php</automated>
    <automated>./packages/workbench/bin/sail node --check resources/js/filament-tutorials.js</automated>
  </verify>
  <done>
    Tutorial launcher starts the current page tutorial and cleans up runtime state without duplicated drivers.
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 5: Style and localize Driver.js popover for Filament</name>
  <files>
    - resources/css/filament-tutorials.css
    - resources/js/filament-tutorials.js
    - resources/views/runtime.blade.php
    - tests/Architecture/RuntimeAssetContractTest.php
  </files>
  <action>
    Style Driver.js for compact Filament-like light/dark UI, translated progress text such as `{{current}} de {{total}}`, button labels in pt_BR when used by SiasgFacil, visible focus through `:focus-visible`, keyboard support, reduced motion support, and no text overflow in the popover.
  </action>
  <verify>
    <automated>./packages/workbench/bin/sail npm run build</automated>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Architecture/RuntimeAssetContractTest.php</automated>
  </verify>
  <done>
    Driver.js UI looks and behaves like a Filament-adjacent product surface in light and dark mode.
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 6: Add browser smoke coverage for Workbench tutorial flow</name>
  <files>
    - composer.json
    - tests/Browser/WorkbenchTutorialBrowserTest.php
    - tests/Pest.php
    - workbench/*
  </files>
  <action>
    Add the browser testing dependency only if needed and approved by package support constraints. Cover the Workbench panel flow: visit the panel, confirm the tutorial launcher appears only on pages with tutorial data, open tutorial, assert localized progress, advance steps, close/done, assert no JavaScript errors, no console logs, no serious accessibility issues, and capture desktop and mobile screenshots.
  </action>
  <verify>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Browser/WorkbenchTutorialBrowserTest.php</automated>
  </verify>
  <done>
    Runtime behavior is proven in a real browser before the package is wired into SiasgFacil.
  </done>
</task>
</tasks>

## Final Verification

```bash
./packages/workbench/bin/workbench up -d
./packages/workbench/bin/sail composer validate --strict
./packages/workbench/bin/sail npm run build
./packages/workbench/bin/sail php vendor/bin/pest --compact
./packages/workbench/bin/sail pint --dirty
./packages/workbench/bin/sail phpstan --memory-limit=1G
```
