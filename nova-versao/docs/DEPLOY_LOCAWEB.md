# Deploy — Locaweb (Hospedagem Compartilhada)

> Guia passo a passo para publicar o sistema **Concessionária Inteligente Bem** na Locaweb.

---

## Pré-requisitos no plano Locaweb

Confirme antes de começar:

| Requisito | Mínimo | Verificar em |
|-----------|--------|-------------|
| PHP | 8.1+ | cPanel → MultiPHP Manager |
| MySQL | 8.0+ | cPanel → Bancos de Dados |
| mod_rewrite | Ativado | cPanel → Apache Handlers |
| Uploads | 10 MB | cPanel → PHP ini (ou .user.ini) |
| Subdomínios/Addon domains | Sim | cPanel → Domínios |

---

## Estrutura de diretórios no servidor

O projeto deve ficar **fora do `public_html`** para que `app/`, `storage/` e `.env` não fiquem expostos:

```
/home/usuario/                  ← diretório home
├── concessionaria/             ← subir ESTE diretório inteiro aqui
│   ├── app/
│   ├── database/
│   ├── docs/
│   ├── storage/
│   │   ├── uploads/
│   │   └── logs/
│   ├── public/                 ← document root do domínio/subdomínio
│   │   ├── index.php
│   │   ├── .htaccess
│   │   ├── .user.ini
│   │   └── assets/
│   ├── .env                    ← NUNCA commitar
│   └── .env.example
└── public_html/                ← site principal (não usar para este projeto)
```

---

## Passo 1 — Upload dos arquivos

1. Compacte a pasta `nova-versao/` em um arquivo `.zip`
2. No cPanel → Gerenciador de Arquivos, suba o zip para `/home/usuario/concessionaria/`
3. Extraia o zip
4. Renomeie se necessário: o caminho final deve ser `/home/usuario/concessionaria/public/index.php`

**Alternativa via FTP (FileZilla):**
- Host: `ftp.seudominio.com.br`
- Porta: 21 (ou 22 para SFTP)
- Usuario/senha: credenciais cPanel

---

## Passo 2 — Configurar o domínio

**Para domínio principal** (ex: `seusite.com.br` aponta para `/home/usuario/public_html/`):

Crie um arquivo `/home/usuario/public_html/.htaccess` com:

```apache
RewriteEngine On
RewriteRule ^(.*)$ /home/usuario/concessionaria/public/$1 [L]
```

**Recomendado: Subdomínio dedicado** (ex: `app.seusite.com.br`):

1. cPanel → Domínios → Subdomínios
2. Subdomínio: `app`
3. Domínio raiz: `seusite.com.br`
4. Document Root: `/home/usuario/concessionaria/public`
5. Clique em **Criar**

---

## Passo 3 — Banco de dados

1. cPanel → Banco de Dados MySQL → **Criar Banco de Dados**
   - Nome: `usuario_cib` (prefixo do usuário é adicionado automaticamente)

2. Crie um usuário MySQL e dê **Todos os Privilégios**

3. Importe o schema:
   - cPanel → phpMyAdmin → selecione o banco → **Importar**
   - Arquivo: `nova-versao/database/schema.sql`

---

## Passo 4 — Arquivo `.env`

1. Copie `.env.example` para `.env` na raiz do projeto (`/home/usuario/concessionaria/.env`)
2. Edite com os dados reais:

```env
DB_HOST=localhost
DB_NAME=usuario_cib
DB_USER=usuario_cib_user
DB_PASS=SuaSenhaAqui
DB_PORT=3306

APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.seusite.com.br
APP_SECRET=<64 caracteres aleatórios — use: openssl rand -hex 32>

ASAAS_API_URL=https://api.asaas.com/v3
ASAAS_API_KEY=SuaChaveAsaasProducao
ASAAS_WEBHOOK_TOKEN=<token aleatorio>

SESSION_LIFETIME=3600
UPLOAD_MAX_SIZE_MB=10
LOG_ENABLED=true
```

> **Importante:** Troque `api-sandbox.asaas.com` por `api.asaas.com` em produção.

---

## Passo 5 — Configurar PHP 8.1+

No cPanel → **MultiPHP Manager**:

1. Selecione o domínio/subdomínio
2. Escolha **PHP 8.1** ou **PHP 8.2**
3. Salve

O arquivo `public/.user.ini` já está configurado para produção (erros ocultos, sessão segura, limites corretos).

---

## Passo 6 — Permissões de arquivo

Via SSH ou cPanel → Terminal:

```bash
# Leitura/escrita para o servidor
chmod 755 /home/usuario/concessionaria/
chmod -R 755 /home/usuario/concessionaria/app/
chmod -R 755 /home/usuario/concessionaria/public/
chmod 600 /home/usuario/concessionaria/.env

# Storage precisa de escrita pelo PHP
chmod -R 775 /home/usuario/concessionaria/storage/
```

---

## Passo 7 — Verificar funcionamento

Acesse pelo navegador:

| URL | Resultado esperado |
|-----|--------------------|
| `https://app.seusite.com.br/` | Landing page carrega |
| `https://app.seusite.com.br/login` | Tela de login carrega |
| `https://app.seusite.com.br/api/operadoras` | JSON com operadoras |
| `https://app.seusite.com.br/admin/vendedor` | Redireciona para login |

---

## Configurar Webhook Asaas

No painel Asaas → Configurações → Webhooks:

- URL: `https://app.seusite.com.br/api/webhook/asaas`
- Token: mesmo valor de `ASAAS_WEBHOOK_TOKEN` no `.env`
- Eventos: `PAYMENT_CONFIRMED`, `PAYMENT_RECEIVED`, `PAYMENT_OVERDUE`

---

## Checklist pré-publicação

- [ ] `APP_ENV=production` e `APP_DEBUG=false` no `.env`
- [ ] `APP_SECRET` com valor único e forte
- [ ] `ASAAS_API_URL` apontando para produção (não sandbox)
- [ ] Banco importado e dados de seed conferidos
- [ ] Subdomínio configurado com document root correto
- [ ] PHP 8.1+ ativado no MultiPHP Manager
- [ ] `storage/` com permissão de escrita pelo PHP
- [ ] Acessar `/api/operadoras` e confirmar resposta JSON
- [ ] Criar vendedor de teste e enviar proposta completa
- [ ] Confirmar que uploads vão para `storage/uploads/propostas/`
- [ ] Confirmar que logs aparecem em `storage/logs/`
- [ ] Certificado SSL ativo (cPanel → SSL/TLS)

---

## Problemas comuns

**Erro 500 / página em branco:**
- Ative temporariamente `APP_DEBUG=true` para ver o erro
- Verifique permissões do `.env` e `storage/`
- Confirme que PHP 8.1+ está ativo

**Rotas retornam 404:**
- Confirme que `mod_rewrite` está ativo
- Verifique se `AllowOverride All` está configurado no Apache
- O `.htaccess` em `public/` precisa estar presente

**Uploads falham:**
- Verifique `upload_max_filesize` e `post_max_size` em `.user.ini`
- Confirme permissão 775 em `storage/uploads/`

**Sessão expira rapidamente:**
- Ajuste `SESSION_LIFETIME` no `.env` (valor em segundos)
- Verifique se o domínio usa HTTPS (cookie `secure` requer HTTPS)
