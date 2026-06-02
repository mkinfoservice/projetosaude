<?php
/**
 * views/pages/empresa/cadastro.php
 * Formulário de cadastro de empresa (multi-step)
 */
?>
<section class="auth-section">
<div class="container" style="max-width:720px; padding:3rem 1.5rem;">

    <div class="auth-card">
        <!-- Cabeçalho -->
        <div class="auth-header">
            <div class="auth-icon">
                <svg viewBox="0 0 40 40" fill="none" width="44" height="44">
                    <rect width="40" height="40" rx="10" fill="#00C48C"/>
                    <path d="M12 20h16M20 12v16" stroke="white" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>
            <h1 class="auth-title">Cadastro de Empresa</h1>
            <p class="auth-subtitle">Preencha os dados para cadastrar sua empresa na plataforma.</p>
        </div>

        <!-- Steps indicator -->
        <div class="steps-bar" style="display:flex;gap:.5rem;margin-bottom:2rem;">
            <?php foreach (['Dados da Empresa','Endereço','Acesso & Contrato'] as $i => $label): ?>
            <div class="step-item" id="step-indicator-<?= $i+1 ?>"
                 style="flex:1;text-align:center;padding:.5rem;border-radius:8px;font-size:.8rem;font-weight:600;
                        background:<?= $i===0 ? 'var(--green)' : 'var(--gray-100)' ?>;
                        color:<?= $i===0 ? '#fff' : 'var(--gray-400)' ?>;">
                <?= $i+1 ?>. <?= $label ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <strong>Corrija os erros abaixo:</strong>
            <ul style="margin:.5rem 0 0 1.2rem;">
                <?php foreach ($errors as $e): ?>
                <li><?= h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="/empresa/cadastro" enctype="multipart/form-data"
              id="form-empresa" novalidate>

            <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">

            <!-- ====== STEP 1: Dados da Empresa ====== -->
            <div class="form-step" id="step-1">
                <h3 class="step-title">Dados da Empresa</h3>

                <div class="form-group">
                    <label class="form-label" for="cnpj">CNPJ <span class="required">*</span></label>
                    <input type="text" id="cnpj" name="cnpj"
                           class="form-input <?= isset($errors['cnpj']) ? 'is-invalid' : '' ?>"
                           value="<?= h($input['cnpj'] ?? '') ?>"
                           placeholder="00.000.000/0001-00"
                           maxlength="18" required>
                    <?php if (isset($errors['cnpj'])): ?>
                    <span class="form-error"><?= h($errors['cnpj']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="razao_social">Razão Social <span class="required">*</span></label>
                    <input type="text" id="razao_social" name="razao_social"
                           class="form-input <?= isset($errors['razao_social']) ? 'is-invalid' : '' ?>"
                           value="<?= h($input['razao_social'] ?? '') ?>"
                           placeholder="Empresa Ltda" required>
                    <?php if (isset($errors['razao_social'])): ?>
                    <span class="form-error"><?= h($errors['razao_social']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="email">E-mail <span class="required">*</span></label>
                        <input type="email" id="email" name="email"
                               class="form-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               value="<?= h($input['email'] ?? '') ?>"
                               placeholder="contato@empresa.com.br" required>
                        <?php if (isset($errors['email'])): ?>
                        <span class="form-error"><?= h($errors['email']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="telefone">Telefone / WhatsApp <span class="required">*</span></label>
                        <input type="text" id="telefone" name="telefone"
                               class="form-input <?= isset($errors['telefone']) ? 'is-invalid' : '' ?>"
                               value="<?= h($input['telefone'] ?? '') ?>"
                               placeholder="(21) 99999-9999" required>
                        <?php if (isset($errors['telefone'])): ?>
                        <span class="form-error"><?= h($errors['telefone']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions" style="justify-content:flex-end;">
                    <button type="button" class="btn btn-primary" onclick="goToStep(2)">
                        Próximo →
                    </button>
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
                               class="form-input"
                               value="<?= h($input['bairro'] ?? '') ?>"
                               placeholder="Centro">
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
                    <div class="form-group" style="flex:.4">
                        <label class="form-label" for="complemento">Complemento</label>
                        <input type="text" id="complemento" name="complemento"
                               class="form-input" value="<?= h($input['complemento'] ?? '') ?>" placeholder="Sala 101">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex:.7">
                        <label class="form-label" for="cidade">Cidade <span class="required">*</span></label>
                        <input type="text" id="cidade" name="cidade"
                               class="form-input <?= isset($errors['cidade']) ? 'is-invalid' : '' ?>"
                               value="<?= h($input['cidade'] ?? '') ?>"
                               placeholder="Rio de Janeiro" required>
                        <?php if (isset($errors['cidade'])): ?>
                        <span class="form-error"><?= h($errors['cidade']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group" style="flex:.3">
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
                        <?php if (isset($errors['uf'])): ?>
                        <span class="form-error"><?= h($errors['uf']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="goToStep(1)">← Voltar</button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(3)">Próximo →</button>
                </div>
            </div>

            <!-- ====== STEP 3: Acesso & Contrato ====== -->
            <div class="form-step" id="step-3" style="display:none;">
                <h3 class="step-title">Acesso & Contrato Social</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="senha">Senha <span class="required">*</span></label>
                        <div class="input-password">
                            <input type="password" id="senha" name="senha"
                                   class="form-input <?= isset($errors['senha']) ? 'is-invalid' : '' ?>"
                                   placeholder="Mínimo 8 caracteres" required>
                            <button type="button" class="btn-toggle-password" onclick="CIB.togglePassword('senha')" aria-label="Ver senha">
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
                            <button type="button" class="btn-toggle-password" onclick="CIB.togglePassword('senha_confirmacao')" aria-label="Ver senha">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <?php if (isset($errors['senha_confirmacao'])): ?>
                        <span class="form-error"><?= h($errors['senha_confirmacao']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="contrato_social">
                        Contrato Social <span class="required">*</span>
                        <span class="form-hint">PDF, JPG ou PNG — máximo 10MB</span>
                    </label>
                    <div class="upload-area <?= isset($errors['contrato_social']) ? 'is-invalid' : '' ?>"
                         id="upload-area" onclick="document.getElementById('contrato_social').click()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" style="color:var(--green);margin-bottom:.5rem">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <p id="upload-label">Clique ou arraste o arquivo aqui</p>
                        <p style="font-size:.8rem;color:var(--gray-400)">PDF, JPG, PNG até 10MB</p>
                        <input type="file" id="contrato_social" name="contrato_social"
                               accept=".pdf,.jpg,.jpeg,.png" style="display:none"
                               onchange="updateUploadLabel(this)">
                    </div>
                    <?php if (isset($errors['contrato_social'])): ?>
                    <span class="form-error"><?= h($errors['contrato_social']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-check" style="margin:.5rem 0 1.5rem">
                    <input type="checkbox" id="termos" name="termos" required>
                    <label for="termos" style="font-size:.9rem;color:var(--gray-600)">
                        Li e aceito os <a href="#" style="color:var(--green)">Termos de Uso</a> e a
                        <a href="#" style="color:var(--green)">Política de Privacidade</a>.
                    </label>
                </div>

                <div class="alert alert-info" style="margin-bottom:1.5rem;">
                    <strong>Taxa de adesão:</strong> R$ 97,00 — será gerado um PIX após o envio.
                    A conta será ativada automaticamente após a confirmação do pagamento.
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="goToStep(2)">← Voltar</button>
                    <button type="submit" class="btn btn-primary btn-lg" id="btn-submit">
                        Finalizar Cadastro
                    </button>
                </div>
            </div>

        </form>

        <p style="text-align:center;margin-top:1.5rem;font-size:.9rem;color:var(--gray-500)">
            Já tem uma conta? <a href="/login" style="color:var(--green);font-weight:600">Entrar</a>
        </p>
    </div>
</div>
</section>

<script>
// Multi-step navigation
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

// Atualiza label do upload
function updateUploadLabel(input) {
    const label = document.getElementById('upload-label');
    if (input.files && input.files[0]) {
        label.textContent = input.files[0].name;
        document.getElementById('upload-area').style.borderColor = 'var(--green)';
    }
}

// Se teve erro no servidor, abre o step correto
(function() {
    const errorFields = <?= json_encode(array_keys($errors ?? [])) ?>;
    const stepMap = {
        cnpj:1, razao_social:1, email:1, telefone:1,
        cep:2, endereco:2, cidade:2, uf:2,
        senha:3, senha_confirmacao:3, contrato_social:3
    };
    let targetStep = 3;
    for (const f of errorFields) {
        if (stepMap[f] && stepMap[f] < targetStep) targetStep = stepMap[f];
    }
    if (errorFields.length) goToStep(targetStep);
})();

// Máscaras
document.getElementById('cnpj').addEventListener('input', function() {
    this.value = CIB.maskCNPJ(this.value);
});
document.getElementById('telefone').addEventListener('input', function() {
    this.value = CIB.maskPhone(this.value);
});
document.getElementById('cep').addEventListener('input', function() {
    this.value = CIB.maskCEP(this.value);
});

// Auto-preenchimento de CEP via ViaCEP
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

// Drag & drop no upload
const uploadArea = document.getElementById('upload-area');
['dragover','dragenter'].forEach(ev => {
    uploadArea.addEventListener(ev, e => { e.preventDefault(); uploadArea.style.borderColor = 'var(--green)'; });
});
uploadArea.addEventListener('dragleave', () => { uploadArea.style.borderColor = ''; });
uploadArea.addEventListener('drop', e => {
    e.preventDefault();
    const dt = e.dataTransfer;
    if (dt.files.length) {
        document.getElementById('contrato_social').files = dt.files;
        updateUploadLabel(document.getElementById('contrato_social'));
    }
});

// Previne duplo-clique no submit
document.getElementById('form-empresa').addEventListener('submit', function() {
    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.textContent = 'Enviando...';
});
</script>
