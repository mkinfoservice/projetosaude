# CHANGELOG - Nova Versao

## 2026-05-14 - Home com consulta de beneficios e redes

### Arquivos alterados
- `app/views/pages/home.php`
- `app/views/pages/login.php`
- `app/views/layouts/main.php`
- `public/assets/css/app.css`
- `public/assets/js/app.js`
- `docs/CHANGELOG.md`

### Melhorias feitas
- Substituida a secao publica do assistente inteligente por uma consulta de beneficios e redes credenciadas por plano.
- Adicionados dados ficticios de planos, beneficios, notas e unidades credenciadas para validacao visual.
- Ajustados CTAs da home e do modal de cotacao para apontarem para beneficios e redes, sem foco automatico em chat.
- Criado visual responsivo para o novo bloco com cards premium, filtros simples e aviso de dados demonstrativos.
- Refinada a secao para um padrao visual premium inspirado em Figma/Vercel, com painel editorial, card de consulta ativo e cache-busting dos assets.
- Redesenhada a tela de login com card glass premium, badge de acesso seguro, painel lateral mais sofisticado e mockup comercial.

### Pontos que ainda faltam
- Integrar a consulta com tabelas reais de rede credenciada quando o modulo de operadoras evoluir.
- Permitir busca real por CEP/bairro com dados oficiais das operadoras.

### Como testar
- Abrir `/` e confirmar que a home inicia no hero/topo.
- Rolar ate a area de beneficios e trocar o plano no seletor.
- Digitar um bairro/CEP e confirmar que a listagem atualiza o contexto exibido.
- Clicar em "Simular valores deste perfil" e validar que o modal de cotacao abre sem erro visual.

## 2026-05-14 - Home inicia no topo

### Arquivos alterados
- `public/assets/js/app.js`
- `docs/CHANGELOG.md`

### Melhorias feitas
- Removido foco automatico no input do assistente inteligente ao carregar a home.
- Configurada restauracao manual de scroll para evitar que o navegador reabra a home no meio da pagina.
- Mantido o assistente funcionando quando o usuario clicar explicitamente para usar.

### Pontos que ainda faltam
- Redesenhar ou substituir a secao do assistente se ela continuar abaixo do padrao visual desejado.

### Como testar
- Abrir `/` em uma nova aba e confirmar que a pagina inicia no hero/topo.
- Recarregar a home apos ter rolado a pagina e confirmar que ela volta ao topo.
- Clicar nos CTAs de cotacao e validar que o assistente/modal continuam funcionando.

## 2026-05-14 - Design system premium healthtech

### Arquivos criados
- `docs/DESIGN_SYSTEM.md`

### Arquivos alterados
- `app/views/layouts/main.php`
- `app/views/components/navbar.php`
- `public/assets/css/app.css`
- `docs/CHANGELOG.md`

### Melhorias feitas
- Aplicada camada visual inspirada nas referencias Figma para SaaS healthtech premium.
- Criados tokens complementares de azul, navy, teal, bordas, sombras e fundo de app.
- Ajustados landing, login, sidebar, topbar, cards, formularios, stepper, filtros e cards de planos.
- Removida dependencia de CDN para fonte/Alpine na camada principal.
- Navbar mobile passou a usar o JavaScript local `app.js`.
- Documentado o design system reutilizavel.

### Pontos que ainda faltam
- Revisar todas as telas secundarias com screenshots reais apos login.
- Criar pagina de sucesso/revisao completa se o fluxo de proposta evoluir para quatro etapas.
- Trocar logotipo temporario por asset oficial quando existir.

### Como testar
- Abrir `/` e conferir landing clara premium.
- Abrir `/login` e validar layout split moderno.
- Entrar como vendedor e acessar `/admin/vendedor` e `/admin/vendedor/nova-proposta`.
- No mobile, testar menu da navbar sem depender de CDN externa.
- Conferir cards de planos, filtros, modal de beneficios e responsividade basica.

## 2026-05-14 - Fase 7: Preparacao para hospedagem Locaweb

### Arquivos criados
- `storage/logs/.htaccess`
- `public/.user.ini`
- `docs/DEPLOY_LOCAWEB.md`

### Arquivos alterados
- `app/config/app.php`

### Melhorias feitas
- Criado `.htaccess` em `storage/logs/` bloqueando acesso HTTP aos arquivos de log.
- Confirmado `.htaccess` em `storage/uploads/` bloqueando execucao de scripts (ja existia).
- Criado `public/.user.ini` com configuracoes PHP otimizadas para producao (erros ocultos, sessao segura, limites de upload).
- Corrigido `UPLOAD_PATH` em `app.php` — removido `realpath()` que retornava `false` quando o diretorio nao existia, causando falha silenciosa em ambientes limpos.
- Adicionada criacao automatica dos diretorios `storage/uploads/` e `storage/logs/` na inicializacao da aplicacao.
- Criado guia completo de deploy para Locaweb: estrutura de diretorios, configuracao de dominio/subdominio, banco de dados, variaveis de ambiente, permissoes e checklist de verificacao.

