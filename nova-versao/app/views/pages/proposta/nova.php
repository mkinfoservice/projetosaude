<?php
/**
 * views/pages/proposta/nova.php
 * Nova Proposta — Design Premium SaaS / Healthtech
 */

// Indexa planos por operadora para JS
$planosPorOperadora = [];
foreach ($planos as $p) {
    $planosPorOperadora[$p['operadora_id']][] = $p;
}

$operadorasPorId = [];
foreach ($operadoras as $op) {
    $operadorasPorId[(int)$op['id']] = $op;
}
?>
<div class="dashboard-wrapper">

    <!-- ===================================================== SIDEBAR -->
    <aside class="dashboard-sidebar">

        <div class="sidebar-logo">
            <svg viewBox="0 0 40 40" fill="none" width="32" height="32">
                <rect width="40" height="40" rx="8" fill="#00C48C"/>
                <path d="M20 29s-9-5.6-9-12.1A5.1 5.1 0 0120 13a5.1 5.1 0 019 3.9C29 23.4 20 29 20 29z" fill="white"/>
            </svg>
            <span>CIB</span>
        </div>

        <nav class="sidebar-nav">
            <p class="sidebar-section-label">Principal</p>

            <a href="/admin/vendedor" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Meu Painel
            </a>

            <a href="/admin/vendedor/nova-proposta" class="sidebar-link active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 8v8M8 12h8"/>
                </svg>
                Nova Proposta
            </a>

            <p class="sidebar-section-label">Gestão</p>

            <a href="/admin/vendedor/propostas" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="8" y1="13" x2="16" y2="13"/>
                    <line x1="8" y1="17" x2="12" y2="17"/>
                </svg>
                Propostas
            </a>

            <a href="/admin/vendedor/comissoes" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7H14a3.5 3.5 0 010 7H6"/>
                </svg>
                Comissões
            </a>

            <?php if (!empty($vendedor) && in_array($vendedor['perfil'] ?? '', ['SUPERVISOR','GERENTE','ADMIN'])): ?>
            <a href="/admin/supervisor" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
                Fila Supervisor
            </a>
            <?php endif; ?>

            <a href="/logout" class="sidebar-link sidebar-link-danger" style="margin-top:auto;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Sair
            </a>
        </nav>

        <?php if (!empty($vendedor)): ?>
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($vendedor['nome_completo'] ?? 'V', 0, 1)) ?></div>
            <div class="sidebar-user-info">
                <strong><?= h(explode(' ', $vendedor['nome_completo'] ?? 'Vendedor')[0]) ?></strong>
                <small><?= h($vendedor['perfil'] ?? 'Vendedor') ?></small>
            </div>
        </div>
        <?php endif; ?>

    </aside>

    <!-- ===================================================== MAIN -->
    <main class="dashboard-main">

        <!-- Topbar -->
        <header class="dashboard-topbar">
            <div>
                <h2 class="dashboard-title">Nova Proposta</h2>
                <p class="dashboard-subtitle">Preencha os dados do beneficiário, escolha o plano e envie os documentos.</p>
            </div>
            <a href="/admin/vendedor/propostas" class="btn btn-secondary btn-sm">
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                    <path d="M15 10H5M9 6l-4 4 4 4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Voltar
            </a>
        </header>

        <!-- ===================== STEPPER -->
        <div class="progress-steps" id="stepper" style="justify-content:flex-start;max-width:520px;margin-bottom:2rem;">
            <div class="progress-step active" id="progress-step-1">
                <div class="progress-step-num">1</div>
                <span class="progress-step-label">Beneficiário</span>
            </div>
            <div class="progress-connector" id="connector-1"></div>
            <div class="progress-step" id="progress-step-2">
                <div class="progress-step-num">2</div>
                <span class="progress-step-label">Plano</span>
            </div>
            <div class="progress-connector" id="connector-2"></div>
            <div class="progress-step" id="progress-step-3">
                <div class="progress-step-num">3</div>
                <span class="progress-step-label">Documentos</span>
            </div>
        </div>

        <!-- Errors -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom:1.5rem;">
            <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18" style="flex-shrink:0;margin-top:1px;">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                <strong>Corrija os erros abaixo:</strong>
                <ul style="margin:.4rem 0 0 1.25rem;">
                    <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- ===================== FORM -->
        <form method="POST" action="/admin/vendedor/nova-proposta"
              enctype="multipart/form-data" id="form-proposta" novalidate>

            <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
            <input type="hidden" name="idades_vidas" id="idades_vidas" value="<?= h($input['idades_vidas'] ?? '') ?>">

            <!-- =========================================== STEP 1: Beneficiário -->
            <div class="form-step" id="step-1">

                <!-- Card: Dados Pessoais -->
                <div class="dash-card" style="margin-bottom:1.25rem;">
                    <div class="dash-card-header">
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <div class="form-section-icon">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h3>Dados Pessoais</h3>
                                <p class="dashboard-subtitle" style="margin:0;font-size:var(--ds-text-xs);">Informações do beneficiário titular do plano</p>
                            </div>
                        </div>
                    </div>
                    <div class="step-card-body">

                        <div class="form-group">
                            <label class="form-label" for="nome_completo">
                                Nome Completo <span class="required">*</span>
                            </label>
                            <input type="text" id="nome_completo" name="nome_completo"
                                   class="form-input <?= isset($errors['nome_completo']) ? 'input-error' : '' ?>"
                                   value="<?= h($input['nome_completo'] ?? '') ?>"
                                   placeholder="Nome completo do titular" required>
                            <?php if (isset($errors['nome_completo'])): ?>
                                <span class="form-error-msg"><?= h($errors['nome_completo']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="cpf">CPF <span class="required">*</span></label>
                                <input type="text" id="cpf" name="cpf"
                                       class="form-input <?= isset($errors['cpf']) ? 'input-error' : '' ?>"
                                       value="<?= h($input['cpf'] ?? '') ?>"
                                       placeholder="000.000.000-00" maxlength="14" required>
                                <?php if (isset($errors['cpf'])): ?>
                                    <span class="form-error-msg"><?= h($errors['cpf']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="rg">RG</label>
                                <input type="text" id="rg" name="rg" class="form-input"
                                       value="<?= h($input['rg'] ?? '') ?>" placeholder="Número do RG">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="data_nascimento">
                                    Data de Nascimento <span class="required">*</span>
                                </label>
                                <input type="date" id="data_nascimento" name="data_nascimento"
                                       class="form-input <?= isset($errors['data_nascimento']) ? 'input-error' : '' ?>"
                                       value="<?= h($input['data_nascimento'] ?? '') ?>" required>
                                <?php if (isset($errors['data_nascimento'])): ?>
                                    <span class="form-error-msg"><?= h($errors['data_nascimento']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="sexo">Sexo</label>
                                <select id="sexo" name="sexo" class="form-input">
                                    <option value="">Selecione</option>
                                    <option value="M"     <?= ($input['sexo'] ?? '') === 'M'     ? 'selected' : '' ?>>Masculino</option>
                                    <option value="F"     <?= ($input['sexo'] ?? '') === 'F'     ? 'selected' : '' ?>>Feminino</option>
                                    <option value="OUTRO" <?= ($input['sexo'] ?? '') === 'OUTRO' ? 'selected' : '' ?>>Outro</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="telefone">
                                    Telefone <span class="required">*</span>
                                </label>
                                <input type="text" id="telefone" name="telefone"
                                       class="form-input <?= isset($errors['telefone']) ? 'input-error' : '' ?>"
                                       value="<?= h($input['telefone'] ?? '') ?>"
                                       placeholder="(21) 99999-9999" required>
                                <?php if (isset($errors['telefone'])): ?>
                                    <span class="form-error-msg"><?= h($errors['telefone']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">E-mail</label>
                                <input type="email" id="email" name="email" class="form-input"
                                       value="<?= h($input['email'] ?? '') ?>" placeholder="cliente@email.com">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="nome_mae">Nome da Mãe</label>
                                <input type="text" id="nome_mae" name="nome_mae" class="form-input"
                                       value="<?= h($input['nome_mae'] ?? '') ?>" placeholder="Nome completo da mãe">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="profissao">Profissão</label>
                                <input type="text" id="profissao" name="profissao" class="form-input"
                                       value="<?= h($input['profissao'] ?? '') ?>" placeholder="Ex: Autônomo, CLT...">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Card: Endereço -->
                <div class="dash-card" style="margin-bottom:1.25rem;">
                    <div class="dash-card-header">
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <div class="form-section-icon">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h3>Endereço</h3>
                                <p class="dashboard-subtitle" style="margin:0;font-size:var(--ds-text-xs);">Residência atual do beneficiário</p>
                            </div>
                        </div>
                    </div>
                    <div class="step-card-body">

                        <div class="form-row" style="grid-template-columns: 180px 1fr;">
                            <div class="form-group">
                                <label class="form-label" for="cep">CEP <span class="required">*</span></label>
                                <input type="text" id="cep" name="cep"
                                       class="form-input <?= isset($errors['cep']) ? 'input-error' : '' ?>"
                                       value="<?= h($input['cep'] ?? '') ?>"
                                       placeholder="00000-000" maxlength="9" required>
                                <?php if (isset($errors['cep'])): ?>
                                    <span class="form-error-msg"><?= h($errors['cep']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="bairro">Bairro</label>
                                <input type="text" id="bairro" name="bairro" class="form-input"
                                       value="<?= h($input['bairro'] ?? '') ?>" placeholder="Nome do bairro">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="endereco">
                                Logradouro <span class="required">*</span>
                            </label>
                            <input type="text" id="endereco" name="endereco"
                                   class="form-input <?= isset($errors['endereco']) ? 'input-error' : '' ?>"
                                   value="<?= h($input['endereco'] ?? '') ?>"
                                   placeholder="Rua, Avenida, Travessa..." required>
                            <?php if (isset($errors['endereco'])): ?>
                                <span class="form-error-msg"><?= h($errors['endereco']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-row" style="grid-template-columns: 100px 1fr 1fr 80px;">
                            <div class="form-group">
                                <label class="form-label" for="numero">Número</label>
                                <input type="text" id="numero" name="numero" class="form-input"
                                       value="<?= h($input['numero'] ?? '') ?>" placeholder="123">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="complemento">Complemento</label>
                                <input type="text" id="complemento" name="complemento" class="form-input"
                                       value="<?= h($input['complemento'] ?? '') ?>" placeholder="Apto 10, Bloco B...">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="cidade">Cidade <span class="required">*</span></label>
                                <input type="text" id="cidade" name="cidade"
                                       class="form-input <?= isset($errors['cidade']) ? 'input-error' : '' ?>"
                                       value="<?= h($input['cidade'] ?? '') ?>" required>
                                <?php if (isset($errors['cidade'])): ?>
                                    <span class="form-error-msg"><?= h($errors['cidade']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="uf">UF <span class="required">*</span></label>
                                <select id="uf" name="uf" class="form-input" required>
                                    <option value="">--</option>
                                    <?php foreach (['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf): ?>
                                    <option value="<?= $uf ?>" <?= ($input['uf'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="form-actions">
                    <span></span>
                    <button type="button" class="btn btn-primary" onclick="goToStep(2)">
                        Próximo: Escolher Plano
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
                            <path d="M5 10h10M12 6l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

            </div><!-- /step-1 -->

            <!-- =========================================== STEP 2: Plano -->
            <div class="form-step" id="step-2" style="display:none;">

                <!-- Plan Explorer Header -->
                <div class="plan-explorer-head">
                    <div style="position:relative;z-index:1;">
                        <span class="eyebrow">Central de comparação</span>
                        <h3 style="font-size:var(--ds-text-2xl);font-weight:800;color:#fff;letter-spacing:-.03em;margin-bottom:.35rem;line-height:1.2;">
                            Todos os planos disponíveis
                        </h3>
                        <p style="font-size:var(--ds-text-sm);color:rgba(255,255,255,.65);max-width:420px;line-height:1.5;">
                            Compare por valor, reputação e benefícios. Selecione o plano ideal antes de prosseguir.
                        </p>
                    </div>
                    <div id="plan-client-pill" class="plan-client-pill">
                        Informe a data de nascimento
                    </div>
                </div>

                <!-- Vidas panel -->
                <div class="plan-life-panel">
                    <div>
                        <strong style="display:block;font-size:var(--ds-text-sm);font-weight:700;color:var(--ds-navy-800);margin-bottom:.2rem;">
                            Vidas da cotação
                        </strong>
                        <span style="font-size:var(--ds-text-xs);color:var(--ds-slate-400);">
                            Titular usa a data do passo 1. Para dependentes, informe a idade abaixo.
                        </span>
                    </div>
                    <div id="dependentes-list" class="dependentes-list"></div>
                </div>

                <!-- Filter bar -->
                <div class="plan-filters">
                    <div class="form-group" style="flex:2;min-width:200px;margin:0;">
                        <label class="form-label">Buscar plano ou operadora</label>
                        <input type="search" id="plan-search" class="form-input"
                               placeholder="Ex: Amil, Unimed, Individual...">
                    </div>
                    <div class="form-group" style="flex:1;min-width:150px;margin:0;">
                        <label class="form-label">Coparticipação</label>
                        <select id="plan-category-filter" class="form-input">
                            <option value="">Todos</option>
                            <option value="SEM_COPARTICIPACAO">Sem copart.</option>
                            <option value="COM_COPARTICIPACAO">Com copart.</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;min-width:145px;margin:0;">
                        <label class="form-label">Ordenar por</label>
                        <select id="plan-sort" class="form-input">
                            <option value="ranking">Melhor ranking</option>
                            <option value="popular">Mais populares</option>
                            <option value="price_asc">Menor preço</option>
                            <option value="price_desc">Maior preço</option>
                            <option value="rating">Melhor avaliados</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;min-width:130px;margin:0;">
                        <label class="form-label">Valor máximo</label>
                        <input type="number" id="plan-max-price" class="form-input"
                               min="0" step="10" placeholder="R$ máximo">
                    </div>
                </div>

                <!-- Insights bar -->
                <div class="plan-insights" id="plan-insights"></div>

                <!-- Plan results -->
                <div class="plan-results" id="plan-results"></div>

                <!-- Empty state -->
                <div class="plan-empty" id="plan-empty" style="display:none;">
                    <svg viewBox="0 0 56 56" fill="none" width="52" height="52" style="opacity:.25;">
                        <circle cx="28" cy="28" r="26" stroke="currentColor" stroke-width="2"/>
                        <path d="M18 28h20M28 18v20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <p>Nenhum plano encontrado para os filtros aplicados.</p>
                    <button type="button" class="btn btn-ghost btn-sm"
                            onclick="document.getElementById('plan-search').value='';document.getElementById('plan-category-filter').value='';document.getElementById('plan-max-price').value='';renderPlanExplorer();">
                        Limpar filtros
                    </button>
                </div>

                <!-- Legacy / Manual selection fallback -->
                <div class="legacy-plan-section">
                    <h4>Seleção manual de plano</h4>
                    <p style="font-size:var(--ds-text-xs);color:var(--ds-slate-400);margin-bottom:1.25rem;">
                        Use o comparador acima ou selecione manualmente a operadora e o plano abaixo.
                    </p>

                    <div class="form-group">
                        <label class="form-label" for="operadora_id">Operadora <span class="required">*</span></label>
                        <select id="operadora_id" name="operadora_id"
                                class="form-input <?= isset($errors['operadora_id']) ? 'input-error' : '' ?>"
                                onchange="filtrarPlanos(this.value)">
                            <option value="">Selecione a operadora...</option>
                            <?php foreach ($operadoras as $op): ?>
                            <option value="<?= $op['id'] ?>" <?= ($input['operadora_id'] ?? '') == $op['id'] ? 'selected' : '' ?>>
                                <?= h($op['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['operadora_id'])): ?>
                            <span class="form-error-msg"><?= h($errors['operadora_id']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Categoria <span class="required">*</span></label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                            <label class="radio-card" id="cat-sem">
                                <input type="radio" name="categoria" value="SEM_COPARTICIPACAO"
                                       <?= empty($input['categoria']) || ($input['categoria'] ?? '') === 'SEM_COPARTICIPACAO' ? 'checked' : '' ?>
                                       onchange="onCategoriaChange()">
                                <div class="radio-card-body">
                                    <strong>Sem coparticipação</strong>
                                    <small>Sem custo adicional por consulta</small>
                                </div>
                            </label>
                            <label class="radio-card" id="cat-com">
                                <input type="radio" name="categoria" value="COM_COPARTICIPACAO"
                                       <?= ($input['categoria'] ?? '') === 'COM_COPARTICIPACAO' ? 'checked' : '' ?>
                                       onchange="onCategoriaChange()">
                                <div class="radio-card-body">
                                    <strong>Com coparticipação</strong>
                                    <small>Custo parcial por consulta</small>
                                </div>
                            </label>
                        </div>
                        <?php if (isset($errors['categoria'])): ?>
                            <span class="form-error-msg"><?= h($errors['categoria']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="plano_id">Plano <span class="required">*</span></label>
                        <select id="plano_id" name="plano_id"
                                class="form-input <?= isset($errors['plano_id']) ? 'input-error' : '' ?>">
                            <option value="">Selecione operadora e categoria primeiro...</option>
                        </select>
                        <?php if (isset($errors['plano_id'])): ?>
                            <span class="form-error-msg"><?= h($errors['plano_id']) ?></span>
                        <?php endif; ?>
                        <div id="plano-valor" style="margin-top:.5rem;font-size:.875rem;color:var(--ds-teal-600);font-weight:600;display:none;"></div>
                    </div>

                    <div class="form-row" style="align-items:flex-start;">
                        <div class="form-group" style="max-width:180px;">
                            <label class="form-label" for="quantidade_vidas">
                                Qtd. de Vidas <span class="required">*</span>
                            </label>
                            <input type="number" id="quantidade_vidas" name="quantidade_vidas" class="form-input"
                                   value="<?= h($input['quantidade_vidas'] ?? '1') ?>" min="1" max="99"
                                   oninput="renderDependentes(); renderPlanExplorer(); atualizarTotal();">
                        </div>
                        <div id="resumo-valor" class="plan-summary-box" style="display:none;flex:1;">
                            <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--ds-slate-500);margin-bottom:.25rem;">
                                Estimativa total mensal
                            </p>
                            <p id="valor-total-text" style="font-size:1.75rem;font-weight:800;color:var(--ds-navy-800);letter-spacing:-.04em;line-height:1;margin-bottom:.25rem;"></p>
                            <p style="font-size:11px;color:var(--ds-slate-400);">* Sujeito a ajuste pela operadora</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="observacoes">Observações</label>
                        <textarea id="observacoes" name="observacoes" rows="3" class="form-input"
                                  placeholder="Informações adicionais sobre o beneficiário ou a proposta..."><?= h($input['observacoes'] ?? '') ?></textarea>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(1)">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                            <path d="M15 10H5M9 6l-4 4 4 4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Voltar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(3)">
                        Próximo: Documentos
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                            <path d="M5 10h10M12 6l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

            </div><!-- /step-2 -->

            <!-- =========================================== STEP 3: Documentos -->
            <div class="form-step" id="step-3" style="display:none;">

                <div class="dash-card" style="margin-bottom:1.25rem;">
                    <div class="dash-card-header">
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <div class="form-section-icon">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h3>Documentos</h3>
                                <p class="dashboard-subtitle" style="margin:0;font-size:var(--ds-text-xs);">Envie os documentos do beneficiário titular</p>
                            </div>
                        </div>
                    </div>
                    <div class="step-card-body">

                        <div class="alert alert-info" style="margin-bottom:1.75rem;">
                            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16" style="flex-shrink:0;margin-top:1px;">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span>Formatos aceitos: <strong>PDF, JPG, PNG</strong> &nbsp;·&nbsp; Tamanho máximo: <strong>10 MB</strong> por arquivo</span>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                            <?php
                            $uploadFields = [
                                ['doc_frente',      'RG ou CNH — Frente',        true,  'Documento de identidade, frente obrigatória'],
                                ['doc_verso',       'RG ou CNH — Verso',         false, 'Verso do documento (opcional)'],
                                ['doc_residencia',  'Comprovante de Residência',  false, 'Conta de água, luz ou gás (opcional)'],
                                ['doc_contracheque','Contracheque / Holerite',    false, 'Comprovante de renda (opcional)'],
                            ];
                            foreach ($uploadFields as [$name, $label, $obrigatorio, $hint]):
                            ?>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="margin-bottom:.5rem;">
                                    <?= $label ?>
                                    <?php if ($obrigatorio): ?><span class="required">*</span><?php endif; ?>
                                </label>
                                <div class="upload-area <?= isset($errors[$name]) ? 'is-invalid' : '' ?>"
                                     onclick="document.getElementById('<?= $name ?>').click()"
                                     id="upload-area-<?= $name ?>">
                                    <svg viewBox="0 0 24 24" fill="none"
                                         stroke="<?= $obrigatorio ? 'var(--ds-teal-500)' : 'var(--ds-slate-300)' ?>"
                                         stroke-width="1.5" width="32" height="32">
                                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                        <polyline points="17 8 12 3 7 8"/>
                                        <line x1="12" y1="3" x2="12" y2="15"/>
                                    </svg>
                                    <p id="label-<?= $name ?>" class="upload-area-label">
                                        <?= $obrigatorio ? 'Clique para selecionar' : 'Clique para selecionar (opcional)' ?>
                                    </p>
                                    <span class="upload-area-hint"><?= $hint ?></span>
                                    <input type="file" id="<?= $name ?>" name="<?= $name ?>"
                                           accept=".pdf,.jpg,.jpeg,.png" style="display:none"
                                           onchange="updateLabel(this,'label-<?= $name ?>','upload-area-<?= $name ?>')">
                                </div>
                                <?php if (isset($errors[$name])): ?>
                                    <span class="form-error-msg"><?= h($errors[$name]) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="goToStep(2)">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                            <path d="M15 10H5M9 6l-4 4 4 4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Voltar
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg" id="btn-submit">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="17" height="17">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Enviar Proposta
                    </button>
                </div>

            </div><!-- /step-3 -->

        </form>

    </main>
</div>

<!-- ===================================================== MODAL: Benefícios -->
<div class="modal" id="plan-benefits-modal" role="dialog" aria-modal="true" aria-labelledby="plan-benefits-title">
    <div class="modal-content plan-benefits-modal">

        <div class="benefits-modal-head">
            <div class="benefits-modal-head-info">
                <span class="benefits-eyebrow">Benefícios do plano</span>
                <h3 id="plan-benefits-title" class="benefits-plan-name">Plano selecionado</h3>
                <p id="plan-benefits-subtitle" class="benefits-plan-meta"></p>
            </div>
            <div id="plan-benefits-price" class="benefits-price-badge">R$ 0,00</div>
            <button type="button" class="benefits-close" data-modal-close aria-label="Fechar">
                <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>

        <div class="benefits-body">
            <p class="benefits-section-label">O que está incluído</p>
            <ul id="plan-benefits-list" class="benefit-list"></ul>

            <div class="rede-widget">
                <div class="rede-widget-header">
                    <div class="rede-widget-icon">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <strong class="rede-widget-title">Verificar Rede Credenciada</strong>
                        <span class="rede-widget-sub">Consulte hospitais e clínicas próximos antes de contratar</span>
                    </div>
                </div>
                <div class="rede-widget-form">
                    <input type="text" id="rede-cep-input" class="form-input rede-cep-field"
                           placeholder="Digite seu CEP (ex: 23000-000)" maxlength="9"
                           oninput="this.value=CIB.maskCEP(this.value)"
                           aria-label="CEP para busca de rede credenciada">
                    <button type="button" class="btn btn-primary btn-sm"
                            onclick="buscarRedeCredenciada()" id="rede-cep-btn">
                        Buscar
                    </button>
                </div>
                <div id="rede-cep-result" class="rede-cep-result hidden" aria-live="polite"></div>
            </div>
        </div>

        <div class="benefits-footer">
            <button type="button" class="btn btn-outline btn-sm" data-modal-close>Fechar</button>
            <button type="button" class="btn btn-primary" id="benefits-select-btn">
                Selecionar este plano
            </button>
        </div>

    </div>
</div>

<script>
/* ────────────────────────────────────────────────────────────
   Dados PHP → JS
   ──────────────────────────────────────────────────────────── */
const planosData     = <?= json_encode($planosPorOperadora, JSON_UNESCAPED_UNICODE) ?>;
const operadorasData = <?= json_encode($operadorasPorId,   JSON_UNESCAPED_UNICODE) ?>;
let valorUnitario = 0;
let selectedPlanId = null;

/* ────────────────────────────────────────────────────────────
   Stepper — usa classes CSS em vez de inline styles
   ──────────────────────────────────────────────────────────── */
function goToStep(n) {
    document.querySelectorAll('.form-step').forEach((el, i) => {
        el.style.display = (i + 1 === n) ? '' : 'none';
    });
    document.querySelectorAll('.progress-step').forEach((el, i) => {
        el.classList.remove('active', 'done');
        if (i + 1 === n)      el.classList.add('active');
        else if (i + 1 < n)   el.classList.add('done');
    });
    document.querySelectorAll('.progress-connector').forEach((el, i) => {
        el.classList.toggle('done', i + 1 < n);
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ────────────────────────────────────────────────────────────
   Utilitários
   ──────────────────────────────────────────────────────────── */
function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[c]));
}

function allPlans() {
    return Object.values(planosData).flat().map((plan) => {
        const op = operadorasData[plan.operadora_id] || {};
        return { ...plan, operadora_nome: op.nome || 'Operadora', ...planMetrics(plan) };
    });
}

function planMetrics(plan) {
    const seed        = parseInt(plan.id, 10) || 1;
    const rating      = Math.min(5, 4.05 + ((seed * 7) % 10) / 10);
    const votos       = 86 + ((seed * 37) % 520);
    const popularidade= 58 + ((seed * 11) % 40);
    const score       = Math.round((rating * 16) + (popularidade * 0.18) + Math.min(votos, 500) * 0.035);
    return { rating, votos, popularidade, score };
}

function getVidaAges() {
    const titular = calcularIdadeTitular();
    const ages    = [titular].filter((a) => a > 0);
    document.querySelectorAll('.dependente-idade').forEach((el) => {
        const age = parseInt(el.value, 10);
        if (age >= 0 && age <= 120) ages.push(age);
    });
    document.getElementById('idades_vidas').value = ages.join(',');
    return ages.length ? ages : [0];
}

function valueForAge(plan, idade) {
    if (idade <= 18) return parseFloat(plan.faixa_0_18  || 0);
    if (idade <= 28) return parseFloat(plan.faixa_19_28 || 0);
    if (idade <= 43) return parseFloat(plan.faixa_29_43 || 0);
    if (idade <= 58) return parseFloat(plan.faixa_44_58 || 0);
    return parseFloat(plan.faixa_59_plus || 0);
}

function totalForPlan(plan) {
    return getVidaAges().reduce((s, age) => s + valueForAge(plan, age), 0);
}

function calcularIdadeTitular() {
    const value = document.getElementById('data_nascimento').value;
    if (!value) return 0;
    const birth = new Date(value + 'T00:00:00');
    const today = new Date();
    let age     = today.getFullYear() - birth.getFullYear();
    const md    = today.getMonth() - birth.getMonth();
    if (md < 0 || (md === 0 && today.getDate() < birth.getDate())) age--;
    return Math.max(0, age);
}

function faixaEtariaLabel(idade) {
    if (idade <= 18) return '0–18';
    if (idade <= 28) return '19–28';
    if (idade <= 43) return '29–43';
    if (idade <= 58) return '44–58';
    return '59+';
}

function valorPorFaixaSelecionada(option) {
    const idade = calcularIdadeTitular();
    if (idade <= 18) return parseFloat(option.dataset.faixa018  || 0);
    if (idade <= 28) return parseFloat(option.dataset.faixa1928 || 0);
    if (idade <= 43) return parseFloat(option.dataset.faixa2943 || 0);
    if (idade <= 58) return parseFloat(option.dataset.faixa4458 || 0);
    return parseFloat(option.dataset.faixa59 || 0);
}

function benefitsFor(plan) {
    const op = (operadorasData[plan.operadora_id]?.nome || 'Operadora').toLowerCase();
    const benefits = [
        'Consultas eletivas com rede credenciada',
        'Pronto atendimento conforme cobertura contratada',
        'Teleorientação para dúvidas de saúde',
        'Acompanhamento comercial até a implantação'
    ];
    if (plan.categoria === 'SEM_COPARTICIPACAO') benefits.unshift('Sem custo adicional por consulta');
    if (String(plan.cobertura || '').toLowerCase().includes('oeste')) benefits.push('Rede regional otimizada para Zona Oeste');
    if (op.includes('amil'))   benefits.push('Programa de medicina preventiva Amil');
    if (op.includes('unimed')) benefits.push('Rede cooperada com ampla capilaridade');
    if (op.includes('cemeru')) benefits.push('Atendimento regional com foco em agilidade');
    return benefits;
}

/* ────────────────────────────────────────────────────────────
   Dependentes
   ──────────────────────────────────────────────────────────── */
function renderDependentes() {
    const qtd    = Math.max(1, parseInt(document.getElementById('quantidade_vidas').value, 10) || 1);
    const holder = document.getElementById('dependentes-list');
    const prev   = Array.from(document.querySelectorAll('.dependente-idade')).map((el) => el.value);
    holder.innerHTML = '';

    const titularAge = calcularIdadeTitular();
    const chip = document.createElement('span');
    chip.className   = 'life-chip';
    chip.textContent = titularAge > 0
        ? `Titular · ${titularAge} anos (faixa ${faixaEtariaLabel(titularAge)})`
        : 'Titular · informe a data de nascimento';
    holder.appendChild(chip);

    for (let i = 1; i < qtd; i++) {
        const input = document.createElement('input');
        input.type        = 'number';
        input.min         = '0';
        input.max         = '120';
        input.className   = 'form-input dependente-idade';
        input.placeholder = `Dep. ${i}: idade`;
        input.value       = prev[i - 1] || '';
        input.style.cssText = 'width:120px;height:36px;font-size:13px;';
        input.addEventListener('input', () => { renderPlanExplorer(); atualizarTotal(); });
        holder.appendChild(input);
    }
    getVidaAges();
}

/* ────────────────────────────────────────────────────────────
   Plan Explorer — renderização premium
   ──────────────────────────────────────────────────────────── */
function renderPlanExplorer() {
    const search   = document.getElementById('plan-search')?.value.trim().toLowerCase() || '';
    const category = document.getElementById('plan-category-filter')?.value || '';
    const sort     = document.getElementById('plan-sort')?.value || 'ranking';
    const maxPrice = parseFloat(document.getElementById('plan-max-price')?.value || 0);
    const ages     = getVidaAges();
    const clientName  = document.getElementById('nome_completo').value.trim().split(' ')[0] || 'Cliente';
    const titularAge  = calcularIdadeTitular();
    const pill = document.getElementById('plan-client-pill');
    pill.textContent  = titularAge > 0
        ? `${clientName} · ${titularAge} anos · faixa ${faixaEtariaLabel(titularAge)}`
        : 'Informe a data de nascimento';

    let plans = allPlans().map((plan) => ({ ...plan, total: totalForPlan(plan) }));
    plans = plans.filter((plan) => {
        const hay = `${plan.nome} ${plan.operadora_nome} ${plan.cobertura || ''}`.toLowerCase();
        if (search   && !hay.includes(search))        return false;
        if (category && plan.categoria !== category)  return false;
        if (maxPrice > 0 && plan.total > maxPrice)    return false;
        return plan.total > 0;
    });

    plans.sort((a, b) => {
        if (sort === 'price_asc')  return a.total - b.total;
        if (sort === 'price_desc') return b.total - a.total;
        if (sort === 'popular')    return b.popularidade - a.popularidade;
        if (sort === 'rating')     return b.rating - a.rating;
        return b.score - a.score;
    });

    renderPlanInsights(plans, ages);

    const target = document.getElementById('plan-results');
    const empty  = document.getElementById('plan-empty');
    target.innerHTML = '';
    empty.style.display = plans.length ? 'none' : '';

    plans.forEach((plan, index) => {
        const isSelected  = String(selectedPlanId) === String(plan.id);
        const faixaResumo = ages.filter((a) => a > 0)
            .map((a) => `${a}a: R$ ${valueForAge(plan, a).toLocaleString('pt-BR', {minimumFractionDigits: 2})}`)
            .join(' &nbsp;·&nbsp; ');

        const card = document.createElement('article');
        card.className = `plan-card${isSelected ? ' selected' : ''}`;

        card.innerHTML = `
            <div class="plan-card-head">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.4rem;">
                    <span class="plan-card-operadora">${escapeHtml(plan.operadora_nome)}</span>
                    <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,.45);">#${index + 1}</span>
                </div>
                <h4 class="plan-card-name">${escapeHtml(plan.nome)}</h4>
                <div class="plan-card-price">
                    R$ ${plan.total.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    <span style="font-size:13px;font-weight:500;opacity:.65;">/mês</span>
                </div>
            </div>
            <div class="plan-card-body">
                <div class="plan-tags">
                    <span class="${plan.categoria === 'SEM_COPARTICIPACAO' ? 'tag-teal' : ''}">${plan.categoria === 'SEM_COPARTICIPACAO' ? 'Sem copart.' : 'Com copart.'}</span>
                    <span>${plan.popularidade}% popular</span>
                    <span>★ ${plan.rating.toFixed(1)}</span>
                </div>
                ${plan.cobertura ? `<p style="font-size:12px;color:var(--ds-slate-400);margin:0 0 .6rem;line-height:1.4;">${escapeHtml(plan.cobertura)}</p>` : ''}
                <p style="font-size:11px;color:var(--ds-slate-400);line-height:1.7;margin:0;">${faixaResumo || 'Informe as idades para ver preços por faixa'}</p>
            </div>
            <div class="plan-card-footer">
                <button type="button" class="btn btn-ghost btn-sm"
                        onclick="openPlanBenefits(${Number(plan.id)})">
                    Benefícios
                </button>
                <button type="button"
                        class="btn btn-sm ${isSelected ? 'btn-navy' : 'btn-primary'}"
                        onclick="selectPlan(${Number(plan.id)})">
                    ${isSelected ? '✓ Selecionado' : 'Selecionar'}
                </button>
            </div>
        `;
        target.appendChild(card);
    });
}

function renderPlanInsights(plans, ages) {
    const target = document.getElementById('plan-insights');
    if (!plans.length) { target.innerHTML = ''; return; }
    const cheapest = [...plans].sort((a, b) => a.total - b.total)[0];
    const popular  = [...plans].sort((a, b) => b.popularidade - a.popularidade)[0];
    const premium  = [...plans].sort((a, b) => b.total - a.total)[0];
    const vidas    = ages.filter((a) => a > 0).length || 1;
    target.innerHTML = `
        <span>${plans.length} plano${plans.length !== 1 ? 's' : ''} · ${vidas} vida${vidas !== 1 ? 's' : ''}</span>
        <span>Mais barato: ${escapeHtml(cheapest.operadora_nome)} · R$ ${cheapest.total.toLocaleString('pt-BR', {minimumFractionDigits:2})}</span>
        <span>Mais popular: ${escapeHtml(popular.operadora_nome)} (${popular.popularidade}%)</span>
        <span>Premium: ${escapeHtml(premium.operadora_nome)} · R$ ${premium.total.toLocaleString('pt-BR', {minimumFractionDigits:2})}</span>
    `;
}

/* ────────────────────────────────────────────────────────────
   Selecionar plano
   ──────────────────────────────────────────────────────────── */
function selectPlan(planId) {
    const plan = allPlans().find((p) => String(p.id) === String(planId));
    if (!plan) return;
    selectedPlanId = plan.id;
    document.getElementById('operadora_id').value = plan.operadora_id;
    const catEl = document.querySelector(`input[name="categoria"][value="${plan.categoria}"]`);
    if (catEl) catEl.checked = true;
    filtrarPlanos(plan.operadora_id);
    document.getElementById('plano_id').value = plan.id;
    valorUnitario = totalForPlan(plan);
    document.getElementById('plano_id').dispatchEvent(new Event('change'));
    renderPlanExplorer();
    atualizarTotal();
}

/* ────────────────────────────────────────────────────────────
   Modal de benefícios
   ──────────────────────────────────────────────────────────── */
function openPlanBenefits(planId) {
    const plan = allPlans().find((p) => String(p.id) === String(planId));
    if (!plan) return;
    const total = totalForPlan(plan);
    document.getElementById('plan-benefits-title').textContent    = plan.nome;
    document.getElementById('plan-benefits-subtitle').textContent = plan.operadora_nome;
    document.getElementById('plan-benefits-price').textContent    = `R$ ${total.toLocaleString('pt-BR', {minimumFractionDigits:2})} /mês`;
    document.getElementById('plan-benefits-list').innerHTML       = benefitsFor(plan)
        .map((b) => `<li><span class="benefit-check" aria-hidden="true"></span>${escapeHtml(b)}</li>`)
        .join('');
    const selectBtn = document.getElementById('benefits-select-btn');
    if (selectBtn) selectBtn.onclick = () => { CIB.closeAllModals(); selectPlan(plan.id); };
    const cepInput  = document.getElementById('rede-cep-input');
    const cepResult = document.getElementById('rede-cep-result');
    if (cepInput)  cepInput.value = '';
    if (cepResult) { cepResult.className = 'rede-cep-result hidden'; cepResult.innerHTML = ''; }
    document.getElementById('plan-benefits-modal').dataset.operadoraId   = plan.operadora_id;
    document.getElementById('plan-benefits-modal').dataset.operadoraNome = plan.operadora_nome;
    CIB.openModal('plan-benefits-modal');
}

/* ────────────────────────────────────────────────────────────
   Rede credenciada
   ──────────────────────────────────────────────────────────── */
const redeCredenciadaUrls = {
    'amil':        'https://www.amil.com.br/portal/rede-credenciada',
    'unimed':      'https://www.unimed.coop.br/rede-credenciada',
    'klini-saude': 'https://www.klinisaude.com.br/rede-credenciada',
    'cemeru':      'https://www.cemeru.com.br',
    'samoc':       'https://www.samoc.org.br',
    'oplan-saude': 'https://www.oplansaude.com.br',
    'abrabdir':    'https://www.abrabdir.com.br',
    'abracem':     'https://www.abracem.org.br',
    'aba':         'https://www.abaplanos.com.br',
};

async function buscarRedeCredenciada() {
    const input    = document.getElementById('rede-cep-input');
    const resultEl = document.getElementById('rede-cep-result');
    const btn      = document.getElementById('rede-cep-btn');
    const modal    = document.getElementById('plan-benefits-modal');
    const rawCep   = (input?.value || '').replace(/\D/g, '');

    if (rawCep.length !== 8) {
        resultEl.className = 'rede-cep-result error';
        resultEl.innerHTML = 'CEP inválido. Use o formato 00000-000.';
        resultEl.classList.remove('hidden');
        return;
    }
    if (btn) btn.disabled = true;
    resultEl.className = 'rede-cep-result loading';
    resultEl.innerHTML = '<span class="loading"></span> Buscando endereço...';
    resultEl.classList.remove('hidden');
    try {
        const res  = await fetch(`https://viacep.com.br/ws/${rawCep}/json/`);
        const data = await res.json();
        if (data.erro) throw new Error('CEP não encontrado');
        const operadoraNome = modal.dataset.operadoraNome || 'Operadora';
        const opSlug = Object.keys(redeCredenciadaUrls).find(
            (slug) => operadoraNome.toLowerCase().includes(slug.replace('-saude','').replace('-',''))
        ) || null;
        const urlBase = opSlug ? redeCredenciadaUrls[opSlug] : null;
        resultEl.className = 'rede-cep-result success';
        resultEl.innerHTML = `
            <div class="rede-address">
                <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                <span><strong>${data.localidade}/${data.uf}</strong> — ${data.bairro || ''} ${data.logradouro || ''}</span>
            </div>
            <div class="rede-links">
                ${urlBase ? `<a href="${urlBase}" target="_blank" rel="noopener" class="rede-link">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>
                    Ver rede credenciada — ${escapeHtml(operadoraNome)}
                </a>` : ''}
                <a href="https://www.ans.gov.br/rede-assistencial" target="_blank" rel="noopener" class="rede-link rede-link-muted">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    Consultar rede via ANS (todas as operadoras)
                </a>
            </div>
        `;
    } catch (err) {
        resultEl.className = 'rede-cep-result error';
        resultEl.innerHTML = 'CEP não encontrado. Verifique o número e tente novamente.';
    } finally {
        if (btn) btn.disabled = false;
    }
}

/* ────────────────────────────────────────────────────────────
   Filtros de plano (legacy select)
   ──────────────────────────────────────────────────────────── */
function filtrarPlanos(opId) {
    const cat = document.querySelector('input[name="categoria"]:checked')?.value || '';
    const sel = document.getElementById('plano_id');
    sel.innerHTML = '<option value="">Selecione um plano...</option>';
    document.getElementById('plano-valor').style.display  = 'none';
    document.getElementById('resumo-valor').style.display = 'none';
    valorUnitario = 0;
    if (!opId) return;
    (planosData[opId] || [])
        .filter((p) => !cat || p.categoria === cat)
        .forEach((p) => {
            const opt     = document.createElement('option');
            opt.value     = p.id;
            opt.dataset.faixa018  = p.faixa_0_18;
            opt.dataset.faixa1928 = p.faixa_19_28;
            opt.dataset.faixa2943 = p.faixa_29_43;
            opt.dataset.faixa4458 = p.faixa_44_58;
            opt.dataset.faixa59   = p.faixa_59_plus;
            const cob  = p.cobertura ? ` — ${p.cobertura}` : '';
            const base = parseFloat(p.faixa_0_18 || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2});
            opt.textContent = `${p.nome}${cob} — a partir de R$ ${base}`;
            sel.appendChild(opt);
        });
}

function onCategoriaChange() {
    filtrarPlanos(document.getElementById('operadora_id').value);
    renderPlanExplorer();
}

document.getElementById('plano_id').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    selectedPlanId = this.value || selectedPlanId;
    valorUnitario  = valorPorFaixaSelecionada(opt);
    const idade = calcularIdadeTitular();
    const faixa = faixaEtariaLabel(idade);
    const div   = document.getElementById('plano-valor');
    if (valorUnitario > 0) {
        div.textContent  = `Valor para titular (${idade} anos, faixa ${faixa}): R$ ${valorUnitario.toLocaleString('pt-BR', {minimumFractionDigits:2})}`;
        div.style.display = '';
    } else {
        div.style.display = 'none';
    }
    atualizarTotal();
});

document.getElementById('data_nascimento').addEventListener('change', function () {
    const plano = document.getElementById('plano_id');
    if (plano.value) plano.dispatchEvent(new Event('change'));
    renderDependentes();
    renderPlanExplorer();
});

document.getElementById('nome_completo').addEventListener('input', renderPlanExplorer);

['plan-search', 'plan-category-filter', 'plan-sort', 'plan-max-price'].forEach((id) => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('input',  renderPlanExplorer);
        el.addEventListener('change', renderPlanExplorer);
    }
});

function atualizarTotal() {
    const qtd      = parseInt(document.getElementById('quantidade_vidas').value) || 1;
    const selected = selectedPlanId ? allPlans().find((p) => String(p.id) === String(selectedPlanId)) : null;
    const total    = selected ? totalForPlan(selected) : valorUnitario * qtd;
    const div      = document.getElementById('resumo-valor');
    if (total > 0) {
        document.getElementById('valor-total-text').textContent =
            'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        div.style.display = '';
    } else {
        div.style.display = 'none';
    }
}

/* ────────────────────────────────────────────────────────────
   Upload — feedback visual
   ──────────────────────────────────────────────────────────── */
function updateLabel(input, labelId, areaId) {
    if (input.files[0]) {
        document.getElementById(labelId).textContent = input.files[0].name;
        const area = document.getElementById(areaId);
        if (area) area.classList.add('has-file');
    }
}

/* ────────────────────────────────────────────────────────────
   Máscaras
   ──────────────────────────────────────────────────────────── */
document.getElementById('cpf').addEventListener('input',     function () { this.value = CIB.maskCPF(this.value);   });
document.getElementById('telefone').addEventListener('input', function () { this.value = CIB.maskPhone(this.value); });
document.getElementById('cep').addEventListener('input',      function () { this.value = CIB.maskCEP(this.value);   });

/* CEP auto-fill */
document.getElementById('cep').addEventListener('blur', function () {
    const cep = this.value.replace(/\D/g, '');
    if (cep.length !== 8) return;
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then((r) => r.json())
        .then((d) => {
            if (!d.erro) {
                if (d.logradouro) document.getElementById('endereco').value = d.logradouro;
                if (d.bairro)     document.getElementById('bairro').value   = d.bairro;
                if (d.localidade) document.getElementById('cidade').value   = d.localidade;
                if (d.uf)         document.getElementById('uf').value       = d.uf;
            }
        })
        .catch(() => {});
});

/* ────────────────────────────────────────────────────────────
   Submit state
   ──────────────────────────────────────────────────────────── */
document.getElementById('form-proposta').addEventListener('submit', function () {
    const btn      = document.getElementById('btn-submit');
    btn.disabled   = true;
    btn.innerHTML  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Enviando...`;
});

/* ────────────────────────────────────────────────────────────
   Init — abre step correto se houve erros de validação
   ──────────────────────────────────────────────────────────── */
(function () {
    const errs = <?= json_encode(array_keys($errors ?? [])) ?>;
    const map  = {
        nome_completo:1, cpf:1, rg:1, data_nascimento:1, telefone:1, cep:1, endereco:1, cidade:1, uf:1,
        operadora_id:2, plano_id:2, categoria:2,
        doc_frente:3, doc_verso:3, doc_residencia:3, doc_contracheque:3
    };
    let target = 3;
    for (const f of errs) if (map[f] && map[f] < target) target = map[f];
    if (errs.length) goToStep(target);
})();

renderDependentes();
renderPlanExplorer();
</script>

<style>
/* Spin animation para o botão de submit */
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
