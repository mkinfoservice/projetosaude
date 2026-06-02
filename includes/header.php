<?php
/**
 * includes/header.php
 * Cabeçalho reutilizável com navegação, meta tags e verificação de sessão
 * ⚠️ Deve ser incluído APÓS definir $pageTitle e $currentPage
 */
if (!defined('SITE_LOADED')) {
    exit('Acesso direto não permitido.');
}

// Inicia sessão e gera CSRF se ainda não existir
if (session_status() === PHP_SESSION_NONE) session_start();
$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

$isLoggedIn = !empty($_SESSION['logged_in']);
$userType = $_SESSION['user_type'] ?? null;
$perfil = $_SESSION['user_perfil'] ?? null;
$userData = $_SESSION['user_data'] ?? [];

$pageTitle = $pageTitle ?? 'Concessionária Inteligente Bem';
$currentPage = $currentPage ?? 'home';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema Multi-Operadoras de Planos de Saúde - Concessionária Inteligente Bem">
    <meta name="theme-color" content="#0066cc">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
</head>
<body>
    <nav class="navbar">
        <a href="/" class="navbar-brand">🏥 Concessionária Inteligente Bem</a>
        <div class="nav-links">
            <a href="/" class="<?= $currentPage === 'home' ? 'active' : '' ?>">🏠 Início</a>
            
            <?php if ($isLoggedIn): ?>
                <?php if ($userType === 'EMPRESA'): ?>
                    <a href="/admin/empresa.php" class="<?= $currentPage === 'empresa' ? 'active' : '' ?>">🏢 Painel Empresa</a>
                <?php elseif ($userType === 'VENDEDOR'): ?>
                    <?php if ($perfil === 'SUPERVISOR'): ?>
                        <a href="/admin/supervisor.php" class="<?= $currentPage === 'supervisor' ? 'active' : '' ?>">👁️ Supervisor</a>
                    <?php elseif ($perfil === 'GERENTE'): ?>
                        <a href="/admin/gerente.php" class="<?= $currentPage === 'gerente' ? 'active' : '' ?>">📊 Gerente</a>
                    <?php else: ?>
                        <a href="/admin/vendedor.php" class="<?= $currentPage === 'vendedor' ? 'active' : '' ?>">🤝 Corretor</a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($userType === 'EMPRESA'): ?>
                    <a href="/index.php?section=cadastro-empresa" class="<?= $currentPage === 'empresas' ? 'active' : '' ?>">🏢 Cadastrar Empresa</a>
                <?php endif; ?>
                
                <a href="#" data-action="logout" class="btn-logout">🚪 Sair</a>
            <?php else: ?>
                <a href="#" data-modal="modal-login" class="<?= $currentPage === 'login' ? 'active' : '' ?>">🔐 Login</a>
                <a href="#section-simulacao" data-modal="modal-simulacao" class="<?= $currentPage === 'cotacao' ? 'active' : '' ?>">📊 Cotação</a>
                <a href="#section-atendimento" data-modal="modal-atendimento" class="<?= $currentPage === 'atendimento' ? 'active' : '' ?>">💬 Atendimento Auto</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="main-content">