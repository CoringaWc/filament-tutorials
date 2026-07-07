<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('runs the dashboard tutorial across static and dynamic targets', function (): void {
    $page = visit('/admin')
        ->assertVisible('[data-filament-tutorials-launcher]')
        ->assertScript('document.querySelector("[data-filament-tutorials-launcher]")?.dataset.filamentTutorialsBooted', 'true')
        ->assertScript('JSON.parse(document.querySelector("[data-filament-tutorials-runtime]").dataset.payload).tutorials.length > 0', true)
        ->screenshot(filename: 'tutorial-dashboard-initial')
        ->click('[data-filament-tutorials-launcher]')
        ->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertScript('document.body.classList.contains("driver-active")', true)
        ->assertSee('Painel do laboratório')
        ->assertSee('1 de 10')
        ->click('.driver-popover-next-btn')
        ->assertSee('Busca global')
        ->click('.driver-popover-next-btn')
        ->assertSee('Ação da página')
        ->click('.driver-popover-next-btn')
        ->assertSee('Widget')
        ->click('.driver-popover-next-btn')
        ->assertSee('Conteúdo da página')
        ->click('.driver-popover-next-btn')
        ->assertSee('Schema e infolist')
        ->click('.driver-popover-next-btn')
        ->assertSee('Tabela')
        ->click('.driver-popover-next-btn')
        ->assertSee('Menu aberto')
        ->assertVisible('[data-lab-dropdown-menu]')
        ->click('.driver-popover-next-btn')
        ->assertSee('Seção aberta')
        ->assertVisible('[data-lab-collapsible-panel]')
        ->wait(1)
        ->click('.driver-popover-next-btn')
        ->wait(2)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee('Modal aberto')
        ->assertVisible('[data-tour="workbench.dashboard.modal"]')
        ->screenshot(filename: 'tutorial-dashboard-modal')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();

    $page->click('.driver-popover-next-btn')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

it('keeps the tutorial launcher and popover usable on mobile and dark mode', function (): void {
    visit('/admin')
        ->resize(390, 844)
        ->assertVisible('[data-filament-tutorials-launcher]')
        ->assertScript('document.querySelector("[data-filament-tutorials-launcher]")?.dataset.filamentTutorialsBooted', 'true')
        ->click('[data-filament-tutorials-launcher]')
        ->wait(1)
        ->assertSee('Painel do laboratório')
        ->screenshot(filename: 'tutorial-dashboard-mobile');

    visit('/admin')
        ->resize(1440, 900)
        ->assertScript('document.documentElement.classList.add("dark") || true')
        ->assertScript('document.querySelector("[data-filament-tutorials-launcher]")?.dataset.filamentTutorialsBooted', 'true')
        ->click('[data-filament-tutorials-launcher]')
        ->wait(1)
        ->assertSee('Painel do laboratório')
        ->screenshot(filename: 'tutorial-dashboard-dark')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
