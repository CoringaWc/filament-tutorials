# Phase 005: Browser Coverage And Documentation

## Goal

Close the package by proving the browser tutorial experience and documenting the public usage contract for implementation teams.

## Scope

- Document installation, panel registration, discovery, inline tutorials, targets, lifecycle helpers, progress persistence, and workbench gates.
- Keep browser coverage focused on the Workbench surfaces that prove the runtime: launcher, Driver.js popover, dropdown, profile menu, collapsible content, modal, mobile viewport, and dark mode.
- Run the final release gates after documentation and asset changes.

## Verification

- `./packages/workbench/bin/sail composer validate --strict`
- `./packages/workbench/bin/sail npm run check`
- `./packages/workbench/bin/sail npm run build`
- `./packages/workbench/bin/sail composer run build`
- `./packages/workbench/bin/sail php vendor/bin/pest --compact`
- `./packages/workbench/bin/sail php vendor/bin/pest --compact tests/Browser/WorkbenchTutorialBrowserTest.php`
- `./packages/workbench/bin/sail php vendor/bin/phpstan analyse --memory-limit=1G`
- `./packages/workbench/bin/sail pint --dirty --format agent`
