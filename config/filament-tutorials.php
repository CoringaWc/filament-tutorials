<?php

declare(strict_types=1);
use Filament\View\PanelsRenderHook;

return [
    'discovery' => [
        'enabled' => true,
        'path_suffix' => 'Tutorials',
        'namespace_suffix' => 'Tutorials',
    ],

    'launcher_render_hook' => PanelsRenderHook::TOPBAR_START,

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
