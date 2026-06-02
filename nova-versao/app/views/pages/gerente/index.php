<?php
/**
 * views/pages/gerente/index.php — Painel do Gerente
 */
$statusLabels = [
    'APROVADO_GERENTE'  => ['label'=>'Aprovado',  'class'=>'badge-success'],
    'RECUSADO_GERENTE'  => ['label'=>'Recusado',  'class'=>'badge-danger'],
    'ENVIADO_OPERADORA' => ['label'=>'Enviado',   'class'=>'badge-success'],
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
            <a href="/admin/supervisor" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                Fila Supervisor
            </a>
            <a href="/admin/gerente" class="sidebar-link active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Painel Gerente
            </a>
            <a href="/admin/gerente/tabelas" class="sidebar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M3 3h18v18H3z"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                Tabelas de Preco
            </a>
            <a href="/logout" class="sidebar-link sidebar-link-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sair
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($vendedor['nome_completo'] ?? 'G', 0, 1)) ?></div>
            <div class="sidebar-user-info">
                <strong><?= h(explode(' ', $vendedor['nome_completo'] ?? 'Gerente')[0]) ?></strong>
                <small>Gerente</small>
            </div>
        </div>

    </aside>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div>
                <h2 class="dashboard-title">Painel do Gerente</h2>
                <p class="dashboard-subtitle"><?= count($pendentes) ?> proposta(s) aguardando finalização</p>
            </div>
        </header>

        <?php if ($flash): ?>
        <div class="alert" style="background:#f0fff4;border:1px solid #9ae6b4;color:#276749;margin-bottom:1.5rem;"><?= h($flash) ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
                </div>
                <div>
                    <p class="stat-label">Pendentes</p>
                    <p class="stat-value"><?= number_format($totalPendentes) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                </div>
                <div>
                    <p class="stat-label">Enviadas</p>
                    <p class="stat-value"><?= number_format($totalEnviadas) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-navy">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 000 7H14a3.5 3.5 0 010 7H6"/></svg>
                </div>
                <div>
                    <p class="stat-label">Pipeline</p>
                    <p class="stat-value" style="font-size:1.25rem;">R$ <?= number_format($valorPipeline, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Fila pendente gerente -->
        <?php if (empty($pendentes)): ?>
        <div class="dash-card" style="margin-bottom:1.5rem;">
            <div class="empty-state"><p>Nenhuma proposta aguardando finalização.</p></div>
        </div>
        <?php else: ?>
        <?php foreach ($pendentes as $p): ?>
        <div class="dash-card" style="margin-bottom:1rem;">
            <div style="padding:1.25rem 1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;">
                    <div>
                        <p style="font-family:monospace;font-weight:700;color:var(--navy);font-size:.95rem;"><?= h($p['protocolo']) ?></p>
                        <p style="font-size:.875rem;color:var(--gray-600);margin-top:.2rem;">
                            <strong><?= h($p['cliente_nome']) ?></strong>
                            &nbsp;·&nbsp; <?= h($p['operadora_nome']??'-') ?> / <?= h($p['plano_nome']??'-') ?>
                            &nbsp;·&nbsp; <strong style="color:var(--navy)">R$ <?= number_format($p['valor_total']??0,2,',','.') ?></strong>
                        </p>
                        <p style="font-size:.8rem;color:var(--gray-400);margin-top:.2rem;">
                            Vendedor: <?= h($p['vendedor_nome']) ?> — Empresa: <?= h($p['empresa_nome']??'-') ?>
                        </p>
                    </div>
                    <a href="/proposta/<?= $p['id'] ?>" target="_blank" class="btn btn-outline btn-sm">Ver Detalhes</a>
                </div>

                <form method="POST" action="/admin/gerente" style="display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap;">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
                    <input type="hidden" name="proposta_id" value="<?= $p['id'] ?>">

                    <div style="flex:1;min-width:220px;">
                        <label style="font-size:.8rem;font-weight:600;color:var(--gray-600);display:block;margin-bottom:.3rem;">
                            Concessionária de Destino <span class="required">*</span>
                        </label>
                        <select name="operadora_destino" class="form-input">
                            <option value="">Selecione a concessionária...</option>
                            <?php foreach ($operadoras as $op): ?>
                            <option value="<?= h($op['nome']) ?>"><?= h($op['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="flex:1;min-width:220px;">
                        <label style="font-size:.8rem;font-weight:600;color:var(--gray-600);display:block;margin-bottom:.3rem;">Observação Gerente</label>
                        <input type="text" name="decisao" class="form-input"
                               placeholder="Opcional para finalizar, obrigatório para recusar">
                    </div>

                    <button type="submit" name="acao" value="finalizar" class="btn btn-primary btn-sm"
                            onclick="return confirm('Finalizar e enviar à operadora?')">
                        Finalizar
                    </button>
                    <button type="submit" name="acao" value="recusar" class="btn btn-sm"
                            style="background:#fed7d7;color:#c53030;border:none;cursor:pointer;padding:.4rem .85rem;border-radius:var(--radius);font-size:.8rem;font-weight:600;"
                            onclick="return confirm('Recusar esta proposta?')">
                        Recusar
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Histórico -->
        <?php if (!empty($recentes)): ?>
        <div class="dash-card">
            <div class="dash-card-header"><h3>Histórico Recente</h3></div>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Protocolo</th><th>Cliente</th><th>Destino</th><th>Status</th><th>Data</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentes as $p):
                            $sl = $statusLabels[$p['status']] ?? ['label'=>$p['status'],'class'=>'badge-secondary'];
                        ?>
                        <tr>
                            <td style="font-family:monospace;font-size:.8rem;"><?= h($p['protocolo']) ?></td>
                            <td><?= h($p['cliente_nome']) ?></td>
                            <td style="font-size:.85rem;"><?= h($p['operadora_nome']??'-') ?></td>
                            <td><span class="badge <?= $sl['class'] ?>"><?= $sl['label'] ?></span></td>
                            <td style="font-size:.8rem;color:var(--gray-400);"><?= data_br($p['atualizado_em']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>
