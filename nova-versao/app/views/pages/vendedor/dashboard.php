<?php
/**
 * views/pages/vendedor/dashboard.php
 * Painel do vendedor / supervisor / gerente
 */

$perfilLabel = [
    'VENDEDOR'   => 'Vendedor',
    'SUPERVISOR' => 'Supervisor',
    'GERENTE'    => 'Gerente',
    'ADMIN'      => 'Administrador',
][$vendedor['perfil']] ?? $vendedor['perfil'];

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
                <path d="M20 29s-9-5.6-9-12.1A5.1 5.1 0 0120 13a5.1 5.1 0 019 3.9C29 23.4 20 29 20 29z" fill="white"/>
            </svg>
            <span>CIB</span>
        </div>
        <nav class="sidebar-nav">
            <a href="/admin/vendedor" class="sidebar-link active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Meu Painel
            </a>
            <a href="/admin/vendedor/nova-proposta" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M12 5v14M5 12h14"/></svg>
                Nova Proposta
            </a>
            <a href="/admin/vendedor/propostas" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Minhas Propostas
            </a>
            <a href="/admin/vendedor/comissoes" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7H14a3.5 3.5 0 010 7H6"/></svg>
                Comissoes
            </a>
            <?php if (in_array($vendedor['perfil'], ['SUPERVISOR','GERENTE','ADMIN'])): ?>
            <a href="/admin/supervisor" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                Fila Supervisor
            </a>
            <?php endif; ?>
            <a href="/logout" class="sidebar-link sidebar-link-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sair
            </a>
        </nav>

        <!-- Usuário logado -->
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($vendedor['nome_completo'], 0, 1)) ?></div>
            <div class="sidebar-user-info">
                <strong><?= h(explode(' ', $vendedor['nome_completo'])[0]) ?></strong>
                <small><?= $perfilLabel ?></small>
            </div>
        </div>

    </aside>

    <!-- Conteúdo -->
    <main class="dashboard-main">

        <?php if ($flash): ?>
        <div class="alert" style="background:#f0fff4;border:1px solid #9ae6b4;color:#276749;margin-bottom:1.5rem;">
            <?= h($flash) ?>
        </div>
        <?php endif; ?>

        <!-- Topbar -->
        <header class="dashboard-topbar">
            <div>
                <h2 class="dashboard-title">Olá, <?= h(explode(' ', $vendedor['nome_completo'])[0]) ?>!</h2>
                <p class="dashboard-subtitle">
                    <span class="badge badge-outline"><?= $perfilLabel ?></span>
                    <?php if ($empresa): ?>
                    &nbsp;·&nbsp; <?= h($empresa['razao_social']) ?>
                    <?php endif; ?>
                </p>
            </div>
            <a href="/admin/vendedor/nova-proposta" class="btn btn-primary btn-sm">
                + Nova Proposta
            </a>
        </header>

        <!-- Stats -->
        <div class="stats-grid">
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
                    <p class="stat-value" style="font-size:1.1rem;"><?= data_br($vendedor['criado_em'] ?? '') ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
                </div>
                <div>
                    <p class="stat-label">Em Andamento</p>
                    <p class="stat-value"><?= number_format($propostasAtivas) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 000 7H14a3.5 3.5 0 010 7H6"/></svg>
                </div>
                <div>
                    <p class="stat-label">Comissao Estimada</p>
                    <p class="stat-value" style="font-size:1.25rem;">R$ <?= number_format($comissaoEstimada, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Acesso -->
        <div class="dash-card" style="margin-bottom:1.5rem;">
            <div class="dash-card-header">
                <h3>Seu ID de Acesso</h3>
            </div>
            <div style="padding:1.25rem 1.5rem;">
                <p style="font-size:.875rem;color:var(--gray-600);margin-bottom:.75rem;">
                    Compartilhe estas informações de acesso com o vendedor:
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div style="background:var(--gray-50);padding:.875rem 1rem;border-radius:var(--radius);border:1px solid var(--gray-100);">
                        <p style="font-size:.75rem;color:var(--gray-400);margin-bottom:.25rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Login (Telefone)</p>
                        <p style="font-family:monospace;font-size:1rem;color:var(--navy);font-weight:700;"><?= h($vendedor['telefone']) ?></p>
                    </div>
                    <div style="background:var(--gray-50);padding:.875rem 1rem;border-radius:var(--radius);border:1px solid var(--gray-100);">
                        <p style="font-size:.75rem;color:var(--gray-400);margin-bottom:.25rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">URL de Acesso</p>
                        <p style="font-family:monospace;font-size:.9rem;color:var(--green);font-weight:700;"><?= h(APP_URL) ?>/login</p>
                    </div>
                </div>
                <?php if ($vendedor['status'] === 'PENDENTE_PAGAMENTO'): ?>
                <div class="alert alert-warning" style="margin-top:1rem;">
                    Conta pendente de pagamento (R$ 57,00/mês). Realize o PIX para ativar o acesso.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Propostas recentes -->
        <div class="dash-card">
            <div class="dash-card-header">
                <h3>Últimas Propostas</h3>
                <a href="/admin/vendedor/propostas" class="link-ver-todos">Ver todas →</a>
            </div>

            <?php if (empty($ultimasPropostas)): ?>
            <div class="empty-state">
                <p style="margin-bottom:1rem;">Você ainda não enviou propostas.</p>
                <a href="/admin/vendedor/nova-proposta" class="btn btn-primary btn-sm">
                    Criar primeira proposta
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Protocolo</th>
                            <th>Cliente</th>
                            <th>Operadora</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimasPropostas as $p): ?>
                        <tr>
                            <td style="font-family:monospace;font-size:.8rem;"><?= h($p['protocolo']) ?></td>
                            <td><?= h($p['cliente']) ?></td>
                            <td><?= h($p['operadora']) ?></td>
                            <td>
                                <?php $pl = $propostaLabels[$p['status']] ?? ['label'=>h($p['status']),'class'=>'badge-secondary']; ?>
                                <span class="badge <?= $pl['class'] ?>"><?= $pl['label'] ?></span>
                            </td>
                            <td style="font-size:.8rem;color:var(--gray-400);"><?= data_br($p['criado_em']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </main>
</div>
