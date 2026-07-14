<?php

declare(strict_types=1);

it('keeps released library artifacts free from development lockfiles', function (): void {
    $attributes = file_get_contents(__DIR__.'/../../.gitattributes');
    $gitignore = file_get_contents(__DIR__.'/../../.gitignore');

    expect($attributes)
        ->toContain('/composer.lock export-ignore')
        ->toContain('/package-lock.json export-ignore')
        ->toContain('/node_modules export-ignore')
        ->toContain('/vendor export-ignore')
        ->and($gitignore)->not->toContain('/package-lock.json')
        ->and(file_exists(__DIR__.'/../../package-lock.json'))->toBeTrue();
});

it('pins every third party GitHub Action to an immutable commit', function (): void {
    $workflowFiles = glob(__DIR__.'/../../.github/workflows/*.yml');

    if ($workflowFiles === false) {
        throw new RuntimeException('Unable to list GitHub Actions workflows.');
    }

    expect(count($workflowFiles))->toBeGreaterThan(0);

    foreach ($workflowFiles as $workflowFile) {
        $workflow = file_get_contents($workflowFile);

        preg_match_all('/^\s*uses:\s*([^\s#]+)/m', (string) $workflow, $matches);

        foreach ($matches[1] as $actionReference) {
            if (str_starts_with($actionReference, './')) {
                continue;
            }

            expect($actionReference)->toMatch('/^[^@]+@[a-f0-9]{40}$/');
        }
    }
});

it('automates dependency updates with a supply chain cooldown', function (): void {
    $dependabot = file_get_contents(__DIR__.'/../../.github/dependabot.yml');

    expect($dependabot)
        ->toContain('version: 2')
        ->toContain('package-ecosystem: composer')
        ->toContain('package-ecosystem: npm')
        ->toContain('package-ecosystem: github-actions');

    expect(substr_count((string) $dependabot, 'default-days: 7'))->toBe(3);
});

it('builds Filament workbench assets before browser tests', function (): void {
    $testWorkflow = file_get_contents(__DIR__.'/../../.github/workflows/run-tests.yml');

    expect($testWorkflow)
        ->toContain('- name: Build workbench assets')
        ->toContain('run: composer build');
});

it('publishes a private vulnerability disclosure path', function (): void {
    $securityPolicy = file_get_contents(__DIR__.'/../../SECURITY.md');

    expect($securityPolicy)
        ->toContain('# Security Policy')
        ->toContain('/security/advisories/new')
        ->toContain('Please do not disclose suspected vulnerabilities in public issues');
});
