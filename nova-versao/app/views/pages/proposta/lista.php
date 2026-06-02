<?php
/**
 * views/pages/proposta/lista.php — Lista de propostas do vendedor
 */
$statusLabels = [
    'RASCUNHO'             => ['label'=>'Rascunho',            'class'=>'badge-secondary'],
    'PENDENTE_SUPERVISOR'  => ['label'=>'Pend. Supervisor',    'class'=>'badge-warning'],
    'APROVADO_SUPERVISOR'  => ['label'=>'Aprv. Supervisor',    'class'=>'badge-info'],
    'REPROVADO_SUPERVISOR' => ['label'=>'Reprovado',           'class'=>'badge-danger'],
    'PENDENTE_GERENTE'     => ['label'=>'Pend. Gerente',       'class'=>'badge-warning'],
    'APROVADO_GERENTE'     => ['label'=>'Aprv. Gerente',       'class'=>'badge-info'],
    'RECUSADO_GERENTE'     => ['label'=>'Recusado',            'class'=>'badge-danger'],
    'ENVIADO_OPERADORA'    => ['label'=>'Enviado Operadora',   'class'=>'badge-success'],
    'CONCLUIDO'            => ['label'=>'Concluído',           'class'=>'badge-success'],
    'CANCELADO'            => ['label'=>'Cancelado',           'class'=>'badge-secondary'],
];
?>
<div class="dashboard-wrapper">
    <aside class="dashboard-sidebar">
        <div class="sidebar-logo">
            <svg viewBox="0 0 40 40" fill="none" width="32" height="32"><rect width="40" height="40" rx="8" fill="#00C48C"/><path d="M20 8v24M8 20h24" stroke="white" stroke-width="3.5" stroke-linecap="round"/></svg>
            <span>CIB</span>
        </div>
        <nav class="sidebar-nav">
            <a href="/admin/vendedor" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Meu Painel
            </a>
            <a href="/admin/vendedor/nova-proposta" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M12 5v14M5 12h14"/></svg>
                Nova Proposta
            </a>
            <a href="/admin/vendedor/propostas" class="sidebar-link active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Minhas Propostas
            </a>
            <a href="/admin/vendedor/comissoes" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7H14a3.5 3.5 0 010 7H6"/></svg>
                Comissoes
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
                <h2 class="dashboard-title">Minhas Propostas</h2>
                <p class="dashboard-subtitle"><?= count($propostas) ?> proposta(s)</p>
            </div>
            <a href="/admin/vendedor/nova-proposta" class="btn btn-primary btn-sm">+ Nova Proposta</a>
        </header>

        <?php if ($flash): ?>
        <div class="alert" style="background:#f0fff4;border:1px solid #9ae6b4;color:#276749;margin-bottom:1.5rem;"><?= h($flash) ?></div>
        <?php endif; ?>

        <div class="dash-card">
            <?php if (empty($propostas)): ?>
            <div class="empty-state">
                <p style="margin-bottom:1rem;">Nenhuma proposta enviada ainda.</p>
                <a href="/admin/vendedor/nova-proposta" class="btn btn-primary btn-sm">Criar primeira proposta</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Protocolo</th>
                            <th>Cliente</th>
                            <th>Operadora / Plano</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($propostas as $p): ?>
                        <tr>
                            <td style="font-family:monospace;font-size:.8rem;font-weight:600;"><?= h($p['protocolo']) ?></td>
                            <td><?= h($p['cliente_nome']) ?></td>
                            <td>
                                <span style="font-size:.85rem;"><?= h($p['operadora_nome']??'-') ?></span>
                                <?php if ($p['plano_nome']): ?>
                                <br><span style="font-size:.75rem;color:var(--gray-400);"><?= h($p['plano_nome']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:600;color:var(--navy);">
                                <?= $p['valor_total'] ? 'R$ '.number_format($p['valor_total'],2,',','.') : '-' ?>
                            </td>
                            <td>
                                <?php $sl=$statusLabels[$p['status']]??['label'=>$p['status'],'class'=>'badge-secondary']; ?>
                                <span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span>
                            </td>
                            <td style="font-size:.8rem;color:var(--gray-400);"><?= data_br($p['criado_em']) ?></td>
                            <td><a href="/proposta/<?= $p['id'] ?>" style="font-size:.8rem;color:var(--green);font-weight:600;">Ver →</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
