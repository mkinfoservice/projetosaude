# Projeto Concessionaria Inteligente

Sistema em estabilizacao para cotacao e gestao comercial de propostas de planos de saude.

## Estado atual

Este projeto veio de uma hospedagem Locaweb e esta em fase de saneamento tecnico. Ele contem:

- `index.html`: prototipo estatico com dados em `localStorage`.
- `index.php`: entrada PHP recomendada para evolucao.
- `api/`: endpoints e classes PHP.
- `admin/`: paineis administrativos.
- `backend/`: esboco Node.js ainda nao consolidado.
- `assets/`: CSS, JS e uploads.

## Requisitos para rodar localmente

Para a versao PHP:

- PHP 8.x
- MySQL ou MariaDB
- Servidor local PHP ou Apache/Nginx

Para verificacoes JavaScript:

- Node.js

## Como rodar a versao estatica

Abra `index.html` diretamente no navegador. Essa versao e apenas um prototipo e usa `localStorage`; nao grava dados em backend real.

## Como rodar a versao PHP

Com PHP instalado, na raiz do projeto:

```bash
php -S 127.0.0.1:8000
```

Depois acesse:

```text
http://127.0.0.1:8000/index.php
```

Observacao: nesta maquina de analise o comando `php` nao esta disponivel, entao a execucao PHP local ainda precisa ser validada em ambiente com PHP instalado.

## Configuracao

Os arquivos de configuracao atuais ainda contem credenciais hardcoded e devem ser saneados antes de producao:

- `api/config.php`
- `config.php`
- `api/api.php`
- `api/index.php`
- `api/api/config.php`
- `backend/backend.env.txt`

Recomendacao: mover segredos para variaveis de ambiente ou arquivo fora do `public_html`.

## Documentacao

- `ANALISE_TECNICA.md`: diagnostico tecnico completo.
- `ROADMAP.md`: plano de reestruturacao por fases.

## Proxima etapa recomendada

Estabilizar a versao PHP/MySQL como base principal, manter o `index.html` como referencia visual temporaria e migrar gradualmente os fluxos simulados para backend real.

