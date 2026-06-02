<?php
/**
 * views/pages/vendedor/lista.php
 * Lista de vendedores da empresa
 */

$statusLabels = [
    'PENDENTE_PAGAMENTO' => ['label' => 'Pend. Pagamento', 'class' => 'badge-warning'],
    'ATIVO'              => ['label' => 'Ativo',           'class' => 'badge-success'],
    'SUSPENSO'           => ['label' => 'Suspenso',        'class' => 'badge-danger'],
    'CANCELADO'          => ['label' => 'Cancelado',       'class' => 'badge-secondary'],
];

$perfilLabels = [
    'VENDEDOR'   => 'Vendedor',
    'SUPERVISOR' => 'Supervisor',
    'GERENTE'    => 'Gerente',
    'ADMIN'      => 'Admin',
];

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<div class="dashboard-wrapper">

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
            <a href="/admin/empresa/propostas" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Propostas
            </a>
            <a href="/admin/empresa/pagamentos" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                Pagamentos
            </a>
            <a href="/logout" class="sidebar-link sidebar-link-danger" style="margin-top:auto;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sair
            </a>
        </nav>
    </aside>

    <main class="dashboard-main">

        <header class="dashboard-topbar">
            <div>
                <h2 class="dashboard-title">Vendedores</h2>
                <p class="dashboard-subtitle"><?= h($empresa['razao_social'] ?? '') ?></p>
            </div>
            <a href="/admin/empresa/vendedores/novo" class="btn btn-primary btn-sm">
                + Novo Vendedor
            </a>
        </header>

        <?php if ($flash): ?>
        <div class="alert alert-success" style="margin-bottom:1.5rem;background:#f0fff4;border:1px solid #9ae6b4;color:#276749;">
            <?= h($flash) ?>
        </div>
        <?php endif; ?>

        <div class="dash-card">
            <div class="dash-card-header">
                <h3>Todos os Vendedores (<?= count($vendedores) ?>)</h3>
            </div>

            <?php if (empty($vendedores)): ?>
            <div class="empty-state">
                <p style="margin-bottom:1rem;">Nenhum vendedor cadastrado ainda.</p>
                <a href="/admin/empresa/vendedores/novo" class="btn btn-primary btn-sm">
                    Cadastrar primeiro vendedor
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th>Cadastro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendedores as $v): ?>
                        <tr>
                            <td style="font-weight:500;"><?= h($v['nome_completo']) ?></td>
                            <td style="font-family:monospace;font-size:.85rem;">
                                <?= h(substr($v['cpf'],0,3) . '.***.***-' . substr($v['cpf'],-2)) ?>
                            </td>
                            <td><?= h($v['telefone']) ?></td>
                            <td>
                                <span class="badge badge-outline">
                                    <?= h($perfilLabels[$v['perfil']] ?? $v['perfil']) ?>
                                </span>
                            </td>
                            <td>
                                <?php $sl = $statusLabels[$v['status']] ?? ['label'=>$v['status'],'class'=>'badge-secondary']; ?>
                                <span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span>
                            </td>
                            <td style="font-size:.8rem;color:var(--gray-400);"><?= data_br($v['criado_em']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </main>
</div>
