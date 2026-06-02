# Roadmap de Reestruturacao

## Fase 1 - Correcao e estabilizacao local

- Definir `index.php` como entrada principal dinamica.
- Manter `index.html` como prototipo legado ate migrar os fluxos.
- Corrigir caminhos de CSS, JS, footer e imagens inexistentes.
- Corrigir erros JavaScript que impedem carregamento.
- Padronizar includes PHP para nomes reais dos arquivos.
- Criar instrucoes de execucao local no `README.md`.
- Levantar servidor PHP local quando PHP estiver instalado.
- Criar um schema MySQL minimo compativel com a aplicacao PHP.
- Proteger arquivos sensiveis e remover credenciais do codigo.

## Fase 2 - Redesign premium

- Criar identidade visual moderna para corretora/concessionaria inteligente de planos de saude.
- Redesenhar landing page com foco em confianca, seguranca, tecnologia e atendimento humanizado.
- Melhorar navbar, menu mobile e hierarquia de chamadas.
- Redesenhar cards de operadoras e planos.
- Melhorar formularios, estados de validacao e modais.
- Criar UX mobile profissional para cotacao e envio de proposta.
- Criar layouts administrativos para vendedor, empresa, supervisor, gerente e administrador.

## Fase 3 - Modularizacao

- Organizar estrutura em:
  - `/assets`
  - `/assets/css`
  - `/assets/js`
  - `/assets/img`
  - `/pages`
  - `/components`
  - `/data`
  - `/includes`
  - `/api`
- Remover duplicacoes entre `index.html`, `index.php`, `app.js` e `assets/js/app.js`.
- Padronizar nomes de classes, IDs, funcoes e endpoints.
- Separar scripts por dominio:
  - autenticacao
  - cotacao
  - propostas
  - pagamentos
  - paineis
- Criar componentes reutilizaveis para cards, tabelas, status, modais e formularios.

## Fase 4 - Funcionalidade real

- Substituir `localStorage` por backend PHP/MySQL.
- Criar fluxo real de cadastro de empresa.
- Criar fluxo real de cadastro de vendedor/corretor.
- Criar fluxo real de login com perfis e permissoes.
- Criar fluxo real de propostas:
  - cotacao
  - ficha cadastral
  - upload de documentos
  - analise do supervisor
  - aprovacao final do gerente
  - acompanhamento pelo vendedor e empresa
- Criar trilha de auditoria operacional.
- Preparar LGPD: consentimento, logs, minimizacao e mascaramento de dados sensiveis.

## Fase 5 - Integracao com APIs de operadoras

- Criar camada `/providers` ou `/integrations`.
- Implementar contrato comum:
  - `quote(request)`
  - `submitProposal(payload)`
  - `getProposalStatus(protocol)`
  - `isAvailable()`
- Criar adaptadores:
  - `AmilProvider`
  - `UnimedProvider`
  - `CemeruProvider`
  - `KliniProvider`
  - `SamocProvider`
- Usar API quando disponivel.
- Usar tabela local/importada como fallback.
- Criar importador CSV/Excel para tabelas.
- Versionar tabelas por data de vigencia.
- Registrar origem da cotacao: `API`, `TABELA_IMPORTADA` ou `MANUAL`.

## Fase 6 - Preparacao para hospedagem Locaweb

- Confirmar plano atual da Locaweb:
  - PHP suportado
  - versao do PHP
  - MySQL disponivel
  - permissoes de upload
  - cron jobs
  - suporte a Node.js
- Estrategia recomendada inicial: frontend e backend PHP na Locaweb com MySQL.
- Evitar Node.js na primeira estabilizacao se a hospedagem nao suportar processo persistente.
- Avaliar backend externo no futuro para integracoes mais complexas.
- Preparar deploy com arquivos sensiveis fora do `public_html`.

## Ordem de prioridade

1. Estabilizar renderizacao e assets.
2. Sanear credenciais e configuracao.
3. Corrigir banco e endpoints essenciais.
4. Redesenhar experiencia comercial.
5. Implementar fluxo real de propostas.
6. Integrar pagamentos e operadoras.

