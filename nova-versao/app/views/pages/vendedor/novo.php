<?php
/**
 * views/pages/vendedor/novo.php
 * Formulário de cadastro de vendedor (acessado pela empresa)
 */
?>
<div class="dashboard-wrapper">

    <!-- Sidebar empresa -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-logo">
            <svg viewBox="0 0 40 40" fill="none" width="32" height="32">
                <rect width="40" height="40" rx="8" fill="#00C48C"/>
                <path d="M20 8v24M8 20h24" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
            </svg>
            <span>CIB</span>
        </div>
        <nav class="sidebar-nav">
            <a href="/admin/empresa" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="/admin/empresa/vendedores" class="sidebar-link active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Vendedores
            </a>
            <a href="/logout" class="sidebar-link sidebar-link-danger" style="margin-top:auto;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sair
            </a>
        </nav>
    </aside>

    <main class="dashboard-main">

        <!-- Topbar -->
        <header class="dashboard-topbar">
            <div>
                <h2 class="dashboard-title">Cadastrar Vendedor</h2>
                <p class="dashboard-subtitle"><?= h($empresa['razao_social'] ?? '') ?></p>
            </div>
            <a href="/admin/empresa/vendedores" class="btn btn-outline btn-sm">← Voltar</a>
        </header>

        <!-- Steps indicator -->
        <div class="steps-bar" style="display:flex;gap:.5rem;margin-bottom:2rem;">
            <?php foreach (['Dados Pessoais','Endereço','Acesso & Documentos'] as $i => $label): ?>
            <div class="step-item" id="step-indicator-<?= $i+1 ?>"
                 style="flex:1;text-align:center;padding:.5rem;border-radius:8px;font-size:.8rem;font-weight:600;
                        background:<?= $i===0 ? 'var(--green)' : 'var(--gray-100)' ?>;
                        color:<?= $i===0 ? '#fff' : 'var(--gray-400)' ?>;">
                <?= $i+1 ?>. <?= $label ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom:1.5rem;">
            <strong>Corrija os erros abaixo:</strong>
            <ul style="margin:.5rem 0 0 1.2rem;">
                <?php foreach ($errors as $e): ?>
                <li><?= h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="auth-card" style="max-width:720px;">
            <form method="POST" action="/admin/empresa/vendedores/novo"
                  enctype="multipart/form-data" id="form-vendedor" novalidate>

                <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">

                <!-- ====== STEP 1: Dados Pessoais ====== -->
                <div class="form-step" id="step-1">
                    <h3 class="step-title">Dados Pessoais</h3>

                    <div class="form-group">
                        <label class="form-label" for="nome_completo">Nome Completo <span class="required">*</span></label>
                        <input type="text" id="nome_completo" name="nome_completo"
                               class="form-input <?= isset($errors['nome_completo']) ? 'is-invalid' : '' ?>"
                               value="<?= h($input['nome_completo'] ?? '') ?>"
                               placeholder="Nome completo do vendedor" required>
                        <?php if (isset($errors['nome_completo'])): ?>
                        <span class="form-error"><?= h($errors['nome_completo']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="cpf">CPF <span class="required">*</span></label>
                            <input type="text" id="cpf" name="cpf"
                                   class="form-input <?= isset($errors['cpf']) ? 'is-invalid' : '' ?>"
                                   value="<?= h($input['cpf'] ?? '') ?>"
                                   placeholder="000.000.000-00" maxlength="14" required>
                            <?php if (isset($errors['cpf'])): ?>
                            <span class="form-error"><?= h($errors['cpf']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="rg">RG <span class="required">*</span></label>
                            <input type="text" id="rg" name="rg"
                                   class="form-input <?= isset($errors['rg']) ? 'is-invalid' : '' ?>"
                                   value="<?= h($input['rg'] ?? '') ?>"
                                   placeholder="0000000" required>
                            <?php if (isset($errors['rg'])): ?>
                            <span class="form-error"><?= h($errors['rg']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="telefone">Telefone / WhatsApp <span class="required">*</span>
                                <span class="form-hint">Usado como login</span>
                            </label>
                            <input type="text" id="telefone" name="telefone"
                                   class="form-input <?= isset($errors['telefone']) ? 'is-invalid' : '' ?>"
                                   value="<?= h($input['telefone'] ?? '') ?>"
                                   placeholder="(21) 99999-9999" required>
                            <?php if (isset($errors['telefone'])): ?>
                            <span class="form-error"><?= h($errors['telefone']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">E-mail
                                <span class="form-hint">Opcional</span>
                            </label>
                            <input type="email" id="email" name="email"
                                   class="form-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   value="<?= h($input['email'] ?? '') ?>"
                                   placeholder="vendedor@empresa.com">
                            <?php if (isset($errors['email'])): ?>
                            <span class="form-error"><?= h($errors['email']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="perfil">Perfil <span class="required">*</span></label>
                        <select id="perfil" name="perfil"
                                class="form-input <?= isset($errors['perfil']) ? 'is-invalid' : '' ?>" required>
                            <option value="">Selecione...</option>
                            <option value="VENDEDOR"   <?= ($input['perfil'] ?? '') === 'VENDEDOR'   ? 'selected' : '' ?>>Vendedor</option>
                            <option value="SUPERVISOR" <?= ($input['perfil'] ?? '') === 'SUPERVISOR' ? 'selected' : '' ?>>Supervisor</option>
                            <option value="GERENTE"    <?= ($input['perfil'] ?? '') === 'GERENTE'    ? 'selected' : '' ?>>Gerente</option>
                        </select>
                        <?php if (isset($errors['perfil'])): ?>
                        <span class="form-error"><?= h($errors['perfil']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions" style="justify-content:flex-end;">
                        <button type="button" class="btn btn-primary" onclick="goToStep(2)">Próximo →</button>
                    </div>
                </div>

                <!-- ====== STEP 2: Endereço ====== -->
                <div class="form-step" id="step-2" style="display:none;">
                    <h3 class="step-title">Endereço</h3>

                    <div class="form-row">
                        <div class="form-group" style="flex:.4">
                            <label class="form-label" for="cep">CEP <span class="required">*</span></label>
                            <div style="position:relative">
                                <input type="text" id="cep" name="cep"
                                       class="form-input <?= isset($errors['cep']) ? 'is-invalid' : '' ?>"
                                       value="<?= h($input['cep'] ?? '') ?>"
                                       placeholder="00000-000" maxlength="9" required>
                                <span id="cep-loading" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);display:none;font-size:.8rem;color:var(--green)">Buscando...</span>
                            </div>
                            <?php if (isset($errors['cep'])): ?>
                            <span class="form-error"><?= h($errors['cep']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group" style="flex:.6">
                            <label class="form-label" for="bairro">Bairro</label>
                            <input type="text" id="bairro" name="bairro"
                                   class="form-input" value="<?= h($input['bairro'] ?? '') ?>" placeholder="Centro">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex:1">
                            <label class="form-label" for="endereco">Endereço <span class="required">*</span></label>
                            <input type="text" id="endereco" name="endereco"
                                   class="form-input <?= isset($errors['endereco']) ? 'is-invalid' : '' ?>"
                                   value="<?= h($input['endereco'] ?? '') ?>"
                                   placeholder="Rua, Avenida..." required>
                            <?php if (isset($errors['endereco'])): ?>
                            <span class="form-error"><?= h($errors['endereco']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex:.25">
                            <label class="form-label" for="numero">Número</label>
                            <input type="text" id="numero" name="numero"
                                   class="form-input" value="<?= h($input['numero'] ?? '') ?>" placeholder="123">
                        </div>
                        <div class="form-group" style="flex:.75">
                            <label class="form-label" for="cidade">Cidade <span class="required">*</span></label>
                            <input type="text" id="cidade" name="cidade"
                                   class="form-input <?= isset($errors['cidade']) ? 'is-invalid' : '' ?>"
                                   value="<?= h($input['cidade'] ?? '') ?>"
                                   placeholder="Rio de Janeiro" required>
                            <?php if (isset($errors['cidade'])): ?>
                            <span class="form-error"><?= h($errors['cidade']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group" style="max-width:120px;">
                        <label class="form-label" for="uf">UF <span class="required">*</span></label>
                        <select id="uf" name="uf"
                                class="form-input <?= isset($errors['uf']) ? 'is-invalid' : '' ?>" required>
                            <option value="">--</option>
                            <?php
                            $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG',
                                    'PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                            foreach ($ufs as $uf): ?>
                            <option value="<?= $uf ?>" <?= ($input['uf'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="goToStep(1)">← Voltar</button>
                        <button type="button" class="btn btn-primary" onclick="goToStep(3)">Próximo →</button>
                    </div>
                </div>

                <!-- ====== STEP 3: Acesso & Documentos ====== -->
                <div class="form-step" id="step-3" style="display:none;">
                    <h3 class="step-title">Acesso & Documentos</h3>

                    <div class="alert alert-info" style="margin-bottom:1.5rem;font-size:.875rem;">
                        O vendedor fará login com o <strong>telefone</strong> cadastrado e a senha definida abaixo.
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="senha">Senha Inicial <span class="required">*</span></label>
                            <div class="input-password">
                                <input type="password" id="senha" name="senha"
                                       class="form-input <?= isset($errors['senha']) ? 'is-invalid' : '' ?>"
                                       placeholder="Mínimo 8 caracteres" required>
                                <button type="button" class="btn-toggle-password" onclick="CIB.togglePassword('senha')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                            <?php if (isset($errors['senha'])): ?>
                            <span class="form-error"><?= h($errors['senha']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="senha_confirmacao">Confirmar Senha <span class="required">*</span></label>
                            <div class="input-password">
                                <input type="password" id="senha_confirmacao" name="senha_confirmacao"
                                       class="form-input <?= isset($errors['senha_confirmacao']) ? 'is-invalid' : '' ?>"
                                       placeholder="Repita a senha" required>
                                <button type="button" class="btn-toggle-password" onclick="CIB.togglePassword('senha_confirmacao')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                            <?php if (isset($errors['senha_confirmacao'])): ?>
                            <span class="form-error"><?= h($errors['senha_confirmacao']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Upload frente do documento -->
                    <div class="form-group">
                        <label class="form-label">
                            Documento de Identidade — Frente <span class="required">*</span>
                            <span class="form-hint">RG ou CNH — PDF, JPG, PNG, máx. 10MB</span>
                        </label>
                        <div class="upload-area <?= isset($errors['doc_frente']) ? 'is-invalid' : '' ?>"
                             onclick="document.getElementById('doc_frente').click()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32" style="color:var(--green);margin-bottom:.4rem">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <p id="label-frente">Clique para selecionar (frente)</p>
                            <input type="file" id="doc_frente" name="doc_frente"
                                   accept=".pdf,.jpg,.jpeg,.png" style="display:none"
                                   onchange="updateLabel(this,'label-frente')">
                        </div>
                        <?php if (isset($errors['doc_frente'])): ?>
                        <span class="form-error"><?= h($errors['doc_frente']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Upload verso do documento (opcional) -->
                    <div class="form-group">
                        <label class="form-label">
                            Documento de Identidade — Verso
                            <span class="form-hint">Opcional — PDF, JPG, PNG</span>
                        </label>
                        <div class="upload-area"
                             onclick="document.getElementById('doc_verso').click()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32" style="color:var(--gray-300);margin-bottom:.4rem">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <p id="label-verso" style="color:var(--gray-400)">Clique para selecionar (verso — opcional)</p>
                            <input type="file" id="doc_verso" name="doc_verso"
                                   accept=".pdf,.jpg,.jpeg,.png" style="display:none"
                                   onchange="updateLabel(this,'label-verso')">
                        </div>
                        <?php if (isset($errors['doc_verso'])): ?>
                        <span class="form-error"><?= h($errors['doc_verso']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="alert alert-info" style="font-size:.85rem;">
                        <strong>Mensalidade:</strong> R$ 57,00/mês — será gerado um PIX automaticamente.
                        O vendedor só poderá acessar o sistema após confirmação do pagamento.
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="goToStep(2)">← Voltar</button>
                        <button type="submit" class="btn btn-primary btn-lg" id="btn-submit">
                            Cadastrar Vendedor
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </main>
</div>

<script>
function goToStep(n) {
    document.querySelectorAll('.form-step').forEach((el, i) => {
        el.style.display = (i + 1 === n) ? '' : 'none';
    });
    document.querySelectorAll('[id^="step-indicator-"]').forEach((el, i) => {
        const active = (i + 1 === n);
        el.style.background = active ? 'var(--green)' : (i + 1 < n ? '#e8f8f2' : 'var(--gray-100)');
        el.style.color       = active ? '#fff' : (i + 1 < n ? 'var(--green)' : 'var(--gray-400)');
    });
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function updateLabel(input, labelId) {
    if (input.files && input.files[0]) {
        document.getElementById(labelId).textContent = input.files[0].name;
        input.closest('.upload-area').style.borderColor = 'var(--green)';
    }
}

// Abre step com erro
(function() {
    const errorFields = <?= json_encode(array_keys($errors ?? [])) ?>;
    const stepMap = {
        nome_completo:1, cpf:1, rg:1, telefone:1, email:1, perfil:1,
        cep:2, endereco:2, cidade:2, uf:2,
        senha:3, senha_confirmacao:3, doc_frente:3, doc_verso:3
    };
    let target = 3;
    for (const f of errorFields) {
        if (stepMap[f] && stepMap[f] < target) target = stepMap[f];
    }
    if (errorFields.length) goToStep(target);
})();

// Máscaras
document.getElementById('cpf').addEventListener('input', function() {
    this.value = CIB.maskCPF(this.value);
});
document.getElementById('telefone').addEventListener('input', function() {
    this.value = CIB.maskPhone(this.value);
});
document.getElementById('cep').addEventListener('input', function() {
    this.value = CIB.maskCEP(this.value);
});

// CEP auto-preenchimento
document.getElementById('cep').addEventListener('blur', function() {
    const cep = this.value.replace(/\D/g,'');
    if (cep.length !== 8) return;
    document.getElementById('cep-loading').style.display = 'inline';
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(r => r.json())
        .then(d => {
            if (!d.erro) {
                if (d.logradouro) document.getElementById('endereco').value = d.logradouro;
                if (d.bairro)     document.getElementById('bairro').value   = d.bairro;
                if (d.localidade) document.getElementById('cidade').value   = d.localidade;
                if (d.uf)         document.getElementById('uf').value       = d.uf;
            }
        })
        .catch(() => {})
        .finally(() => { document.getElementById('cep-loading').style.display = 'none'; });
});

document.getElementById('form-vendedor').addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.textContent = 'Salvando...';
});
</script>