### Como testar
- Limpar o diretorio `storage/` e subir o servidor — os subdiretorios devem ser criados automaticamente.
- Tentar acessar `storage/logs/` pelo browser — deve retornar 403.
- Tentar fazer upload de um `.php` — deve ser bloqueado pelo `.htaccess` de uploads.
- Seguir `docs/DEPLOY_LOCAWEB.md` para o primeiro deploy em producao.

---

## 2026-05-14 - Fase 6: Comissoes e providers de cotacao

### Arquivos criados
- `app/views/pages/vendedor/comissoes.php`
- `app/services/operators/KliniSaudeProvider.php`
- `app/services/operators/SamocProvider.php`

### Arquivos alterados
- `app/models/VendedorModel.php`
- `app/controllers/VendedorController.php`
- `app/controllers/ApiController.php`
- `app/services/CotacaoService.php`
- `app/views/pages/vendedor/dashboard.php`
- `app/views/pages/proposta/lista.php`
- `app/views/pages/proposta/show.php`
- `public/index.php`
- `public/assets/css/style.css`

### Melhorias feitas
- Criada pagina de comissoes (`/admin/vendedor/comissoes`) com extrato por proposta, calculo de 10% sobre valor total, totalizador no rodape e disclaimer.
- Adicionado metodo `propostasComissionadas()` no `VendedorModel` para listar propostas elegiveis (ENVIADO_OPERADORA e CONCLUIDO com `gera_comissao = 1`).
- Corrigido `ultimasPropostas()` — trocado INNER JOIN por LEFT JOIN em `operadoras` (plano_id e nullable e causava sumir propostas do dashboard).
- Implementado endpoint `GET /api/proposta?id=N` (ou `?protocolo=PROP-XXX`) com autenticacao e controle de acesso por perfil.
- Adicionados stubs `KliniSaudeProvider` e `SamocProvider` no motor de cotacao.
- `CotacaoService` atualizado para incluir klini-saude e samoc na cadeia de providers.
- Adicionados CSS para `.csv-sample`, `.operator-control-grid` e `.operator-control-card` usados pelo painel de tabelas.
- Link "Comissoes" adicionado ao menu lateral do vendedor em 3 paginas.

### Como testar
- Entrar como vendedor e acessar `/admin/vendedor/comissoes`.
- Confirmar que so aparecem propostas com status ENVIADO_OPERADORA ou CONCLUIDO.
- Acessar `GET /api/proposta?id=1` autenticado como vendedor dono da proposta.
- Tentar acessar proposta de outro vendedor — deve retornar 403.

---

## 2026-05-14 - Fase 5: operadoras e historico de tabelas

### Arquivos alterados
- `app/controllers/AdminController.php`
- `app/controllers/ApiController.php`
- `app/controllers/PropostaController.php`
- `app/views/pages/gerente/tabelas.php`
- `public/assets/css/main.css`
- `README.md`
- `docs/CHANGELOG.md`

### Melhorias feitas
- Adicionada gestao de operadoras no painel `/admin/gerente/tabelas`.
- Permitido pausar/reativar operadoras nas cotacoes sem apagar planos.
- Adicionado historico operacional das importacoes e alteracoes de status.
- Ajustadas APIs e formulario de proposta para respeitar operadoras inativas.
- Criado visual premium para cards de controle de operadoras.

### Pontos que ainda faltam
- Importacao Excel nativa continua como evolucao futura.
- Historico detalhado por campo/preco pode virar tabela propria em fase posterior.
- Controle de permissao granular por acao pode ser refinado na fase de seguranca avancada.

### Como testar
- Entrar como gerente/admin e acessar `/admin/gerente/tabelas`.
- Pausar uma operadora e confirmar que ela sai das cotacoes e da nova proposta.
- Reativar a operadora e conferir que seus planos voltam a aparecer.
- Importar um CSV e verificar o registro no historico operacional.

## 2026-05-14 - Comparador de planos na proposta

### Arquivos alterados
- `app/controllers/PropostaController.php`
- `app/views/pages/proposta/nova.php`
- `public/assets/css/main.css`
- `README.md`
- `docs/CHANGELOG.md`

