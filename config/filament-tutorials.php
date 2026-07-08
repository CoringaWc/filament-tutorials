<?php

declare(strict_types=1);
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;

return [
    'discovery' => [
        'enabled' => true,
        'path_suffix' => 'Tutorials',
        'namespace_suffix' => 'Tutorials',
    ],

    'launcher' => [
        'enabled' => true,
        'render_hook' => PanelsRenderHook::USER_MENU_BEFORE,
        'icon' => Heroicon::QuestionMarkCircle,
        'label' => 'Abrir tutorial da página',
        'tooltip' => 'Abrir tutorial da página',
    ],

    'launcher_render_hook' => null,

    'dismissal_reminder' => [
        'enabled' => true,
        'step_key' => 'reopen-page-tutorial',
        'skip_label' => 'Ignorar',
        'title' => 'Você pode voltar quando quiser',
        'description' => 'Para rever este guia, clique no ícone de interrogação no topo da página.',
    ],

    'render_hook_targets' => [
        'global-search.before' => PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
        'sidebar.nav.start' => PanelsRenderHook::SIDEBAR_NAV_START,
        'topbar.start' => PanelsRenderHook::TOPBAR_START,
        'user-menu.before' => PanelsRenderHook::USER_MENU_BEFORE,
        'page.header.actions.before' => PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE,
        'page.header-widgets.before' => PanelsRenderHook::PAGE_HEADER_WIDGETS_BEFORE,
        'resource.list.table.before' => PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE,
        'resource.related.table.before' => PanelsRenderHook::RESOURCE_PAGES_MANAGE_RELATED_RECORDS_TABLE_BEFORE,
    ],

    'progress' => [
        'enabled' => true,
        'table' => 'filament_tutorial_progress',
        'route_path' => 'filament-tutorials/progress',
        'middleware' => ['web'],
    ],
];
