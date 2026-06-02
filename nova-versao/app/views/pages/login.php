<div class="auth-page">

    <!-- ===== PAINEL ESQUERDO — Brand ===== -->
    <div class="auth-panel-left">
        <div class="auth-brand">

            <!-- Logo -->
            <a href="/" class="auth-brand-logo" aria-label="Ir para o início">
                <svg viewBox="0 0 44 44" fill="none" width="44" height="44">
                    <rect width="44" height="44" rx="12" fill="rgba(255,255,255,0.12)"/>
                    <rect width="44" height="44" rx="12" stroke="rgba(255,255,255,0.18)" stroke-width="1"/>
                    <path d="M22 32s-9.5-5.9-9.5-12.7A5.4 5.4 0 0122 15.2a5.4 5.4 0 019.5 4.1C31.5 26.1 22 32 22 32z" fill="white"/>
                </svg>
                <div class="auth-brand-name">
                    <strong>Bem</strong>
                    <small>Planos de Saúde</small>
                </div>
            </a>

            <!-- Headline -->
            <h1 class="auth-headline">
                Bem-vindo ao<br>
                <span class="highlight">Painel Inteligente</span>
            </h1>
            <p class="auth-subline">
                Gerencie propostas, acompanhe cotações e expanda seus resultados com a plataforma líder da Zona Oeste do Rio.
            </p>
            <div class="auth-visual-card" aria-hidden="true">
                <div class="auth-visual-top">
                    <span>Pipeline comercial</span>
                    <strong>R$ 127.5K</strong>
                </div>
                <div class="auth-visual-bars">
                    <span style="height:42%"></span>
                    <span style="height:58%"></span>
                    <span style="height:74%"></span>
                    <span style="height:66%"></span>
                    <span style="height:88%"></span>
                </div>
                <div class="auth-visual-row">
                    <span>Aprovadas</span>
                    <strong>68%</strong>
                </div>
            </div>
            <!-- Benefícios -->
            <ul class="auth-benefits">
                <li class="auth-benefit">
                    <span class="auth-benefit-check" aria-hidden="true">
                        <svg viewBox="0 0 16 16" fill="currentColor" width="12" height="12">
                            <path fill-rule="evenodd" d="M13.707 4.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L6 10.586l6.293-6.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    Cotação instantânea com 9+ operadoras
                </li>
                <li class="auth-benefit">
                    <span class="auth-benefit-check" aria-hidden="true">
                        <svg viewBox="0 0 16 16" fill="currentColor" width="12" height="12">
                            <path fill-rule="evenodd" d="M13.707 4.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L6 10.586l6.293-6.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    Gestão completa de propostas e clientes
                </li>
                <li class="auth-benefit">
                    <span class="auth-benefit-check" aria-hidden="true">
                        <svg viewBox="0 0 16 16" fill="currentColor" width="12" height="12">
                            <path fill-rule="evenodd" d="M13.707 4.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L6 10.586l6.293-6.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    Acompanhamento de comissões em tempo real
                </li>
                <li class="auth-benefit">
                    <span class="auth-benefit-check" aria-hidden="true">
                        <svg viewBox="0 0 16 16" fill="currentColor" width="12" height="12">
                            <path fill-rule="evenodd" d="M13.707 4.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-3-3a1 1 0 011.414-1.414L6 10.586l6.293-6.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    Dashboard com métricas e relatórios
                </li>
            </ul>

            <!-- Stats -->
            <div class="auth-stats">
                <div>
                    <span class="auth-stat-num">9+</span>
                    <span class="auth-stat-label">Operadoras</span>
                </div>
                <div>
                    <span class="auth-stat-num">500+</span>
                    <span class="auth-stat-label">Famílias atendidas</span>
                </div>
                <div>
                    <span class="auth-stat-num">5 min</span>
                    <span class="auth-stat-label">Para cotar</span>
                </div>
            </div>

        </div>
    </div>

    <!-- ===== PAINEL DIREITO — Formulário ===== -->
    <div class="auth-panel-right">
        <div class="auth-form-wrap">

            <div class="form-card">
                <div class="auth-form-header">
                    <div class="auth-form-badge">
                        <span></span>
                        Acesso seguro
                    </div>                    <h2 class="auth-form-title">Entrar no Sistema</h2>
                    <p class="auth-form-subtitle">Acesso para empresas, vendedores e gestores.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18" style="flex-shrink:0;">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <?= h($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/login" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">

                    <!-- Tipo de acesso -->
                    <div class="form-group">
                        <label class="form-label" for="tipo">
                            Tipo de Acesso <span class="required">*</span>
                        </label>
                        <select name="tipo" id="tipo" class="form-control" required
                                onchange="updateLoginForm(this.value)">
                            <option value="">Selecione o tipo de acesso...</option>
                            <option value="empresa">Empresa</option>
                            <option value="vendedor">Vendedor / Corretor</option>
                            <option value="vendedor" data-perfil="supervisor">Supervisor Operacional</option>
                            <option value="vendedor" data-perfil="gerente">Gerente de Negócios</option>
                        </select>
                    </div>

                    <!-- Identificador -->
                    <div class="form-group">
                        <label class="form-label" for="identifier" id="identifier-label">
                            E-mail / Telefone <span class="required">*</span>
                        </label>
                        <input type="text" name="identifier" id="identifier" class="form-control"
                               placeholder="E-mail ou telefone" required autocomplete="username">
                    </div>

                    <!-- Senha -->
                    <div class="form-group">
                        <label class="form-label" for="password">
                            Senha <span class="required">*</span>
                        </label>
                        <div class="input-password">
                            <input type="password" name="password" id="password" class="form-input"
                                   placeholder="Sua senha" required autocomplete="current-password">
                            <button type="button" class="btn-toggle-password" onclick="togglePassword()"
                                    aria-label="Mostrar/ocultar senha">
                                <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18" id="eye-icon">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-navy btn-full btn-lg" style="margin-top:0.75rem;">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                        </svg>
                        Entrar no Sistema
                    </button>
                </form>

                <!-- Links auxiliares -->
                <div style="margin-top:1.75rem; padding-top:1.5rem; border-top:1px solid var(--gray-100); text-align:center; display:flex; flex-direction:column; gap:0.6rem;">
                    <p class="auth-form-subtitle">
                        Vendedor sem cadastro?
                        <a href="https://wa.me/5521968827864?text=Quero%20me%20cadastrar%20como%20vendedor"
                           target="_blank" rel="noopener" style="color:var(--green); font-weight:700;">
                            Fale conosco →
                        </a>
                    </p>
                    <a href="/" style="font-size:0.82rem; color:var(--gray-400); transition:color 0.2s;"
                       onmouseover="this.style.color='var(--navy)'" onmouseout="this.style.color='var(--gray-400)'">
                        ← Voltar ao site
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
function updateLoginForm(tipo) {
    const label = document.getElementById('identifier-label');
    const input = document.getElementById('identifier');

    if (tipo === 'empresa') {
        label.innerHTML = 'E-mail da Empresa <span class="required">*</span>';
        input.type = 'email';
        input.placeholder = 'empresa@email.com';
        input.autocomplete = 'email';
    } else if (tipo === 'vendedor') {
        label.innerHTML = 'Telefone (com DDD) <span class="required">*</span>';
        input.type = 'tel';
        input.placeholder = '(21) 99999-9999';
        input.autocomplete = 'tel';
    } else {
        label.innerHTML = 'E-mail / Telefone <span class="required">*</span>';
        input.type = 'text';
        input.placeholder = 'E-mail ou telefone';
    }
}

function togglePassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
