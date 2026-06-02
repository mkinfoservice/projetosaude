<?php
/**
 * views/pages/empresa/propostas.php
 * Propostas vinculadas aos vendedores da empresa
 */

$statusLabels = [
    'RASCUNHO'             => ['label'=>'Rascunho',          'class'=>'badge-secondary'],
    'PENDENTE_SUPERVISOR'  => ['label'=>'Pend. Supervisor',  'class'=>'badge-warning'],
    'APROVADO_SUPERVISOR'  => ['label'=>'Aprv. Supervisor',  'class'=>'badge-info'],
    'REPROVADO_SUPERVISOR' => ['label'=>'Reprovada',         'class'=>'badge-danger'],
    'PENDENTE_GERENTE'     => ['label'=>'Pend. Gerente',     'class'=>'badge-warning'],
    'APROVADO_GERENTE'     => ['label'=>'Aprv. Gerente',     'class'=>'badge-info'],
    'RECUSADO_GERENTE'     => ['label'=>'Recusada',          'class'=>'badge-danger'],
    'ENVIADO_OPERADORA'    => ['label'=>'Enviada',           'class'=>'badge-success'],
    'CONCLUIDO'            => ['label'=>'Concluida',         'class'=>'badge-success'],
    'CANCELADO'            => ['label'=>'Cancelada',         'class'=>'badge-secondary'],
];
?>
<div class="dashboard-wrapper">
    <aside class="dashboard-sidebar">
        <div class="sidebar-logo">
            <svg viewBox="0 0 40 40" fill="none" width="32" height="32"><rect width="40" height="40" rx="8" fill="#00C48C"/><path d="M20 8v24M8 20h24" stroke="white" stroke-width="3.5" stroke-linecap="round"/></svg>
            <span>CIB</span>
        </div>
        <nav class="sidebar-nav">
            <a href="/admin/empresa" class="sidebar-link">Dashboard</a>
            <a href="/admin/empresa/vendedores" class="sidebar-link">Vendedores</a>
            <a href="/admin/empresa/propostas" class="sidebar-link active">Propostas</a>
            <a href="/admin/empresa/pagamentos" class="sidebar-link">Pagamentos</a>
            <a href="/logout" class="sidebar-link sidebar-link-danger" style="margin-top:auto;">Sair</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div>
                <h2 class="dashboard-title">Propostas da Empresa</h2>
                <p class="dashboard-subtitle"><?= h($empresa['razao_social'] ?? '') ?> · <?= count($propostas) ?> proposta(s)</p>
            </div>
            <a href="/admin/empresa/vendedores/novo" class="btn btn-outline btn-sm">+ Novo Vendedor</a>
        </header>

        <div class="dash-card">
            <div class="dash-card-header">
                <h3>Fluxo de Propostas</h3>
            </div>

            <?php if (empty($propostas)): ?>
            <div class="empty-state">
                <p>Nenhuma proposta vinculada a esta empresa ainda.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Protocolo</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Operadora / Plano</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($propostas as $p): ?>
                        <?php $sl = $statusLabels[$p['status']] ?? ['label'=>$p['status'], 'class'=>'badge-secondary']; ?>
                        <tr>
                            <td style="font-family:monospace;font-size:.8rem;font-weight:600;"><?= h($p['protocolo']) ?></td>
                            <td><?= h($p['cliente_nome']) ?></td>
                            <td><?= h($p['vendedor_nome']) ?></td>
                            <td>
                                <span style="font-size:.85rem;"><?= h($p['operadora_nome'] ?? '-') ?></span>
                                <?php if (!empty($p['plano_nome'])): ?>
                                <br><span style="font-size:.75rem;color:var(--gray-400);"><?= h($p['plano_nome']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:700;color:var(--navy);">
                                <?= $p['valor_total'] ? 'R$ ' . number_format((float)$p['valor_total'], 2, ',', '.') : '-' ?>
                            </td>
                            <td><span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span></td>
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
