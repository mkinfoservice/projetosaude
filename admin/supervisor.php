<?php
/**
 * admin/supervisor.php
 * Painel do Supervisor Operacional - Visualiza e decide sobre TODAS as propostas
 */
require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/../api/auth.php';
require_once __DIR__ . '/../api/proposta.php';

$pdo = getDBConnection();
$auth = new Auth($pdo);
$auth->requireLogin(['SUPERVISOR']);

$proposta = new Proposta($pdo);
$userId = $auth->getUserId();
$csrfToken = $auth->generateCsrfToken();

// Filtros e paginação
$filters = array_intersect_key($_GET, array_flip(['status', 'search']));
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$data = $proposta->list($filters, null, $page, $limit);
$propostas = $data['data'];
$pagination = $data['pagination'];

$stats = $proposta->getStats();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Supervisor | Concessionária Inteligente Bem</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
</head>
<body>
    <nav class="navbar">
        <a href="/" class="navbar-brand">🏥 Concessionária Inteligente Bem</a>
        <div class="nav-links">
            <a href="/admin/supervisor.php" class="active">📊 Propostas</a>
            <a href="#" data-action="logout">🚪 Sair</a>
        </div>
    </nav>

    <main class="section">
        <div class="dashboard-header">
            <h2>👁️ Painel Supervisor Operacional</h2>
            <div class="dashboard-stats">
                <div class="stat-card"><div class="value"><?= $stats['total'] ?></div><div class="label">Total Propostas</div></div>
                <div class="stat-card"><div class="value"><?= $stats['aprovadas'] ?></div><div class="label">Aprovadas</div></div>
                <div class="stat-card"><div class="value"><?= $stats['em_analise'] ?></div><div class="label">Em Análise</div></div>
                <div class="stat-card"><div class="value"><?= Utils::formatCurrency($stats['valor_aprovado'] ?? 0) ?></div><div class="label">Valor Aprovado</div></div>
            </div>
        </div>

        <div class="table-container mt-3">
            <table>
                <thead>
                    <tr>
                        <th>Protocolo</th>
                        <th>Cliente</th>
                        <th>Operadora</th>
                        <th>Vendedor</th>
                        <th>Status</th>
                        <th>Decisão Supervisor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($propostas)): ?>
                        <tr><td colspan="7" class="text-center">Aguardando propostas...</td></tr>
                    <?php else: ?>
                        <?php foreach ($propostas as $p): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($p['protocolo']) ?></strong></td>
                                <td><?= htmlspecialchars($p['titular_nome']) ?></td>
                                <td><?= htmlspecialchars($p['operadora_nome']) ?></td>
                                <td><?= htmlspecialchars($p['vendedor_nome']) ?> <small class="text-gray">(<?= $p['vendedor_perfil'] ?>)</small></td>
                                <td><span class="status-badge status-<?= strtolower($p['status']) ?>"><?= $p['status'] ?></span></td>
                                <td><?= htmlspecialchars($p['decisao_supervisor'] ?? 'Pendente') ?></td>
                                <td>
                                    <?php if ($p['status'] === 'EM_ANALISE' || $p['status'] === 'ENVIADA'): ?>
                                        <button class="btn btn-outline btn-sm open-decision-modal" 
                                                data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['titular_nome']) ?>">
                                            📝 Decidir
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-secondary btn-sm view-proposta" data-id="<?= $p['id'] ?>">🔍 Ver</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="mt-3 text-center">
            <?php if ($pagination['pages'] > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                        <a href="?page=<?= $i ?>" class="btn <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>" style="padding:0.4rem 0.8rem;"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Decisão Supervisor -->
    <div id="modal-decisao" class="modal">
        <div class="modal-content">
            <button class="modal-close">&times;</button>
            <h3>📝 Decisão do Supervisor</h3>
            <p>Cliente: <strong id="modal-client-name"></strong></p>
            <form id="form-decisao-supervisor">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="update_proposta_status">
                <input type="hidden" name="id" id="decisao-proposta-id">
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="">Selecione...</option>
                        <option value="APROVADA">✅ Aprovada</option>
                        <option value="RECUSADA">❌ Recusada</option>
                        <option value="EM_ANALISE">⏳ Manter em Análise</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Observação / Decisão</label>
                    <textarea name="decisao" class="form-control" rows="3" placeholder="Justificativa da decisão..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">💾 Salvar Decisão</button>
            </form>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Modal de decisão
        document.querySelectorAll('.open-decision-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('modal-client-name').textContent = btn.dataset.name;
                document.getElementById('decisao-proposta-id').value = btn.dataset.id;
                ModalManager.show('modal-decisao');
            });
        });

        document.getElementById('form-decisao-supervisor').addEventListener('submit', async (e) => {
            e.preventDefault();
            Utils.showLoading('Processando decisão...');
            try {
                const formData = new FormData(e.target);
                const result = await Utils.apiRequest('', { body: formData });
                if (result.success) {
                    Utils.showAlert('Decisão registrada!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    Utils.showAlert(result.error || 'Erro ao registrar', 'error');
                }
            } catch(err) { Utils.showAlert('Erro de conexão', 'error'); }
            finally { Utils.hideLoading(); }
        });
    });
    </script>
</body>
</html>
