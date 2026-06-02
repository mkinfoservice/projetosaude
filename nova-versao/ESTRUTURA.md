# ESTRUTURA — Concessionária Inteligente Bem
> Arquitetura do projeto nova versão. Atualizado em: 2026-05-13

---

## ESTRUTURA DE PASTAS

```
nova-versao/
│
├── public/                         ← WEBROOT (public_html aponta aqui)
│   ├── index.php                   ← Front controller — todas as requests passam aqui
│   ├── .htaccess                   ← Rewrite rules + headers de segurança
│   └── assets/
│       ├── css/
│       │   └── style.css           ← CSS premium (design tokens, componentes, responsivo)
│       ├── js/
│       │   └── app.js              ← JS modular (namespace CIB, chatbot, máscaras, modais)
│       └── img/
│           └── (imagens do site)
│
├── app/                            ← CÓDIGO DA APLICAÇÃO (não acessível pelo browser)
│   │
│   ├── config/
│   │   ├── database.php            ← Conexão PDO singleton (lê .env)
│   │   └── app.php                 ← Configurações gerais, helpers globais
│   │
│   ├── controllers/
│   │   ├── HomeController.php      ← Landing page + 404
│   │   ├── AuthController.php      ← Login + Logout
│   │   ├── EmpresaController.php   ← Painel empresa (a implementar)
│   │   ├── VendedorController.php  ← Painel vendedor (a implementar)
│   │   ├── AdminController.php     ← Painéis supervisor/gerente (a implementar)
│   │   ├── PropostaController.php  ← CRUD de propostas (a implementar)
│   │   └── ApiController.php       ← Endpoints JSON (a implementar)
│   │
│   ├── models/
│   │   ├── BaseModel.php           ← CRUD base (a implementar)
│   │   ├── Empresa.php             ← Model empresa (a implementar)
│   │   ├── Vendedor.php            ← Model vendedor (a implementar)
│   │   ├── Proposta.php            ← Model proposta (a implementar)
│   │   ├── Operadora.php           ← Model operadora (a implementar)
│   │   └── Plano.php               ← Model plano/preço (a implementar)
│   │
│   ├── services/
│   │   ├── AsaasService.php        ← Integração PIX/Asaas (a implementar)
│   │   ├── UploadService.php       ← Gestão de uploads segura (a implementar)
│   │   ├── CepService.php          ← Wrapper ViaCEP (a implementar)
│   │   └── operators/
│   │       ├── TabelaLocalProvider.php ← Preços do BD (a implementar)
│   │       ├── AmilProvider.php        ← API AMIL (a implementar)
│   │       ├── UnimedProvider.php      ← API UNIMED (a implementar)
│   │       └── CemeruProvider.php      ← API CEMERU (a implementar)
│   │
│   └── views/
│       ├── layouts/
│       │   ├── main.php            ← Layout público (navbar + footer + meta)
│       │   └── admin.php           ← Layout admin (a implementar)
│       │
│       ├── pages/
│       │   ├── home.php            ← Landing page premium
│       │   ├── login.php           ← Página de login
│       │   ├── empresa/
│       │   │   └── dashboard.php   ← Painel empresa (a implementar)
│       │   ├── vendedor/
│       │   │   └── dashboard.php   ← Painel vendedor (a implementar)
│       │   ├── admin/
│       │   │   ├── supervisor.php  ← Painel supervisor (a implementar)
│       │   │   └── gerente.php     ← Painel gerente (a implementar)
│       │   └── proposta/
│       │       └── ficha.php       ← Ficha de cadastro (a implementar)
│       │
│       └── components/
│           ├── navbar.php          ← Navbar (responsiva, multi-perfil)
│           ├── footer.php          ← Footer completo
│           ├── modal-login.php     ← Modal de login (a implementar)
│           └── modal-proposta.php  ← Ficha proposta modal (a implementar)
│
├── storage/
│   ├── uploads/
│   │   └── .htaccess               ← Bloqueia execução de scripts nos uploads
│   └── logs/                       ← Logs de sistema (criado automaticamente)
│
├── database/
│   └── schema.sql                  ← Schema MySQL 8+ completo com dados iniciais
│
├── docs/                           ← Documentação adicional (manuais, fluxogramas)
│
├── .env.example                    ← Template de variáveis de ambiente
├── .env                            ← NÃO COMMITAR — credenciais reais (criar localmente)
├── .htaccess                       ← Redirect de / para /public/
├── README.md                       ← Setup e uso
├── ROADMAP.md                      ← Plano de evolução
├── ESTRUTURA.md                    ← Este arquivo
└── ANALISE_TECNICA.md              ← Análise do sistema legado (na raiz do projeto)
```

