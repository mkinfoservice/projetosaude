# ANÁLISE TÉCNICA — Concessionária Inteligente Bem
> Gerado em: 2026-05-13 | Analista: OpenClaude (Engenheiro Senior)

---

## 1. SUMÁRIO EXECUTIVO

O projeto atual está em estado de **protótipo evolutivo não consolidado**. Existem duas versões coexistindo no mesmo diretório — uma estática (HTML puro) e uma PHP com backend — sem integração real entre si. O sistema possui funcionalidades comercialmente relevantes mas carece de estrutura, segurança e organização para ser levado à produção.

**Risco geral: ALTO**
**Maturidade técnica atual: BAIXA (2/10)**
**Potencial do produto: ALTO**

---

## 2. MAPEAMENTO DE ARQUIVOS

### 2.1 Estrutura Raiz

```
/
├── index.html          ← Versão 1: sistema completo em HTML/JS puro (localStorage)
├── index.php           ← Versão 2: PHP com MySQL (incompleto, referencia arquivos ausentes)
├── config.php          ← CRÍTICO: credenciais de BD expostas em texto plano
├── app.js              ← Arquivo JS sem função clara (arquivo perdido)
├── api.php             ← API PHP na raiz (duplicada em /api/)
├── .htaccess           ← Configuração Apache (a validar)
├── public_html.zip     ← Backup/deploy antigo (deve ser removido)
├── imagemlanc1.png     ← Imagem na raiz (fora de /assets/)
├── imagemfern.png      ← Imagem na raiz (fora de /assets/)
├── imagemamep.png      ← Imagem na raiz (fora de /assets/)
├── imagemL.png         ← Imagem na raiz (fora de /assets/)
├── README.md
├── ROADMAP.md
└── ANALISE_TECNICA.md  (este arquivo)
```

### 2.2 Pastas Identificadas

| Pasta | Função | Status |
|-------|--------|--------|
| `/admin/` | Painéis admin (gerente, supervisor, vendedor) | Incompleto |
| `/api/` | Backend PHP | Confuso — pasta duplicada interna |
| `/api/api/` | DUPLICATA da pasta api | Problema crítico |
| `/assets/css/` | CSS do projeto | QUEBRADO |
| `/assets/js/` | JS modular | Parcialmente funcional |
| `/assets/Nova pasta/` | Pasta temp do Windows | Deve ser removida |
| `/assets/uploads/` | Upload de documentos | Desprotegido |
| `/backend/` | Backend Node.js | Tecnologia incorreta para Locaweb |
| `/database/` | Schema SQL | Schema incorreto (PostgreSQL em MySQL) |
| `/frontend/` | JS frontend duplicado | Confuso, duplicado |
| `/includes/` | Header/footer PHP | Parcialmente funcional |

---

## 3. PROBLEMAS CRÍTICOS DE SEGURANÇA

### 🔴 CRÍTICO-1 — Credenciais de BD em Texto Plano
- **Arquivo:** `config.php` (raiz)
- **Problema:** Credenciais reais do banco de dados MySQL escritas em texto plano no código
- **Risco:** Comprometimento total do banco de dados se o arquivo for lido
- **Ação imediata:** Mover credenciais para `.env` fora do webroot e alterar senha

### 🔴 CRÍTICO-2 — Credenciais e Chave API em Arquivo de Texto
- **Arquivo:** `backend/backend.env.txt`
- **Problema:** Credenciais de BD e chave de API real do Asaas expostas em arquivo `.txt`
- **Risco:** Chave Asaas pode ser usada para criar cobranças fraudulentas
- **Ação imediata:** Revogar e gerar nova chave API; remover arquivo imediatamente

### 🔴 CRÍTICO-3 — Login com Senha Hardcoded no JavaScript Público
- **Arquivo:** `index.html` (linha ~791)
- **Problema:** Supervisor/Gerente com senha "123456" visível no código-fonte da página
- **Risco:** Qualquer visitante pode inspecionar o código e fazer login como supervisor/gerente
- **Ação imediata:** Remover index.html de produção; implementar autenticação server-side

### 🔴 CRÍTICO-4 — Credencial Admin no JavaScript Público
- **Arquivo:** `index.html` (linha ~493)
- **Problema:** Credencial de admin (`phone: '21998163817', password: '1234'`) inicializada no JS público
- **Risco:** Acesso irrestrito ao painel admin por qualquer visitante

### 🟡 ALTO-1 — Upload Directory Desprotegido
- **Arquivo:** `assets/uploads/htaccess.txt`
- **Problema:** `.htaccess` salvo com extensão `.txt` — proteção contra execução de PHP está **inativa**
- **Risco:** Upload de arquivo PHP malicioso pode resultar em execução de código no servidor
- **Ação imediata:** Renomear para `.htaccess` com conteúdo que bloqueia execução de scripts

### 🟡 ALTO-2 — CORS Mal Configurado
- **Arquivo:** `api/ajax-handler.php` (linha 16)
- **Problema:** `http://cardsaude_db.mysql.dbaas.com.br` listado como origem CORS permitida
- **Observação:** Esse é o host do banco de dados, não um servidor web — configuração sem sentido

---

## 4. PROBLEMAS ARQUITETURAIS

### 4.1 Dois Sistemas Coexistentes Incompatíveis

| Aspecto | index.html (v1) | index.php (v2) |
|---------|-----------------|-----------------|
| Armazenamento | localStorage | MySQL |
| Autenticação | JavaScript no browser | PHP Session |
| Pagamentos | QR code estático | Asaas API |
| Estado | Funcional (básico) | Parcialmente implementado |

**Resultado:** Ambiguidade total sobre qual versão é a "oficial".

### 4.2 Node.js em Hospedagem PHP (Locaweb)

