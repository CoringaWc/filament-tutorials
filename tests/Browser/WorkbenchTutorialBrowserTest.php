<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\Support\TutorialTargetKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Filament\Pages\WorkbenchDashboard;

uses(RefreshDatabase::class);

it('runs the dashboard tutorial across static and dynamic targets', function (): void {
    $page = visit('/admin')
        ->assertVisible('[data-filament-tutorials-launcher]')
        ->assertVisible('.fi-user-menu')
        ->assertScript(
            <<<'JS'
                (() => {
                    const launcher = document.querySelector('[data-filament-tutorials-launcher]')?.getBoundingClientRect()
                    const userMenu = document.querySelector('.fi-user-menu')?.getBoundingClientRect()

                    return Boolean(
                        launcher &&
                        userMenu &&
                        launcher.right <= userMenu.left + 8 &&
                        Math.abs(launcher.top - userMenu.top) <= 12
                    )
                })()
            JS,
            true,
        )
        ->assertScript('document.querySelector("[data-filament-tutorials-launcher]")?.dataset.filamentTutorialsBooted', 'true')
        ->assertScript('Boolean(document.querySelector("[data-filament-tutorials-launcher]")?._x_dataStack?.length)', true)
        ->assertScript('JSON.parse(document.querySelector("[data-filament-tutorials-runtime]").dataset.payload).tutorials.length > 0', true)
        ->screenshot(filename: 'tutorial-dashboard-initial')
        ->click('[data-filament-tutorials-launcher]')
        ->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertScript('document.body.classList.contains("driver-active")', true)
        ->assertSee('Painel do laboratório')
        ->assertSee('1 de 13')
        ->assertSee('Ignorar')
        ->assertScript('window.getComputedStyle(document.querySelector(".driver-popover-prev-btn")).display', 'none')
        ->assertScript('window.getComputedStyle(document.querySelector("[data-filament-tutorials-skip]")).display !== "none"', true)
        ->assertScript('window.getComputedStyle(document.querySelector(".driver-popover-next-btn")).color', 'rgb(255, 255, 255)')
        ->assertScript(
            <<<'JS'
                (() => {
                    const skip = window.getComputedStyle(document.querySelector('[data-filament-tutorials-skip]'))
                    const previous = window.getComputedStyle(document.querySelector('.driver-popover-prev-btn'))

                    return skip.color === previous.color
                        && skip.backgroundColor === previous.backgroundColor
                        && skip.borderColor === previous.borderColor
                })()
            JS,
            true,
        )
        ->assertScript(
            <<<'JS'
                (() => {
                    const activeTarget = document.querySelector('.driver-active-element')
                    const rect = activeTarget?.getBoundingClientRect()

                    return activeTarget?.dataset.tour === 'workbench.dashboard.intro'
                        && rect.width > 250
                        && rect.height > 100
                })()
            JS,
            true,
        )
        ->click('.driver-popover-next-btn')
        ->assertSee('Menu lateral')
        ->assertSee('2 de 13')
        ->assertNotPresent('[data-filament-tutorials-skip]')
        ->assertScript('window.getComputedStyle(document.querySelector(".driver-popover-prev-btn")).display !== "none"', true)
        ->assertScript(
            sprintf(
                <<<'JS'
                    document.querySelector('.driver-active-element')?.dataset.tour === '%s'
                JS,
                TutorialTargetKeys::navigation(WorkbenchDashboard::class),
            ),
            true,
        )
        ->click('.driver-popover-next-btn')
        ->assertSee('Botão de ajuda')
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
        ->assertSee('Menu de perfil')
        ->assertVisible('[data-lab-profile-menu]')
        ->click('.driver-popover-next-btn')
        ->assertSee('Seção aberta')
        ->assertVisible('[data-lab-collapsible-panel]')
        ->wait(1)
        ->click('.driver-popover-next-btn')
        ->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertSee('Modal aberto')
        ->assertVisible('[data-tour="workbench.dashboard.modal"]')
        ->screenshot(filename: 'tutorial-dashboard-modal')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();

    $page->click('.driver-popover-next-btn')
        ->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertDontSee('Você pode voltar quando quiser')
        ->assertScript('document.body.classList.contains("driver-active")', false)
        ->assertNotPresent('[data-lab-dropdown-menu]:not([hidden])')
        ->assertNotPresent('[data-lab-profile-menu]:not([hidden])')
        ->assertNotPresent('[data-lab-collapsible-panel]:not([hidden])')
        ->assertNotPresent('[data-lab-modal]:not([hidden])');
});

