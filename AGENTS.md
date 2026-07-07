# AGENTS.md

## Projeto

`coringawc/filament-tutorials` e um plugin Filament v5 para criar tutoriais interativos com Driver.js, targets estaveis e integracao por painel.

## Regras

- Comandos PHP, Composer, Pest, Pint, PHPStan e Artisan devem rodar pelo workbench: `./packages/workbench/bin/sail ...` para comandos dentro do container, ou `./packages/workbench/bin/workbench ...` para inicializar o ambiente.
- Siga o padrao dos plugins `coringawc/filament-action-approvals` e `coringawc/filament-acl`.
- O plugin deve ser registrado via `FilamentTutorialsPlugin::make()` no `PanelProvider`.
- Tutoriais complexos vivem em classes descobertas por painel, por exemplo `app/Filament/App/Tutorials`.
- Tutoriais simples podem ser declarados diretamente em `Page` ou `Resource` via contrato.
- Nao use seletores internos `.fi-*` como contrato de tutorial. O plugin deve gerar `data-tour` estavel.
- Use Driver.js pela API oficial. Alpine pode orquestrar lifecycle local apenas se isso ficar comprovado no spike/plano.

## Verificacao

- `./packages/workbench/bin/sail php vendor/bin/pest`
- `./packages/workbench/bin/sail pint --dirty`
- `./packages/workbench/bin/sail phpstan --memory-limit=1G`