- **Pasta `/backend/`** contém Node.js/Express com `package.json`, `server.js`, `db.js`
- **Locaweb hospedagem compartilhada NÃO suporta Node.js**
- Esta pasta é código morto e deve ser arquivada (não deletada ainda)

### 4.3 Schema PostgreSQL em Servidor MySQL

- **Arquivo:** `database/shema.sql` (typo: "shema" em vez de "schema")
- **Problema:** Usa `CREATE EXTENSION IF NOT EXISTS "uuid-ossp"` e `uuid_generate_v4()` — sintaxe PostgreSQL
- **Servidor alvo:** MySQL na Locaweb
- **Resultado:** Schema nunca pôde ser executado no servidor correto

### 4.4 CSS Principal Quebrado

```css
/* assets/css/style.css — conteúdo atual: */
@import url("../Nova%20pasta/style.css");
```
O arquivo CSS principal apenas importa de uma pasta temporária do Windows (`Nova pasta`). Qualquer página que referencie `/assets/css/style.css` fica completamente sem estilo.

### 4.5 Duplicação Massiva de Arquivos

| Arquivo | Ocorrências | Localizações |
|---------|-------------|------------|
| `api.php` | 3 cópias | `/api.php`, `/api/api.php`, `/api/api/api.php` |
| `config.php` | 3 cópias | `/config.php`, `/api/config.php`, `/api/api/config.php` |
| `app.js` | 3 cópias | `/app.js`, `/assets/js/app.js`, `/frontend/app.js` |
| `footer.php` | 2 cópias | `/includes/footer.php`, `/includes/frooter.php` (typo) |

### 4.6 index.php Referencia Objetos Inexistentes

O `index.php` chama `ModalManager.show()` mas `ModalManager` não está definido em nenhum arquivo JS existente.

---

## 5. PROBLEMAS DE ORGANIZAÇÃO

| Problema | Localização | Impacto |
|----------|-------------|---------|
| Imagens na raiz | `imagemlanc1.png`, etc. | Organização |
| Pasta temporária Windows | `/assets/Nova pasta/` | CSS quebrado |
| Backup de deploy | `public_html.zip` | Segurança + lixo |
| .htaccess salvo como .txt | `/assets/uploads/htaccess.txt` | Segurança |
| Pasta api aninhada | `/api/api/` | Confusão de rotas |
| Typo no schema | `shema.sql` | Organização |
| Hosts BD inconsistentes | `config.php` vs `backend.env.txt` | Confusão |

---

## 6. O QUE DEVE SER PRESERVADO E APROVEITADO

### 6.1 Fluxo de Negócio (index.html — para referência de UX)
- ✅ Cadastro de empresa com CNPJ + validação
- ✅ Gestão de logins de vendedores por empresa
- ✅ Fluxo de cotação multi-etapas (5 passos)
- ✅ Tabela de preços por faixa etária e coparticipação
- ✅ Ficha completa de proposta com dados de beneficiário
- ✅ Fluxo de aprovação: Supervisor → Gerente → Operadora
- ✅ Atendimento automático (chatbot embarcado)
- ✅ Identificação de vendedor por telefone (link vendedor → proposta)

### 6.2 Código PHP Bem Escrito (aproveitável na nova versão)
- ✅ `api/auth.php` — bcrypt, CSRF, session regeneration, timeout
- ✅ `includes/functions.php` — h(), formatMoney(), getIdadeFaixa()
- ✅ `api/ajax-handler.php` — estrutura de rotas com switch/case

### 6.3 Conteúdo Comercial
- ✅ Operadoras: AMIL, UNIMED, KLINI SAÚDE, CEMERU, SAMOC, OPLAN SAÚDE, ABRABDIR, ABRACEM, ABA
- ✅ Tabelas de preço por faixa etária (valores de referência)
- ✅ Contato: (21) 96882-7864
- ✅ Textos de marketing presentes no HTML

---

## 7. INVENTÁRIO TECNOLÓGICO

| Tecnologia | Uso Atual | Decisão |
|-----------|-----------|---------|
| PHP 8+ | Backend principal | ✅ MANTER |
| MySQL | Banco de dados Locaweb | ✅ MANTER |
| HTML5 / CSS3 | Frontend | ✅ MANTER |
| JavaScript ES6+ (vanilla) | Frontend | ✅ MANTER |
| Asaas API | Pagamentos PIX | ✅ MANTER (reconfigurar) |
| ViaCEP API | Auto-preench. CEP | ✅ MANTER |
| Node.js / Express | Backend paralelo | ❌ DESCONTINUAR |
| localStorage como BD | Dados no browser | ❌ SUBSTITUIR por MySQL |
| JWT (Node) | Auth no backend JS | ❌ DESCONTINUAR |

---

## 8. AÇÕES URGENTES (ANTES DE QUALQUER NOVO DEPLOY)

1. **[URGENTE]** Revogar a chave API Asaas exposta no `backend/backend.env.txt`
2. **[URGENTE]** Alterar a senha do banco de dados MySQL
3. **[URGENTE]** Remover `config.php` da raiz (credenciais expostas)
4. **[URGENTE]** Remover `public_html.zip` da raiz
5. **[URGENTE]** Renomear `assets/uploads/htaccess.txt` → `.htaccess`
6. **[URGENTE]** Não fazer commit das credenciais no Git

---

## 9. PRÓXIMOS PASSOS

O novo sistema será desenvolvido em `/nova-versao/` conforme a estrutura definida em `nova-versao/ESTRUTURA.md`.

O sistema antigo permanecerá intacto como referência até validação completa da nova versão.

---

*Análise técnica gerada por OpenClaude — Projeto Concessionária Inteligente Bem — 2026-05-13*
