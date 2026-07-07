<x-filament::icon-button
    :icon="$icon"
    :label="$label"
    :tooltip="$tooltip"
    color="gray"
    type="button"
    x-data="filamentTutorialsLauncher"
    x-bind:hidden="! available"
    x-on:click="start"
    data-filament-tutorials-launcher
    data-tour="tutorial.launcher"
    class="me-2"
    hidden
/>
