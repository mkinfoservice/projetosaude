# ROADMAP — Concessionária Inteligente Bem
> Plano de evolução progressiva do sistema. Atualizado em: 2026-05-14

---

## LEGENDA

- ✅ Concluído
- 🔄 Em progresso
- 📋 Planejado
- 💡 Ideia futura

---

## FASE 1 — BASE E ESTRUTURA ✅

**Meta:** Organizar, documentar, estabilizar.

- ✅ Análise técnica do projeto original (`ANALISE_TECNICA.md`)
- ✅ Estrutura de pastas MVC simplificada (`/nova-versao/`)
- ✅ Schema MySQL correto (substituindo o PostgreSQL antigo)
- ✅ Configuração segura via `.env` (sem credenciais no código)
- ✅ Front controller / roteador simples (`public/index.php`)
- ✅ Layout base premium (navbar, footer, CSS, JS)
- ✅ Landing page premium (hero, operadoras, como funciona, tabela de preços, chatbot)
- ✅ Login seguro (bcrypt + session regeneration + CSRF)
- ✅ Proteção de uploads (`.htaccess` correto)
- ✅ Documentação: README, ROADMAP, ANALISE_TECNICA, ESTRUTURA

---

## FASE 2 — MÓDULOS CORE 📋

**Meta:** Sistema funcional com fluxo principal.

### 2.1 Cadastro de Empresa
- 📋 Form de cadastro com CNPJ (validação real)
- 📋 Upload de contrato social (PDF)
- 📋 Geração de cobrança PIX via Asaas
- 📋 Webhook de confirmação de pagamento
- 📋 Ativação automática da conta após pagamento

### 2.2 Cadastro de Vendedor
- 📋 Form completo com CPF, RG, CEP auto-preenchimento
- 📋 Upload de documentos (frente/verso)
- 📋 Vinculação à empresa
- 📋 Geração de PIX mensalidade
- 📋 ID de acesso único por vendedor

### 2.3 Fluxo de Propostas
- 📋 Ficha completa do beneficiário titular
- 📋 Upload de documentos da proposta
- 📋 Envio da proposta → status "Pendente Supervisor"
- 📋 Painel Supervisor: aprovar/reprovar com justificativa
- 📋 Painel Gerente: finalizar → escolher concessionária destino
- 📋 Notificações de mudança de status
- 📋 Protocolo único por proposta (PROP-XXXXXXXX)

---

## FASE 3 — PAINÉIS ADMINISTRATIVOS ✅

**Meta:** Dashboards funcionais para cada perfil.

**Status:** dashboards funcionais e redesign premium aplicados, preservando rotas e lógica existentes.

### Painel Empresa
- ✅ Lista de vendedores com status
- ✅ Propostas da empresa
- ✅ Histórico de pagamentos

### Painel Vendedor
- ✅ Lista de clientes/propostas próprias
- ✅ Indicador de comissão gerada
- ✅ Status de cada proposta em tempo real

### Painel Supervisor
- ✅ Queue de propostas pendentes
- ✅ Ação aprovar/reprovar com comentário
- ✅ Histórico de decisões

### Painel Gerente
- ✅ Propostas aprovadas pelo supervisor
- ✅ Ação de finalização + seleção de operadora destino
- ✅ Dashboard com métricas gerais

---

## FASE 4 — SISTEMA DE COTAÇÃO INTELIGENTE ✅

**Meta:** Motor de cotação multi-operadora.

**Status:** primeira versao funcional com provider local, fallback e comparacao por faixa etaria; comparador de planos aplicado na nova proposta.

### 4.1 Providers de Operadoras
- ✅ `TabelaLocalProvider.php` — tabela do BD (pronto para usar)
- ✅ `AmilProvider.php` — integração API (placeholder para quando disponível)
- ✅ `UnimedProvider.php` — integração API (placeholder para quando disponível)
- ✅ `CemeruProvider.php` — integração API (placeholder para quando disponível)

### 4.2 Estratégia de Fallback
- ✅ Tenta API oficial da operadora
- ✅ Fallback para tabela local (banco de dados)
- ✅ Fallback para cotação manual via WhatsApp

### 4.3 Cálculo por Faixa Etária
- ✅ Input: idades dos beneficiários
- ✅ Output: valor total calculado por faixa
- ✅ Comparação lado a lado entre operadoras

---

## FASE 5 — TABELAS DE PREÇO 🔄

**Meta:** Gestão de vigências e importação de tabelas.

- 🔄 Importação via CSV/Excel (CSV no painel concluído; Excel futuro)
- ✅ Versionamento de tabela com vigência (data_inicio, data_fim)
- 🔄 Histórico de alterações de preço via logs operacionais
- ✅ Ativação/desativação de planos
- 📋 Ativação/desativação de operadoras no painel

---

## FASE 6 — COMISSÕES 💡

**Meta:** Cálculo e gestão de comissões de vendedores.

- 💡 Percentual de comissão por operadora
- 💡 Lançamento automático ao aprovar proposta
- 💡 Relatório mensal de comissões
- 💡 Repasse para empresa parceira

---

## FASE 7 — SEGURANÇA AVANÇADA 💡

- 💡 Rate limiting nas rotas de login (5 tentativas → bloqueio temporário)
- 💡 2FA opcional via WhatsApp/Email
- 💡 Log completo de ações auditáveis
- 💡 Expiração de sessão por inatividade com modal de aviso
- 💡 Política de senha forte no cadastro

---

## FASE 8 — PERFORMANCE E DEPLOY 💡

- 💡 Cache de tabela de preços (APCu ou arquivo)
- 💡 Compressão Gzip via .htaccess
- 💡 Lazy loading de imagens
- 💡 Checklist de deploy para Locaweb
- 💡 Script de migração do sistema antigo

---

## PRINCÍPIOS QUE NUNCA MUDAM

1. **Sem frameworks pesados** — PHP puro, JS vanilla
2. **Compatível com Locaweb** — sem Node.js, sem Docker
3. **Segurança primeiro** — nenhuma credencial no código
4. **Progressivo** — evoluir módulo a módulo, nunca reescrever tudo
5. **Documentar tudo** — cada fase documentada antes de implementar

---

*Roadmap mantido por OpenClaude — Projeto Concessionária Inteligente Bem*