it('keeps the tutorial launcher and popover usable on mobile and dark mode', function (): void {
    visit('/admin')
        ->resize(390, 844)
        ->assertVisible('[data-filament-tutorials-launcher]')
        ->assertScript('document.querySelector("[data-filament-tutorials-launcher]")?.dataset.filamentTutorialsBooted', 'true')
        ->assertScript('Boolean(document.querySelector("[data-filament-tutorials-launcher]")?._x_dataStack?.length)', true)
        ->assertScript('window.Alpine.store("sidebar").isOpen', false)
        ->click('[data-filament-tutorials-launcher]')
        ->wait(1)
        ->assertSee('Painel do laboratório')
        ->assertSee('Ignorar')
        ->click('.driver-popover-next-btn')
        ->wait(1)
        ->assertSee('Menu lateral')
        ->assertScript('window.Alpine.store("sidebar").isOpen', true)
        ->assertScript(
            sprintf(
                <<<'JS'
                    document.querySelector('.driver-active-element')?.dataset.tour === '%s'
                JS,
                TutorialTargetKeys::navigation(WorkbenchDashboard::class),
            ),
            true,
        )
        ->screenshot(filename: 'tutorial-dashboard-mobile');

    visit('/admin')
        ->resize(1440, 900)
        ->assertScript('document.documentElement.classList.add("dark") || true')
        ->assertScript('document.querySelector("[data-filament-tutorials-launcher]")?.dataset.filamentTutorialsBooted', 'true')
        ->assertScript('Boolean(document.querySelector("[data-filament-tutorials-launcher]")?._x_dataStack?.length)', true)
        ->click('[data-filament-tutorials-launcher]')
        ->wait(1)
        ->assertSee('Painel do laboratório')
        ->assertScript('window.getComputedStyle(document.querySelector(".driver-popover-next-btn")).color', 'rgb(255, 255, 255)')
        ->assertScript(
            <<<'JS'
                (() => {
                    const skip = window.getComputedStyle(document.querySelector('[data-filament-tutorials-skip]'))
                    const previous = window.getComputedStyle(document.querySelector('.driver-popover-prev-btn'))

                    return skip.color === previous.color
                        && skip.backgroundColor === previous.backgroundColor
                        && skip.borderColor === previous.borderColor
                })()
            JS,
            true,
        )
        ->screenshot(filename: 'tutorial-dashboard-dark')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

it('lets the user skip from the first popover to the final launcher reminder', function (): void {
    visit('/admin')
        ->assertVisible('[data-filament-tutorials-launcher]')
        ->click('[data-filament-tutorials-launcher]')
        ->wait(1)
        ->assertSee('Painel do laboratório')
        ->assertSee('Ignorar')
        ->click('[data-filament-tutorials-skip]')
        ->wait(1)
        ->assertSee('Você pode voltar quando quiser')
        ->assertSee('Para rever este guia, clique no ícone de interrogação no topo da página.')
        ->assertScript(
            <<<'JS'
                document.querySelector('.driver-active-element')?.dataset.tour === 'tutorial.launcher'
            JS,
            true,
        )
        ->screenshot(filename: 'tutorial-dashboard-skip-reminder')
        ->click('.driver-popover-next-btn')
        ->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertScript('document.body.classList.contains("driver-active")', false)
        ->assertVisible('[data-filament-tutorials-launcher]')
        ->assertScript('document.querySelector("[data-filament-tutorials-launcher]")?.hidden', false)
        ->navigate('/admin')
        ->wait(1)
        ->assertVisible('[data-filament-tutorials-launcher]')
        ->assertScript('document.querySelector("[data-filament-tutorials-launcher]")?.hidden', false)
        ->assertScript(
            <<<'JS'
                (() => {
                    const runtime = document.querySelector('[data-filament-tutorials-runtime]')
                    const payload = JSON.parse(runtime?.dataset.payload ?? '{}')
                    const tutorial = (payload.tutorials ?? []).find((item) => item.key === 'workbench-dashboard')

                    return tutorial?.autoStart === false
                        && tutorial?.progressStatus === 'dismissed'
                })()
            JS,
            true,
        );
});

it('shows the launcher reminder when the first popover is closed', function (): void {
    visit('/admin')
        ->assertVisible('[data-filament-tutorials-launcher]')
        ->click('[data-filament-tutorials-launcher]')
        ->wait(1)
        ->assertSee('Painel do laboratório')
        ->click('.driver-popover-close-btn')
        ->wait(1)
        ->assertSee('Você pode voltar quando quiser')
        ->assertSee('Para rever este guia, clique no ícone de interrogação no topo da página.')
        ->assertScript(
            <<<'JS'
                document.querySelector('.driver-active-element')?.dataset.tour === 'tutorial.launcher'
            JS,
            true,
        )
        ->click('.driver-popover-next-btn')
        ->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertScript('document.body.classList.contains("driver-active")', false);
});