### Melhorias feitas
- Criado comparador visual de planos na etapa de selecao da proposta.
- Adicionados filtros por busca, categoria, valor maximo, ranking, popularidade, mais baratos e mais caros.
- Criado ranking demonstrativo por nota, votos e popularidade para estimular comparacao entre operadoras.
- Adicionado modal de beneficios com dados ficticios para teste comercial.
- Ajustado calculo da proposta para usar faixas etarias do titular e dependentes informados.
- Melhorado contraste e legibilidade do menu lateral dos paineis.

### Pontos que ainda faltam
- Persistir dependentes em tabela propria quando o modulo completo de dependentes for criado.
- Trocar beneficios e ranking ficticios por dados reais de pesquisa, auditoria ou feedback de pacientes.
- Criar campanhas/promocoes de grupo com regra comercial formal.

### Como testar
- Acessar `/admin/vendedor/nova-proposta`.
- Preencher data de nascimento do titular e ajustar a quantidade de vidas.
- Informar idades dos dependentes e alternar filtros/ordenacao do comparador.
- Abrir "Beneficios", selecionar um plano e enviar a proposta com documento obrigatorio.
- Conferir se o valor total muda ao alterar idade/faixa etaria.

## 2026-05-14 - Fase 3: Redesign premium

### Arquivos alterados
- `app/views/layouts/main.php`
- `app/views/pages/home.php`
- `public/assets/css/main.css`
- `public/assets/js/app.js`
- `README.md`
- `ROADMAP.md`

### Melhorias feitas
- Criada camada visual reutilizavel em `public/assets/css/main.css`.
- Refinados navbar, hero, cards de operadoras, botoes, formularios, tabelas, modais, footer e dashboards.
- Melhorada responsividade mobile para hero, menu, cards, chatbot e area administrativa.
- Adicionado fechamento consistente do menu mobile apos clique.
- Preservadas rotas, controllers, models, nomes de funcoes e fluxos POST existentes.

### Pontos que ainda faltam
- Evoluir funcionalidades completas da Fase 3 conforme roadmap: listas dedicadas por perfil, historico de pagamentos e metricas mais completas.
- Substituir textos e dados demonstrativos por dados reais conforme os modulos core avancarem.
- Revisar imagens/logos oficiais das operadoras quando os assets finais estiverem disponiveis.

### Como testar
- Abrir `/nova-versao/public/` ou a URL configurada no Apache apontando para `nova-versao/public`.
- Verificar a home publica sem JSON visivel e sem erro PHP.
- Conferir se `/assets/css/style.css`, `/assets/css/main.css` e `/assets/js/app.js` carregam no navegador.
- Testar menu mobile, modal de cotacao, troca da tabela com/sem coparticipacao e chatbot visual.
- Acessar `/login` e, com credenciais validas, validar os paineis de empresa, vendedor, supervisor e gerente.

## 2026-05-14 - Fase 3: Paineis funcionais

### Arquivos alterados
- `public/index.php`
- `app/controllers/EmpresaController.php`
- `app/controllers/VendedorController.php`
- `app/controllers/AdminController.php`
- `app/models/EmpresaModel.php`
- `app/models/VendedorModel.php`
- `app/models/PropostaModel.php`
- `app/views/pages/empresa/dashboard.php`
- `app/views/pages/empresa/propostas.php`
- `app/views/pages/empresa/pagamentos.php`
- `app/views/pages/vendedor/dashboard.php`
- `app/views/pages/vendedor/lista.php`
- `app/views/pages/supervisor/index.php`
- `app/views/pages/gerente/index.php`
- `public/assets/css/main.css`
- `README.md`
- `ROADMAP.md`

### Melhorias feitas
- Adicionadas rotas `/admin/empresa/propostas` e `/admin/empresa/pagamentos`.
- Criada listagem de propostas por empresa usando vendedores vinculados.
- Criado historico de pagamentos por empresa e vendedores vinculados.
- Registro de pagamentos PIX gerados para empresa e vendedor na tabela `pagamentos`.
- Atualizacao do pagamento no webhook Asaas quando o pagamento e confirmado/recebido.
- Adicionados indicadores de comissao estimada e propostas em andamento no painel do vendedor.
- Adicionadas metricas nos paineis de supervisor e gerente.
- Fase 3 marcada como concluida no roadmap.

### Pontos que ainda faltam
- A comissao ainda e estimada por percentual fixo enquanto a Fase 6 nao define regras por operadora.
- O historico de pagamentos passa a ser preenchido para novas cobrancas; cobrancas antigas sem registro na tabela `pagamentos` nao aparecem automaticamente.
- Relatorios e filtros avancados podem entrar em uma fase futura.

