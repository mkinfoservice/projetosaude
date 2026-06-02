<?php
/**
 * views/pages/vendedor/comissoes.php
 * Extrato de comissoes estimadas do vendedor
 */
$percentualLabel = '10%';
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
            <a href="/admin/vendedor/propostas" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Propostas
            </a>
            <a href="/admin/vendedor/comissoes" class="sidebar-link active">
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
                <h2 class="dashboard-title">Minhas Comissoes</h2>
                <p class="dashboard-subtitle">Estimativa baseada em <?= $percentualLabel ?> sobre propostas enviadas e concluidas</p>
            </div>
            <span class="badge badge-outline"><?= count($propostasComissionadas) ?> proposta(s)</span>
        </header>

        <div class="stats-grid" style="margin-bottom:1.5rem;">
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7H14a3.5 3.5 0 010 7H6"/></svg>
                </div>
                <div>
                    <p class="stat-label">Comissao Estimada Total</p>
                    <p class="stat-value">R$ <?= number_format($comissaoEstimada, 2, ',', '.') ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <p class="stat-label">Propostas Comissionadas</p>
                    <p class="stat-value"><?= count($propostasComissionadas) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-navy">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                </div>
                <div>
                    <p class="stat-label">Taxa de Comissao</p>
                    <p class="stat-value"><?= $percentualLabel ?></p>
                </div>
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-header"><h3>Extrato de Comissoes</h3></div>

            <?php if (empty($propostasComissionadas)): ?>
            <div class="empty-state">
                <p>Nenhuma proposta elegivel para comissao ainda.</p>
                <p style="font-size:.85rem;margin-top:.35rem;">Propostas enviadas a operadoras e concluidas aparecerao aqui.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Protocolo</th>
                            <th>Cliente</th>
                            <th>Operadora / Plano</th>
                            <th>Vidas</th>
                            <th>Valor Proposta</th>
                            <th>Comissao (<?= $percentualLabel ?>)</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $statusLabels = [
                            'ENVIADO_OPERADORA' => ['label'=>'Enviada', 'class'=>'badge-success'],
                            'CONCLUIDO'         => ['label'=>'Concluida','class'=>'badge-success'],
                        ];
                        foreach ($propostasComissionadas as $p):
                            $comissao = (float)$p['valor_total'] * 0.10;
                            $sl = $statusLabels[$p['status']] ?? ['label'=>$p['status'],'class'=>'badge-secondary'];
                        ?>
                        <tr>
                            <td style="font-family:monospace;font-size:.8rem;font-weight:600;">
                                <a href="/proposta/<?= (int)$p['id'] ?>" style="color:var(--green);"><?= h($p['protocolo']) ?></a>
                            </td>
                            <td><?= h($p['cliente_nome']) ?></td>
                            <td>
                                <span style="font-size:.85rem;"><?= h($p['operadora_nome'] ?? '-') ?></span>
                                <?php if (!empty($p['plano_nome'])): ?>
                                <br><span style="font-size:.75rem;color:var(--gray-400);"><?= h($p['plano_nome']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$p['quantidade_vidas'] ?></td>
                            <td style="color:var(--navy);font-weight:600;">R$ <?= number_format((float)$p['valor_total'], 2, ',', '.') ?></td>
                            <td style="color:var(--green);font-weight:700;">R$ <?= number_format($comissao, 2, ',', '.') ?></td>
                            <td><span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span></td>
                            <td style="font-size:.8rem;color:var(--gray-400);"><?= data_br($p['atualizado_em']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:var(--gray-50);">
                            <td colspan="4" style="font-weight:700;color:var(--navy);font-size:.875rem;padding:.75rem 1rem;">Total</td>
                            <td style="font-weight:700;color:var(--navy);padding:.75rem 1rem;">
                                R$ <?= number_format(array_sum(array_column($propostasComissionadas, 'valor_total')), 2, ',', '.') ?>
                            </td>
                            <td style="font-weight:700;color:var(--green);padding:.75rem 1rem;">
                                R$ <?= number_format($comissaoEstimada, 2, ',', '.') ?>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div class="dash-card" style="margin-top:1.5rem;">
            <div style="padding:1.25rem 1.5rem;">
                <p style="font-size:.8rem;color:var(--gray-400);">
                    Os valores sao estimativas baseadas em <?= $percentualLabel ?> do valor total das propostas enviadas e concluidas com flag <em>gera_comissao</em> ativada.
                    A liquidacao efetiva das comissoes fica sujeita as regras contratuais da empresa.
                </p>
            </div>
        </div>
    </main>
</div>
