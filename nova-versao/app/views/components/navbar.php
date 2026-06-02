<?php
$current = $current_page ?? 'home';
$logged  = $is_logged_in ?? false;
$utype   = $user_type ?? null;
$uperfil = $user_perfil ?? null;
?>
<header class="navbar" id="navbar">
    <div class="navbar-container">
        <!-- Logo -->
        <a href="/" class="navbar-brand" aria-label="Concessionária Inteligente Bem">
            <svg viewBox="0 0 40 40" fill="none" width="32" height="32">
                <rect width="40" height="40" rx="8" fill="#00C48C"/>
                <path d="M20 29s-9-5.6-9-12.1A5.1 5.1 0 0120 13a5.1 5.1 0 019 3.9C29 23.4 20 29 20 29z" fill="white"/>
            </svg>
            <span>Bem Planos</span>
        </a>

        <!-- Nav Links Desktop -->
        <nav aria-label="Navegação principal">
            <ul class="navbar-links">
                <li><a href="/" class="nav-link <?= $current === 'home' ? 'active' : '' ?>">Início</a></li>
                <li><a href="/#section-operadoras" class="nav-link">Operadoras</a></li>
                <li><a href="/#section-diferenciais" class="nav-link">Diferenciais</a></li>
                <li><a href="/#section-como-funciona" class="nav-link">Como Funciona</a></li>
                <li><a href="/#section-contato" class="nav-link">Contato</a></li>
            </ul>
        </nav>

        <!-- Actions Desktop -->
        <div class="navbar-actions desktop-only">
            <?php if ($logged): ?>
                <?php if ($utype === 'EMPRESA'): ?>
                    <a href="/admin/empresa" class="btn btn-secondary btn-sm">Painel Empresa</a>
                <?php elseif ($utype === 'VENDEDOR'): ?>
                    <?php if ($uperfil === 'SUPERVISOR'): ?>
                        <a href="/admin/supervisor" class="btn btn-secondary btn-sm">Supervisor</a>
                    <?php elseif ($uperfil === 'GERENTE'): ?>
                        <a href="/admin/gerente" class="btn btn-secondary btn-sm">Gerente</a>
                    <?php else: ?>
                        <a href="/admin/vendedor" class="btn btn-secondary btn-sm">Meu Painel</a>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="/logout" class="btn btn-ghost btn-sm">Sair</a>
            <?php else: ?>
                <a href="/login" class="btn btn-ghost btn-sm">Login</a>
                <button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">
                    Cotar Agora
                </button>
            <?php endif; ?>
        </div>

        <!-- Hamburger -->
        <button class="nav-toggle" aria-label="Abrir menu" aria-expanded="false" onclick="CIB.toggleMobileMenu()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div class="nav-mobile" id="nav-mobile" aria-hidden="true">
        <a href="/" class="nav-link">Início</a>
        <a href="/#section-operadoras" class="nav-link">Operadoras</a>
        <a href="/#section-diferenciais" class="nav-link">Diferenciais</a>
        <a href="/#section-como-funciona" class="nav-link">Como Funciona</a>
        <a href="/#section-contato" class="nav-link">Contato</a>
        <div class="navbar-actions">
            <?php if ($logged): ?>
                <a href="/admin/<?= strtolower($utype ?? 'vendedor') ?>" class="btn btn-secondary btn-sm">Meu Painel</a>
                <a href="/logout" class="btn btn-ghost btn-sm" style="color:#dc2626;">Sair</a>
            <?php else: ?>
                <a href="/login" class="btn btn-secondary btn-sm">Login</a>
                <button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar Agora</button>
            <?php endif; ?>
        </div>
    </div>
</header>