---

## PADRÃO DE ROTEAMENTO

O sistema usa um **front controller simples** sem framework.

```
Requisição → public/index.php → resolve URI → carrega Controller → renderiza View
```

### Tabela de rotas (public/index.php)

| URI | Controller | Método |
|-----|-----------|--------|
| `/` | HomeController | index |
| `/login` | AuthController | login |
| `/logout` | AuthController | logout |
| `/admin/empresa` | EmpresaController | dashboard |
| `/admin/vendedor` | VendedorController | dashboard |
| `/admin/supervisor` | AdminController | supervisor |
| `/admin/gerente` | AdminController | gerente |
| `/api/operadoras` | ApiController | operadoras |
| `/api/planos` | ApiController | planos |
| `/api/cep` | ApiController | cep |
| `/api/auth` | ApiController | auth |
| `/api/empresa` | ApiController | empresa |
| `/api/vendedor` | ApiController | vendedor |
| `/api/proposta` | ApiController | proposta |
| `/api/payment` | ApiController | payment |

---

## CONVENÇÕES DE CÓDIGO

### PHP
- `declare(strict_types=1)` em todos os arquivos PHP
- Classes: `PascalCase`
- Métodos/funções: `camelCase`
- Variáveis: `snake_case`
- Constantes: `UPPER_SNAKE_CASE`
- Queries: sempre PDO com `prepare() + execute()`

### JavaScript
- Namespace global: `CIB` (Concessionária Inteligente Bem)
- ES6+ com `'use strict'`
- Sem dependências externas (jQuery, etc.)

### CSS
- Design tokens em `:root` como CSS custom properties
- BEM-like para componentes: `.card`, `.card-body`, `.card-title`
- Utilities: `.text-center`, `.hidden`, `.btn`, `.btn-primary`
- Mobile-first com breakpoints: 480px, 768px, 1024px

---

## BANCO DE DADOS

### Tabelas principais

| Tabela | Função |
|--------|--------|
| `operadoras` | Operadoras de saúde cadastradas |
| `planos` | Planos com tabela de preços por faixa etária |
| `empresas` | Empresas parceiras |
| `vendedores` | Vendedores, corretores, supervisores, gerentes |
| `clientes` | Beneficiários das propostas |
| `propostas` | Propostas com fluxo de aprovação |
| `pagamentos` | Cobranças PIX via Asaas |
| `logs` | Auditoria de ações do sistema |

### Faixas etárias (coluna de preço)

| Coluna | Faixa |
|--------|-------|
| `faixa_0_18` | 0 a 18 anos |
| `faixa_19_28` | 19 a 28 anos |
| `faixa_29_43` | 29 a 43 anos |
| `faixa_44_58` | 44 a 58 anos |
| `faixa_59_plus` | 59 anos ou mais |

---

## FLUXO DE APROVAÇÃO DE PROPOSTA

```
Cliente → Vendedor cria proposta
                ↓
        Status: PENDENTE_SUPERVISOR
                ↓
    Supervisor aprova ou reprova
                ↓
        Status: APROVADO_SUPERVISOR
                ↓
    Gerente finaliza → escolhe concessionária
                ↓
        Status: ENVIADO_OPERADORA
                ↓
           Status: CONCLUIDO
```

---

*Estrutura mantida por OpenClaude — Projeto Concessionária Inteligente Bem*
