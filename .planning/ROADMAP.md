# Roadmap

## Phase 001: Core Plugin Contract

Status: completed

Harden the package skeleton, public PHP API, workbench setup, discovery model, inline Page/Resource contract, and release gates. Completed 2026-07-07; see `phase-001-filament-tutorials-core/SUMMARY.md`.

## Phase 002: Runtime And Stable Targets

Status: planned

Register Driver.js assets through Filament panel/plugin conventions, expose one runtime bridge, render the page tutorial launcher, and prove stable `data-tour` target materialization for supported Filament primitives.

## Phase 003: Expanded Target Injection

Status: pending

Expand target support beyond the Phase 002 MVP: modals, profile menu, sidebar lifecycle, table/page actions with owners, and dynamic Livewire surfaces without requiring every resource/page to define `extraAttributes()` manually.

## Phase 004: Progress Persistence

Status: pending

Persist per-user completion, dismissal, restart, and auto-start state with package migrations and action-style workflow boundaries.

## Phase 005: Browser Coverage And Documentation

Status: pending

Prove workbench UI behavior with browser tests/screenshots and document package usage patterns.
