# Concessionária Inteligente Bem — Nova Versão

**Plataforma comercial premium de cotação e gestão de planos de saúde**

> Stack: PHP 8+ puro | MySQL | HTML5 | CSS3 | JavaScript Vanilla | Apache/Locaweb

---

                                                                                                                                                                                                                                      
  ┌──────────────┬──────────────────┬───────────┬────────────────────────────────────┐         
  │    Perfil    │ Login (Telefone) │   Senha   │               Acessa               
  ├──────────────┼──────────────────┼───────────┼────────────────────────────────────┤                  
  │ Supervisor   │ 21911110001      │ Teste@123 │ /admin/supervisor
  ├──────────────┼──────────────────┼───────────┼────────────────────────────────────┤                  
  │ Gerente      │ 21911110002      │ Teste@123 │ /admin/gerente                     │
  ├──────────────┼──────────────────┼───────────┼────────────────────────────────────┤                  
  │ Admin Master │ 21911110003      │ Teste@123 │ /admin/supervisor + /admin/gerente 
  └──────────────┴──────────────────┴───────────┴────────────────────────────────────┘   

## Sobre o Projeto

O sistema permite:

- Cotação de planos de saúde (múltiplas operadoras)
- Gestão de propostas com fluxo de aprovação
- Gestão de vendedores/corretores por empresa
- Cadastro de empresas parceiras
- Painel administrativo multi-nível (Supervisor, Gerente, Empresa, Vendedor)
- Integração com Asaas para pagamentos PIX
- Importação futura de tabelas de preço

---

## Configuração Inicial

### 1. Copiar variáveis de ambiente

```bash
cp .env.example .env
```

Edite `.env` com suas credenciais reais (banco MySQL, API Asaas, etc.).

### 2. Criar banco de dados

Execute no MySQL (via phpMyAdmin ou terminal):

```sql
CREATE DATABASE seu_banco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Depois execute o schema:

```bash
# Via terminal MySQL
mysql -u usuario -p seu_banco < database/schema.sql

# Ou importe pelo phpMyAdmin
```

### 3. Gerar hash da senha admin

```php
<?php echo password_hash('SuaSenhaSegura@123', PASSWORD_BCRYPT, ['cost' => 12]);
```

Cole o hash no `schema.sql` ou atualize via phpMyAdmin antes de executar.

### 4. Configurar Apache / cPanel (Locaweb)

No Locaweb, o `public_html` deve apontar para `nova-versao/public/`.

Verifique se o arquivo `nova-versao/public/.htaccess` está ativo (mod_rewrite habilitado).

> **Guia completo de deploy:** `docs/DEPLOY_LOCAWEB.md`

---

## Estrutura de Pastas

```
nova-versao/
├── public/                 ← Webroot (public_html aponta aqui)
│   ├── index.php           ← Front controller / roteador
│   ├── .htaccess           ← Rewrite rules
│   └── assets/
│       ├── css/app.css     ← Design system premium ativo
│       └── js/app.js       ← JS modular
├── app/
│   ├── config/             ← database.php, app.php
│   ├── controllers/        ← HomeController, AuthController, etc.
│   ├── models/             ← Modelos de dados
│   ├── services/           ← Serviços (Asaas, operadoras, etc.)
│   └── views/              ← Templates PHP (layouts, pages, components)
├── storage/
│   └── uploads/            ← Arquivos enviados (protegido com .htaccess)
├── database/
│   └── schema.sql          ← Schema MySQL completo com dados iniciais
├── docs/                   ← Documentação adicional
├── .env.example            ← Template de configuração
├── .htaccess               ← Redirect para /public/
├── README.md               ← Este arquivo
├── ROADMAP.md              ← Plano de evolução
└── ESTRUTURA.md            ← Arquitetura detalhada
```

---

## Perfis de Acesso

| Perfil | Login via | Acesso |
|--------|-----------|--------|
| Admin | Telefone + Senha | Tudo |
| Supervisor | Telefone + Senha | Propostas pendentes → aprovar/reprovar |
| Gerente | Telefone + Senha | Propostas aprovadas pelo supervisor → finalizar |
| Empresa | E-mail + Senha | Gestão de vendedores, ver propostas da empresa |
| Vendedor | Telefone + Senha | Cadastrar clientes, enviar propostas |

---

## Segurança

- Senhas: bcrypt custo 12 com rehash automático
- Sessões: HTTPS only, HttpOnly, SameSite=Strict, regeneração no login
- CSRF: token por sessão, validado em todas as ações de escrita
- Uploads: verificação de MIME type + extensão + .htaccess bloqueador
- SQL: PDO com prepared statements em 100% das queries
- Credenciais: nunca no código, sempre no `.env` fora do webroot

---

## Rotas Administrativas

| Rota | Funcao |
|------|--------|
| `/admin/empresa` | Painel da empresa com resumo operacional. |
| `/admin/empresa/vendedores` | Lista de vendedores vinculados a empresa. |
| `/admin/empresa/vendedores/novo` | Cadastro de vendedor pela empresa. |
| `/admin/empresa/propostas` | Propostas vinculadas aos vendedores da empresa. |
| `/admin/empresa/pagamentos` | Historico de pagamentos PIX da empresa e vendedores. |
| `/admin/vendedor` | Painel do vendedor com propostas e comissao estimada. |
| `/admin/vendedor/propostas` | Lista de propostas do vendedor. |
| `/admin/vendedor/nova-proposta` | Ficha de nova proposta com comparador de planos por idade, valor, ranking e beneficios. |
| `/admin/supervisor` | Fila de aprovacao/reprovacao de propostas. |
| `/admin/gerente` | Fila de finalizacao e envio para operadora. |
| `/admin/gerente/tabelas` | Gestao de tabelas de preco, CSV, vigencia, status dos planos, operadoras e historico operacional. |

---

## APIs Publicas

| Rota | Funcao |
|------|--------|
| `/api/operadoras` | Lista operadoras ativas. |
| `/api/planos` | Lista planos por operadora e categoria. |
| `/api/cotacao` | Calcula cotacao por idades, categoria e operadora opcional. |
| `/api/cep` | Consulta CEP. |

Exemplo:

```text
/api/cotacao?idades=35,32,8&categoria=sem
```

---

## Assets Visuais

- `public/assets/css/app.css` e o design system ativo da aplicacao, com tokens, navbar, hero, cards, formularios, tabelas, modais, dashboards, comparador de planos e responsividade.
- `public/assets/css/style.css` e `public/assets/css/main.css` ficam como historico visual da reconstrucao, mas o layout principal carrega `app.css`.
- `public/assets/js/app.js` concentra apenas comportamentos visuais leves, como menu mobile, modais, mascaras simples e feedback do chatbot.
- `docs/DESIGN_SYSTEM.md` documenta tokens e componentes do visual premium healthtech aplicado em `public/assets/css/app.css`.

Na ficha `/admin/vendedor/nova-proposta`, a selecao de plano usa os dados locais da tabela `planos`, calcula o valor pela faixa etaria do titular e dos dependentes informados, e mantem os campos antigos sincronizados para preservar o fluxo de gravacao da proposta.

O ponto de entrada publico continua sendo `nova-versao/public/index.php`.

---

## Licença

Uso interno — Concessionária Inteligente Bem © 2026