### Como testar
- Entrar como empresa e acessar `/admin/empresa/vendedores`, `/admin/empresa/propostas` e `/admin/empresa/pagamentos`.
- Entrar como vendedor e validar total de propostas, propostas em andamento e comissao estimada.
- Entrar como supervisor e validar fila, acao de aprovar/reprovar e historico.
- Entrar como gerente e validar fila, finalizacao com operadora destino e metricas gerais.

## 2026-05-14 - Fase 4: Cotacao inteligente local

### Arquivos alterados
- `public/index.php`
- `app/controllers/ApiController.php`
- `app/services/CotacaoService.php`
- `app/services/operators/TabelaLocalProvider.php`
- `app/services/operators/AmilProvider.php`
- `app/services/operators/UnimedProvider.php`
- `app/services/operators/CemeruProvider.php`
- `app/views/pages/home.php`
- `public/assets/js/app.js`
- `public/assets/css/main.css`
- `README.md`
- `ROADMAP.md`

### Melhorias feitas
- Criado endpoint `/api/cotacao`.
- Criado provider local para calcular planos usando a tabela `planos`.
- Criados placeholders de providers para Amil, Unimed e Cemeru.
- Implementado fallback automatico para tabela local enquanto APIs oficiais nao existem.
- Calculado valor total por faixa etaria a partir das idades informadas.
- Adicionada comparacao de planos no modal de cotacao da home.

### Pontos que ainda faltam
- Conectar APIs oficiais das operadoras quando credenciais/documentacao estiverem disponiveis.
- Evoluir o fallback manual por WhatsApp para pre-preencher mensagem com a cotacao escolhida.
- Transformar uma cotacao selecionada em proposta com dados reaproveitados.

### Como testar
- Acessar `/api/cotacao?idades=35,32,8&categoria=sem`.
- Na home, preencher idades no card de cotacao rapida e clicar em "Ver Planos Disponiveis".
- No assistente, informar quantidade, idades e telefone do vendedor para abrir a comparacao.

## 2026-05-14 - Fase 5: Tabelas de preco CSV

### Arquivos alterados
- `public/index.php`
- `app/controllers/AdminController.php`
- `app/services/PriceTableImportService.php`
- `app/views/pages/gerente/index.php`
- `app/views/pages/gerente/tabelas.php`
- `public/assets/css/main.css`
- `README.md`
- `ROADMAP.md`

### Melhorias feitas
- Criada rota `/admin/gerente/tabelas`.
- Criada tela de gestao de tabelas de preco para gerente/admin.
- Implementada importacao CSV com cabecalho validado.
- Atualizacao de planos existentes com incremento de `versao`.
- Cadastro de novos planos a partir de CSV.
- Controle de `vigencia_inicio`, `vigencia_fim` e `ativo`.
- Acao de ativar/desativar planos no painel.
- Registro de importacoes e alteracoes de status em `logs`.

### Pontos que ainda faltam
- Importacao nativa de Excel (`.xlsx`) ainda nao implementada.
- Historico dedicado de precos pode virar tabela propria em uma etapa futura.
- Ativacao/desativacao de operadoras pelo painel ainda esta planejada.

### Como testar
- Entrar como gerente/admin e acessar `/admin/gerente/tabelas`.
- Importar CSV com separador `;` e cabecalho documentado na tela.
- Conferir se planos existentes sobem a versao e se novos planos aparecem na lista.
- Usar o botao Ativar/Desativar em um plano e validar impacto em `/api/cotacao`.

## 2026-05-14 - Ajustes de navegacao e faixa etaria em proposta

### Arquivos alterados
- `app/controllers/PropostaController.php`
- `app/views/pages/proposta/nova.php`
- `public/assets/css/main.css`
- `docs/CHANGELOG.md`

### Melhorias feitas
- Corrigido calculo de valor da proposta para usar a faixa etaria do titular pela data de nascimento.
- Atualizado resumo visual da nova proposta para exibir idade, faixa e valor correspondente.
- Melhorado contraste, legibilidade e espacamento do menu lateral dos paineis.
- Aplicada melhoria visual de navegacao para vendedor, empresa, supervisor, gerente e admin.

### Pontos que ainda faltam
- O formulario atual coleta idade apenas do titular; dependentes ainda nao possuem idades individuais no cadastro da proposta.
- Quando dependentes forem detalhados, o calculo deve somar cada vida pela propria faixa etaria.

### Como testar
- Criar nova proposta alterando a data de nascimento do titular e observar a mudanca do valor no passo de plano.
- Enviar uma proposta e conferir se `valor_total` muda conforme a faixa etaria do titular.
- Acessar paineis de vendedor, empresa, supervisor e gerente para validar legibilidade do menu.
