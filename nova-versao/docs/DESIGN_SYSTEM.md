# Design System - Bem Planos de Saude

## Direcao Visual

Plataforma SaaS premium para healthtech comercial: clean, corporativa, moderna e confiavel.

Referencias visuais aplicadas: landing clara com alto contraste, shell administrativo navy, cards brancos com borda suave, botoes azuis de acao principal e teal como cor de saude/sucesso.

## Tokens Principais

- Navy: `--ds-navy-*`
- Azul primario: `--ds-blue-*`
- Teal saude: `--ds-teal-*`
- Neutros: `--ds-slate-*`
- Fundo de app: `--ds-app-bg`
- Bordas: `--ds-card-border`
- Sombras: `--ds-shadow-card`, `--ds-shadow-float`

## Componentes

- `btn`, `btn-primary`, `btn-secondary`, `btn-outline`, `btn-ghost`
- `dashboard-wrapper`, `dashboard-sidebar`, `dashboard-topbar`
- `dash-card`, `stat-card`, `table`, `badge`
- `form-input`, `form-row`, `form-section-icon`, `upload-area`
- `progress-steps`, `progress-step`, `progress-connector`
- `plan-card`, `plan-tags`, `plan-insights`, `plan-life-panel`
- `modal`, `plan-benefits-modal`, `benefit-list`

## Regras de Uso

- Azul e usado para acoes principais e etapas ativas.
- Teal e usado para saude, sucesso, checkmarks e estados concluidos.
- Cards devem usar fundo branco, borda `--ds-card-border` e sombra discreta.
- Dashboards devem usar sidebar navy escura e area de trabalho clara.
- Formulario deve priorizar campos com altura minima de 46px, labels discretas e foco azul.
- Evitar gradientes decorativos excessivos dentro de paineis operacionais.

## Telas Cobertas

- Landing page publica
- Login
- Dashboard administrativo/vendedor
- Nova proposta, incluindo stepper, formularios, filtros, cards de planos e modal de beneficios

## Responsividade

- Desktop: sidebar fixa, conteudo centralizado com largura maxima operacional.
- Tablet/mobile: sidebar e ocultada, navbar publica volta a aparecer, grids viram uma coluna.
- Cards de planos usam `auto-fit` com minimo de 300px.
