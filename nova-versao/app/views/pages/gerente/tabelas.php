<?php
/**
 * views/pages/gerente/tabelas.php
 * Gestao de tabelas de preco.
 */

$categoriaLabels = [
    'SEM_COPARTICIPACAO' => 'Sem coparticipacao',
    'COM_COPARTICIPACAO' => 'Com coparticipacao',
];

$formatLog = static function (?string $json): string {
    $dados = $json ? json_decode($json, true) : [];
    if (!is_array($dados) || empty($dados)) {
        return 'Sem detalhes adicionais.';
    }
    $partes = [];
    foreach ($dados as $chave => $valor) {
        if (is_array($valor)) {
            $valor = json_encode($valor, JSON_UNESCAPED_UNICODE);
        }
        $partes[] = $chave . ': ' . $valor;
    }
    return implode(' | ', $partes);
};
?>
<div class="dashboard-wrapper">
    <aside class="dashboard-sidebar">
        <div class="sidebar-logo">
            <svg viewBox="0 0 40 40" fill="none" width="32" height="32"><rect width="40" height="40" rx="8" fill="#00C48C"/><path d="M20 8v24M8 20h24" stroke="white" stroke-width="3.5" stroke-linecap="round"/></svg>
            <span>CIB</span>
        </div>
        <nav class="sidebar-nav">
            <a href="/admin/vendedor" class="sidebar-link">Meu Painel</a>
            <a href="/admin/supervisor" class="sidebar-link">Fila Supervisor</a>
            <a href="/admin/gerente" class="sidebar-link">Painel Gerente</a>
            <a href="/admin/gerente/tabelas" class="sidebar-link active">Tabelas de Preco</a>
            <a href="/logout" class="sidebar-link sidebar-link-danger" style="margin-top:auto;">Sair</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div>
                <h2 class="dashboard-title">Tabelas de Preco</h2>
                <p class="dashboard-subtitle">Importacao CSV, vigencia, versao e status dos planos</p>
            </div>
            <span class="badge badge-outline"><?= count($planos) ?> plano(s)</span>
        </header>

        <?php if ($flash): ?>
        <div class="alert alert-info" style="margin-bottom:1.5rem;">
            <?= h($flash) ?>
        </div>
        <?php endif; ?>

        <div class="dashboard-grid-2" style="margin-bottom:1.5rem;">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3>Importar CSV</h3>
                </div>
                <div style="padding:1.25rem 1.5rem;">
                    <form method="POST" action="/admin/gerente/tabelas" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
                        <input type="hidden" name="acao" value="importar_csv">

                        <div class="form-group">
                            <label class="form-label" for="arquivo_csv">Arquivo CSV</label>
                            <input type="file" id="arquivo_csv" name="arquivo_csv" class="form-input" accept=".csv,text/csv" required>
                            <p class="form-hint">Use ponto e virgula como separador.</p>
                        </div>

                        <button type="submit" class="btn btn-primary">Importar Tabela</button>
                    </form>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-header">
                    <h3>Formato Esperado</h3>
                </div>
                <div style="padding:1.25rem 1.5rem;">
                    <p style="font-size:.875rem;color:var(--gray-600);margin-bottom:.75rem;">
                        Cabecalho obrigatorio:
                    </p>
                    <pre class="csv-sample">operadora_slug;nome;categoria;cobertura;faixa_0_18;faixa_19_28;faixa_29_43;faixa_44_58;faixa_59_plus;vigencia_inicio;vigencia_fim;ativo</pre>
                    <p style="font-size:.8rem;color:var(--gray-500);margin-top:.75rem;">
                        Categorias: `SEM_COPARTICIPACAO` ou `COM_COPARTICIPACAO`. Datas: `YYYY-MM-DD` ou `DD/MM/YYYY`.
                    </p>
                </div>
            </div>
        </div>

        <div class="dash-card" style="margin-bottom:1.5rem;">
            <div class="dash-card-header">
                <h3>Operadoras nas Cotacoes</h3>
            </div>

            <?php if (empty($operadoras)): ?>
            <div class="empty-state">
                <p>Nenhuma operadora cadastrada.</p>
            </div>
            <?php else: ?>
            <div class="operator-control-grid">
                <?php foreach ($operadoras as $operadora): ?>
                <article class="operator-control-card <?= $operadora['ativo'] ? '' : 'is-paused' ?>">
                    <div>
                        <span class="badge <?= $operadora['ativo'] ? 'badge-success' : 'badge-secondary' ?>">
                            <?= $operadora['ativo'] ? 'Ativa' : 'Pausada' ?>
                        </span>
                        <h4><?= h($operadora['nome']) ?></h4>
                        <p><?= (int)$operadora['planos_ativos'] ?> de <?= (int)$operadora['total_planos'] ?> plano(s) ativos</p>
                    </div>
                    <form method="POST" action="/admin/gerente/tabelas">
                        <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
                        <input type="hidden" name="acao" value="toggle_operadora">
                        <input type="hidden" name="operadora_id" value="<?= (int)$operadora['id'] ?>">
                        <input type="hidden" name="ativo" value="<?= $operadora['ativo'] ? 0 : 1 ?>">
                        <button type="submit" class="btn btn-outline btn-sm">
                            <?= $operadora['ativo'] ? 'Pausar nas cotacoes' : 'Reativar cotacoes' ?>
                        </button>
                    </form>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="dash-card">
            <div class="dash-card-header">
                <h3>Planos Cadastrados</h3>
            </div>

            <?php if (empty($planos)): ?>
            <div class="empty-state">
                <p>Nenhum plano cadastrado.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Operadora</th>
                            <th>Plano</th>
                            <th>Categoria</th>
                            <th>0-18</th>
                            <th>19-28</th>
                            <th>29-43</th>
                            <th>44-58</th>
                            <th>59+</th>
                            <th>Vigencia</th>
                            <th>Versao</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($planos as $plano): ?>
                        <tr>
                            <td>
                                <strong><?= h($plano['operadora_nome']) ?></strong>
                                <br><span style="font-size:.75rem;color:var(--gray-400);"><?= h($plano['operadora_slug']) ?></span>
                            </td>
                            <td>
                                <?= h($plano['nome']) ?>
                                <?php if (!empty($plano['cobertura'])): ?>
                                <br><span style="font-size:.75rem;color:var(--gray-400);"><?= h($plano['cobertura']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-outline"><?= h($categoriaLabels[$plano['categoria']] ?? $plano['categoria']) ?></span></td>
                            <td>R$ <?= number_format((float)$plano['faixa_0_18'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format((float)$plano['faixa_19_28'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format((float)$plano['faixa_29_43'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format((float)$plano['faixa_44_58'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format((float)$plano['faixa_59_plus'], 2, ',', '.') ?></td>
                            <td style="font-size:.8rem;color:var(--gray-500);">
                                <?= $plano['vigencia_inicio'] ? data_br($plano['vigencia_inicio']) : '-' ?>
                                <br>
                                <?= $plano['vigencia_fim'] ? data_br($plano['vigencia_fim']) : 'sem fim' ?>
                            </td>
                            <td><span class="badge badge-info">v<?= (int)$plano['versao'] ?></span></td>
                            <td>
                                <span class="badge <?= $plano['ativo'] ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $plano['ativo'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="/admin/gerente/tabelas">
                                    <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">
                                    <input type="hidden" name="acao" value="toggle_plano">
                                    <input type="hidden" name="plano_id" value="<?= (int)$plano['id'] ?>">
                                    <input type="hidden" name="ativo" value="<?= $plano['ativo'] ? 0 : 1 ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">
                                        <?= $plano['ativo'] ? 'Desativar' : 'Ativar' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div class="dash-card" style="margin-top:1.5rem;">
            <div class="dash-card-header">
                <h3>Historico Operacional</h3>
            </div>

            <?php if (empty($historico)): ?>
            <div class="empty-state">
                <p>Nenhuma alteracao registrada ainda.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Acao</th>
                            <th>Nivel</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico as $log): ?>
                        <tr>
                            <td style="font-size:.8rem;color:var(--gray-500);"><?= data_br($log['criado_em'], true) ?></td>
                            <td><strong><?= h($log['acao']) ?></strong></td>
                            <td><span class="badge badge-outline"><?= h($log['nivel']) ?></span></td>
                            <td style="font-size:.82rem;color:var(--gray-500);"><?= h($formatLog($log['dados'] ?? null)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
