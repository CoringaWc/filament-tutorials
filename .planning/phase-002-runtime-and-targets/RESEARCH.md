# Phase 002 Research

## Filament APIs

- Panel-scoped assets: `Filament\Panel::assets()` registers assets only for panels that declare the plugin.
- Asset classes: `Filament\Support\Assets\Css::make()` and `Filament\Support\Assets\Js::make()->module()`.
- Render hooks: `Filament\Support\Facades\FilamentView::registerRenderHook()` with `Filament\View\PanelsRenderHook`.
- Current page/resource matching: Filament passes Livewire render hook scopes from `getRenderHookScopes()`. Page scopes include the page class, and resource page scopes include page and resource classes.
- Stable shell targets: public render hooks are safe for page, topbar, global search, user menu, and sidebar marker targets. Internal Filament CSS classes are not safe tutorial contracts.
- Launcher placement: default to `TOPBAR_START` so the generic launcher appears even when a panel disables global search. Consumers may configure `launcher_render_hook` to move it near global search.

## Driver.js APIs

- Runtime uses the official `driver()` factory from `driver.js`.
- Lifecycle uses `drive()` and `destroy()`.
- Button copy is configured with `nextBtnText`, `prevBtnText`, `doneBtnText`, and `progressText`.
- Dynamic target readiness uses `MutationObserver`, not timers or Livewire polling.

## Decisions

- Use a vanilla ES module. Alpine is unnecessary for the package-level lifecycle.
- Style Driver.js in package CSS and import `driver.js/dist/driver.css` from that CSS file.
- Use automatic render-hook markers for surfaces the package owns safely; use explicit `data-tour` targets for Workbench internals that belong to the consuming page/resource/component.
