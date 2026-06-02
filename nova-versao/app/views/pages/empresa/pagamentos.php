<?php
/**
 * views/pages/empresa/pagamentos.php
 * Historico de pagamentos da empresa e seus vendedores
 */

$statusLabels = [
    'PENDING'    => ['label'=>'Pendente',   'class'=>'badge-warning'],
    'PENDENTE'   => ['label'=>'Pendente',   'class'=>'badge-warning'],
    'CONFIRMED'  => ['label'=>'Confirmado', 'class'=>'badge-success'],
    'CONFIRMADO' => ['label'=>'Confirmado', 'class'=>'badge-success'],
    'RECEIVED'   => ['label'=>'Recebido',   'class'=>'badge-success'],
    'OVERDUE'    => ['label'=>'Vencido',    'class'=>'badge-danger'],
    'CANCELED'   => ['label'=>'Cancelado',  'class'=>'badge-secondary'],
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
            <a href="/admin/empresa/propostas" class="sidebar-link">Propostas</a>
            <a href="/admin/empresa/pagamentos" class="sidebar-link active">Pagamentos</a>
            <a href="/logout" class="sidebar-link sidebar-link-danger" style="margin-top:auto;">Sair</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div>
                <h2 class="dashboard-title">Historico de Pagamentos</h2>
                <p class="dashboard-subtitle"><?= h($empresa['razao_social'] ?? '') ?> · PIX empresa e vendedores</p>
            </div>
            <span class="badge badge-outline"><?= count($pagamentos) ?> registro(s)</span>
        </header>

        <div class="dash-card">
            <div class="dash-card-header">
                <h3>Pagamentos Registrados</h3>
            </div>

            <?php if (empty($pagamentos)): ?>
            <div class="empty-state">
                <p>Nenhum pagamento registrado ainda.</p>
                <p style="font-size:.85rem;margin-top:.35rem;">Novas cobrancas PIX aparecerao aqui quando forem gravadas na tabela de pagamentos.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Origem</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>ID Asaas</th>
                            <th>Criado em</th>
                            <th>Confirmado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagamentos as $p): ?>
                        <?php $sl = $statusLabels[$p['status']] ?? ['label'=>$p['status'], 'class'=>'badge-secondary']; ?>
                        <tr>
                            <td><span class="badge badge-outline"><?= h($p['entity_type']) ?> #<?= (int)$p['entity_id'] ?></span></td>
                            <td style="font-weight:700;color:var(--navy);">R$ <?= number_format((float)$p['valor'], 2, ',', '.') ?></td>
                            <td><span class="badge <?= $sl['class'] ?>"><?= h($sl['label']) ?></span></td>
                            <td style="font-family:monospace;font-size:.8rem;"><?= h($p['asaas_payment_id'] ?? '-') ?></td>
                            <td style="font-size:.8rem;color:var(--gray-400);"><?= data_br($p['criado_em'], true) ?></td>
                            <td style="font-size:.8rem;color:var(--gray-400);"><?= $p['confirmado_em'] ? data_br($p['confirmado_em'], true) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
