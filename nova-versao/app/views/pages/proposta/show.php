<?php
/**
 * views/pages/proposta/show.php — Detalhe da proposta
 */
$statusLabels = [
    'RASCUNHO'             => ['label'=>'Rascunho',            'class'=>'badge-secondary'],
    'PENDENTE_SUPERVISOR'  => ['label'=>'Aguardando Supervisor','class'=>'badge-warning'],
    'APROVADO_SUPERVISOR'  => ['label'=>'Aprovado Supervisor', 'class'=>'badge-info'],
    'REPROVADO_SUPERVISOR' => ['label'=>'Reprovado',           'class'=>'badge-danger'],
    'PENDENTE_GERENTE'     => ['label'=>'Aguardando Gerente',  'class'=>'badge-warning'],
    'APROVADO_GERENTE'     => ['label'=>'Aprovado Gerente',    'class'=>'badge-info'],
    'RECUSADO_GERENTE'     => ['label'=>'Recusado Gerente',    'class'=>'badge-danger'],
    'ENVIADO_OPERADORA'    => ['label'=>'Enviado à Operadora', 'class'=>'badge-success'],
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
            <a href="/admin/vendedor/propostas" class="sidebar-link active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Propostas
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
                <h2 class="dashboard-title"><?= $proposta ? h($proposta['protocolo']) : 'Proposta não encontrada' ?></h2>
                <?php if ($proposta): ?>
                <p class="dashboard-subtitle">Enviada em <?= data_br($proposta['criado_em'], true) ?></p>
                <?php endif; ?>
            </div>
            <a href="/admin/vendedor/propostas" class="btn btn-outline btn-sm">← Voltar</a>
        </header>

        <?php if (!$proposta): ?>
        <div class="dash-card" style="padding:3rem;text-align:center;">
            <p style="color:var(--gray-400);">Proposta não encontrada ou sem permissão de acesso.</p>
        </div>
        <?php else:
            $sl = $statusLabels[$proposta['status']] ?? ['label'=>$proposta['status'],'class'=>'badge-secondary'];
        ?>

        <!-- Status timeline -->
        <div class="dash-card" style="margin-bottom:1.5rem;padding:1.5rem;">
            <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                <span class="badge <?= $sl['class'] ?>" style="font-size:.9rem;padding:.35rem .9rem;"><?= $sl['label'] ?></span>
                <span style="color:var(--gray-400);font-size:.875rem;">
                    Valor: <strong style="color:var(--navy)">R$ <?= number_format($proposta['valor_total']??0,2,',','.') ?></strong>
                    &nbsp;·&nbsp; <?= (int)$proposta['quantidade_vidas'] ?> vida(s)
                    &nbsp;·&nbsp; <?= h($proposta['operadora_nome']??'-') ?>
                </span>
            </div>
        </div>

        <div class="dashboard-grid-2">
            <!-- Dados do beneficiário -->
            <div class="dash-card">
                <div class="dash-card-header"><h3>Beneficiário</h3></div>
                <div style="padding:1.25rem 1.5rem;">
                    <?php
                    $campos = [
                        'Nome'         => $proposta['cliente_nome'],
                        'CPF'          => $proposta['cliente_cpf'],
                        'Nascimento'   => $proposta['cliente_nascimento'] ? data_br($proposta['cliente_nascimento']) : '-',
                        'Telefone'     => $proposta['cliente_telefone'] ?? '-',
                        'E-mail'       => $proposta['cliente_email'] ?? '-',
                    ];
                    foreach ($campos as $label => $val): ?>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--gray-100);font-size:.875rem;">
                        <span style="color:var(--gray-500);"><?= $label ?></span>
                        <span style="color:var(--navy);font-weight:500;"><?= h($val) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Dados do plano -->
            <div class="dash-card">
                <div class="dash-card-header"><h3>Plano</h3></div>
                <div style="padding:1.25rem 1.5rem;">
                    <?php
                    $campos2 = [
                        'Operadora'    => $proposta['operadora_nome'] ?? '-',
                        'Plano'        => $proposta['plano_nome'] ?? '-',
                        'Categoria'    => $proposta['categoria'] === 'SEM_COPARTICIPACAO' ? 'Sem coparticipação' : 'Com coparticipação',
                        'Vidas'        => $proposta['quantidade_vidas'],
                        'Valor Total'  => 'R$ ' . number_format($proposta['valor_total']??0,2,',','.'),
                        'Vendedor'     => $proposta['vendedor_nome'] ?? '-',
                        'Empresa'      => $proposta['empresa_nome'] ?? '-',
                    ];
                    foreach ($campos2 as $label => $val): ?>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--gray-100);font-size:.875rem;">
                        <span style="color:var(--gray-500);"><?= $label ?></span>
                        <span style="color:var(--navy);font-weight:500;"><?= h((string)$val) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Decisões / histórico -->
            <?php if ($proposta['decisao_supervisor'] || $proposta['decisao_gerente'] || $proposta['operadora_destino']): ?>
            <div class="dash-card">
                <div class="dash-card-header"><h3>Histórico de Decisões</h3></div>
                <div style="padding:1.25rem 1.5rem;font-size:.875rem;">
                    <?php if ($proposta['decisao_supervisor']): ?>
                    <div style="margin-bottom:1rem;padding:.75rem;background:var(--gray-50);border-radius:var(--radius);border-left:3px solid var(--green);">
                        <p style="font-weight:600;color:var(--navy);margin-bottom:.25rem;">Supervisor</p>
                        <p style="color:var(--gray-600);"><?= h($proposta['decisao_supervisor']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($proposta['decisao_gerente']): ?>
                    <div style="padding:.75rem;background:var(--gray-50);border-radius:var(--radius);border-left:3px solid var(--navy);">
                        <p style="font-weight:600;color:var(--navy);margin-bottom:.25rem;">Gerente</p>
                        <p style="color:var(--gray-600);"><?= h($proposta['decisao_gerente']) ?></p>
                        <?php if ($proposta['operadora_destino']): ?>
                        <p style="margin-top:.5rem;font-weight:600;color:var(--green);">Enviado para: <?= h($proposta['operadora_destino']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Documentos -->
            <div class="dash-card">
                <div class="dash-card-header"><h3>Documentos Enviados</h3></div>
                <div style="padding:1.25rem 1.5rem;">
                    <?php
                    $docs = [
                        'Identidade (frente)'  => $proposta['doc_frente_url'],
                        'Identidade (verso)'   => $proposta['doc_verso_url'],
                        'Comprovante residência'=> $proposta['doc_residencia_url'],
                        'Contracheque'         => $proposta['doc_contracheque_url'],
                    ];
                    $temDoc = false;
                    foreach ($docs as $label => $url):
                        if (!$url) continue;
                        $temDoc = true;
                    ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid var(--gray-100);font-size:.875rem;">
                        <span style="color:var(--gray-600);"><?= $label ?></span>
                        <a href="<?= h($url) ?>" target="_blank" class="btn btn-outline btn-sm">Ver</a>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!$temDoc): ?>
                    <p style="color:var(--gray-400);font-size:.875rem;">Nenhum documento disponível.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>
