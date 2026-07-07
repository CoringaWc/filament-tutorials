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
  - FT-011-workbench-coverage-lab
  - FT-012-dynamic-preactions
must_haves:
  truths:
    - Driver.js must be used through its official API.
    - Package JS and CSS must be scoped to panels that register `FilamentTutorialsPlugin::make()`.
    - The runtime must render exactly once per panel page and destroy any active driver before starting another tutorial.
    - Alpine may be used only as a small lifecycle shell if official docs/source review proves it is cleaner than a vanilla module.
    - Tutorial selectors must use `data-tour` attributes generated from stable target keys.
    - Internal Filament classes such as `.fi-*` must not be tutorial contracts.
    - Dynamic targets must wait for the target to exist without `setInterval`, `wire:poll`, or arbitrary sleeps.
    - Driver.js popovers must visually follow Filament modal rhythm: compact radius, neutral surface, dark/light support, clear close button, primary next button, secondary previous button, and no developer-facing copy.
    - The Workbench must become a coverage lab, not a one-page demo.
    - Tutorials may define pre-actions that open sidebar, dropdowns, modals, collapsible sections, relation/nested surfaces, or other hidden UI before Driver.js resolves the target.
  artifacts:
    - Driver.js runtime bridge.
    - Panel-scoped Filament asset registration.
    - Render hook for launcher/runtime payload.
    - Target resolver and data-tour injection strategy.
    - Light/dark Driver.js styling aligned with Filament.
    - Workbench coverage lab for dashboard, pages, resources, relation/nested pages, navbar/topbar, sidebar, global search, dropdowns, modals, infolists, schemas, tables, widgets, actions, and collapsibles.
    - Browser smoke and visual screenshots proving representative tutorial flows.
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
- The package will ship a small vanilla runtime. Alpine is used only where Filament/Livewire already owns the local surface and a native attribute/action is clearer.
- The first implementation supports declarative pre-actions through step metadata. A pre-action must complete, or at least make the target observable, before Driver.js advances to that step.
- Workbench examples should be product-like but synthetic; they must not expose SiasgFacil, gov.br, Compras.gov, CPF, UASG, cookies, tokens, storage, or bridge internals.

## Workbench Coverage Matrix

The Workbench lab must include these targets and at least one tutorial step for each supported group:

| Area | Examples to prove |
| --- | --- |
| Dashboard | Page heading, page body, header actions, header widgets, footer widgets |
| Page | Generic page target, custom schema components, collapsible section opened before target |
| Resource | List table, table columns, table header action, row action, create/edit modal targets |
| Relation/nested | Manage-related page or relation-like nested page with table target and parent context |
| Navbar/topbar | Tutorial launcher, global search area, user menu/dropdown |
| Sidebar | Sidebar nav start/end, navigation item, sidebar open pre-action |
| Modal | Action button opens modal, modal window receives target, modal field/action receives target |
| Dropdown menu | Pre-action opens dropdown before the target is resolved |
| Infolist/schema/table | TextEntry/IconEntry, form input, table column/filter/action |
| Widgets | Stats/chart/table-like widget target |
| Dynamic visibility | Hidden/collapsed target becomes visible through pre-action before the step |

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
    Implement an MVP resolver for supported targets: current page, navigation item, page action/table action when a stable owner/action pair is available, custom target, named render-hook target, and manual component target. Use central configuration hooks where Filament exposes stable public extension points. Preserve existing host attributes when injecting `data-tour`. If a target cannot be materialized through a stable public API, keep it supported only through explicit package-owned/manual `data-tour` attributes in the Workbench lab instead of using private `.fi-*` selectors.
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
    Add a generic page tutorial launcher that appears only when the current page has a tutorial. Starting a tutorial must destroy any existing Driver instance, execute pre-actions for the current step, resolve steps to `data-tour` selectors, skip or report missing targets according to the contract from Phase 001, and return focus to the launcher after close/done. Use `MutationObserver` for dynamic targets. The generic launcher may execute step-owned pre-actions such as opening a modal/collapsible/sidebar/dropdown, but must not hard-code any consuming app feature.
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
    Style Driver.js for compact Filament modal-like light/dark UI, translated progress text such as `{{current}} de {{total}}`, button labels in pt_BR when used by SiasgFacil, visible focus through `:focus-visible`, keyboard support, reduced motion support, and no text overflow in the popover. The close button must look like Filament modal close chrome, previous must look secondary/gray, and next/done must look primary.
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
  <name>Task 6: Build Workbench tutorial coverage lab</name>
  <files>
    - workbench/*
    - tests/Feature/WorkbenchTutorialCoverageTest.php
  </files>
  <action>
    Add a Workbench coverage lab that exercises dashboard, pages, resources, nested/relation-like pages, sidebar, topbar/global search, user dropdown, modals, collapsibles, infolists, schemas/forms, tables, widgets, and actions. Keep examples synthetic, institutional, and minimal. Each area must either have an automatically injected target or an explicit package-owned/manual `data-tour` target with a test proving it exists.
  </action>
  <verify>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Feature/WorkbenchTutorialCoverageTest.php</automated>
  </verify>
  <done>
    Workbench has enough surface area to exercise every tutorial target class requested before the package is wired into SiasgFacil.
  </done>
</task>

<task type="auto" tdd="true">
  <name>Task 7: Add browser and visual coverage for Workbench tutorial flows</name>
  <files>
    - composer.json
    - package.json
    - tests/Browser/WorkbenchTutorialBrowserTest.php
    - tests/Pest.php
    - .gitignore
  </files>
  <action>
    Add browser testing dependencies if not already present. Cover the Workbench panel flow: visit the dashboard, confirm the tutorial launcher appears only on pages with tutorial data, open tutorial, assert localized progress, advance steps across static and dynamic targets, verify modal/collapsible/dropdown pre-actions, close/done, assert no JavaScript errors, no console logs, no serious accessibility issues, and capture desktop, mobile, light, and dark screenshots for visual review.
  </action>
  <verify>
    <automated>./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Browser/WorkbenchTutorialBrowserTest.php</automated>
  </verify>
  <done>
    Runtime behavior is proven in a real browser with visual screenshots before the package is wired into SiasgFacil.
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
./packages/workbench/bin/sail php vendor/bin/phpstan analyse --memory-limit=1G
```

## Execution Notes

- Workbench Docker PHP image requires `sockets` for Pest Browser support.
- Visual screenshots were generated by `tests/Browser/WorkbenchTutorialBrowserTest.php` for desktop, mobile, modal, and dark mode.
- Final executed gates:
  - `./packages/workbench/bin/sail composer validate --strict`
  - `./packages/workbench/bin/sail npm run check`
  - `./packages/workbench/bin/sail npm run build`
  - `./packages/workbench/bin/sail composer run build`
  - `./packages/workbench/bin/sail php vendor/bin/phpstan analyse --memory-limit=1G`
  - `./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Unit tests/Feature tests/Architecture`
  - `./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Browser/WorkbenchTutorialBrowserTest.php`
