<?php
/**
 * views/pages/empresa/dashboard.php
 * Painel administrativo da empresa
 */

$statusLabels = [
    'PENDENTE_PAGAMENTO' => ['label' => 'Pend. Pagamento', 'class' => 'badge-warning'],
    'ATIVO'              => ['label' => 'Ativo',           'class' => 'badge-success'],
    'SUSPENSO'           => ['label' => 'Suspenso',        'class' => 'badge-danger'],
    'CANCELADO'          => ['label' => 'Cancelado',       'class' => 'badge-secondary'],
];

$propostaLabels = [
    'PENDENTE_SUPERVISOR' => ['label' => 'Pend. Supervisor', 'class' => 'badge-warning'],
    'APROVADA_SUPERVISOR' => ['label' => 'Aprv. Supervisor', 'class' => 'badge-info'],
    'FINALIZADA'          => ['label' => 'Finalizada',       'class' => 'badge-success'],
    'REPROVADA'           => ['label' => 'Reprovada',        'class' => 'badge-danger'],
];
?>
<div class="dashboard-wrapper">

    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-logo">
            <svg viewBox="0 0 40 40" fill="none" width="32" height="32">
                <rect width="40" height="40" rx="8" fill="#00C48C"/>
                <path d="M20 8v24M8 20h24" stroke="white" stroke-width="3.5" stroke-linecap="round"/>
            </svg>
            <span>CIB</span>
        </div>
        <nav class="sidebar-nav">
            <a href="/admin/empresa" class="sidebar-link active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="/admin/empresa/vendedores" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                Vendedores
            </a>
            <a href="/admin/empresa/propostas" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Propostas
            </a>
            <a href="/admin/empresa/pagamentos" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M6 15h4"/></svg>
                Pagamentos
            </a>
            <a href="/logout" class="sidebar-link sidebar-link-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sair
            </a>
        </nav>

        <!-- Empresa logada -->
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($empresa['razao_social'] ?? 'E', 0, 1)) ?></div>
            <div class="sidebar-user-info">
                <strong><?= h(explode(' ', $empresa['razao_social'] ?? 'Empresa')[0]) ?></strong>
                <small>Empresa</small>
            </div>
        </div>

    </aside>

    <!-- Conteúdo principal -->
    <main class="dashboard-main">

        <!-- Top bar -->
        <header class="dashboard-topbar">
            <div>
                <h2 class="dashboard-title">Dashboard</h2>
                <p class="dashboard-subtitle"><?= h($empresa['razao_social'] ?? '') ?></p>
            </div>
            <div style="display:flex;align-items:center;gap:.75rem;">
                <span class="badge <?= $statusLabels[$empresa['status']]['class'] ?? 'badge-secondary' ?>">
                    <?= $statusLabels[$empresa['status']]['label'] ?? $empresa['status'] ?>
                </span>
                <a href="/admin/empresa/vendedores/novo" class="btn btn-primary btn-sm">
                    + Novo Vendedor
                </a>
            </div>
        </header>

        <!-- Cards de resumo -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <div>
                    <p class="stat-label">Vendedores Ativos</p>
                    <p class="stat-value"><?= number_format($totalVendedores) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <p class="stat-label">Total de Propostas</p>
                    <p class="stat-value"><?= number_format($totalPropostas) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-navy">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <p class="stat-label">Membro desde</p>
                    <p class="stat-value" style="font-size:1.1rem;"><?= data_br($empresa['criado_em'] ?? '') ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                </div>
                <div>
                    <p class="stat-label">Pagamentos</p>
                    <p class="stat-value"><?= number_format($totalPagamentos) ?></p>
                </div>
            </div>
        </div>

        <!-- Grid: Vendedores + Propostas -->
        <div class="dashboard-grid-2">

            <!-- Vendedores recentes -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3>Vendedores Recentes</h3>
                    <a href="/admin/empresa/vendedores" class="link-ver-todos">Ver todos →</a>
                </div>
                <?php if (empty($vendedores)): ?>
                <div class="empty-state">
                    <p>Nenhum vendedor cadastrado ainda.</p>
                    <a href="/admin/empresa/vendedores/novo" class="btn btn-outline btn-sm">Cadastrar Vendedor</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Perfil</th>
                                <th>Status</th>
                                <th>Cadastro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendedores as $v): ?>
                            <tr>
                                <td><?= h($v['nome_completo']) ?></td>
                                <td><span class="badge badge-outline"><?= h($v['perfil']) ?></span></td>
                                <td>
                                    <span class="badge <?= $statusLabels[$v['status']]['class'] ?? 'badge-secondary' ?>">
                                        <?= $statusLabels[$v['status']]['label'] ?? h($v['status']) ?>
                                    </span>
                                </td>
                                <td style="font-size:.8rem;color:var(--gray-400);"><?= data_br($v['criado_em']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Propostas recentes -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3>Propostas Recentes</h3>
                    <a href="/admin/empresa/propostas" class="link-ver-todos">Ver todas →</a>
                </div>
                <?php if (empty($propostas)): ?>
                <div class="empty-state">
                    <p>Nenhuma proposta enviada ainda.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Protocolo</th>
                                <th>Cliente</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($propostas as $p): ?>
                            <tr>
                                <td style="font-family:monospace;font-size:.8rem;"><?= h($p['protocolo']) ?></td>
                                <td><?= h($p['cliente']) ?></td>
                                <td>
                                    <?php $pl = $propostaLabels[$p['status']] ?? ['label' => h($p['status']), 'class' => 'badge-secondary']; ?>
                                    <span class="badge <?= $pl['class'] ?>"><?= $pl['label'] ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        </div>

    </main>
</div>
