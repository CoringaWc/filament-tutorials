<button
    type="button"
    x-data="filamentTutorialsLauncher"
    x-bind:hidden="! available"
    x-on:click="start"
    data-filament-tutorials-launcher
    data-tour="tutorial.launcher"
    class="me-2 inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:bg-gray-50 hover:text-gray-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500 dark:border-white/10 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200"
    title="{{ __('Abrir tutorial da página') }}"
    aria-label="{{ __('Abrir tutorial da página') }}"
    hidden
>
    <x-filament::icon
        icon="heroicon-m-question-mark-circle"
        class="h-5 w-5"
    />
</button>
