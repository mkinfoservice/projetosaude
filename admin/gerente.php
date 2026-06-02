<?php
/**
 * admin/gerente.php
 * Painel do Gerente - Valida propostas da sua equipe/região
 */
require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/../api/auth.php';
require_once __DIR__ . '/../api/proposta.php';
require_once __DIR__ . '/../api/vendedores.php';

$pdo = getDBConnection();
$auth = new Auth($pdo);
$auth->requireLogin(['GERENTE']);

$proposta = new Proposta($pdo);
$vendedor = new Vendedor($pdo);
$userId = $auth->getUserId();
$csrfToken = $auth->generateCsrfToken();

// Dados do gerente para filtro por empresa
$gerenteData = $vendedor->getById($userId);
$empresaId = $gerenteData['empresa_id'] ?? null;

$filters = array_intersect_key($_GET, array_flip(['status', 'search']));
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;

// Gerente vê propostas da sua empresa
$data = $proposta->list($filters, $userId, $page, $limit);
$propostas = $data['data'];
$pagination = $data['pagination'];

$stats = $proposta->getStats($userId);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Gerente | Concessionária Inteligente Bem</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
</head>
<body>
    <nav class="navbar">
        <a href="/" class="navbar-brand">🏥 Concessionária Inteligente Bem</a>
        <div class="nav-links">
            <a href="/admin/gerente.php" class="active">📊 Propostas</a>
            <a href="#" data-action="logout">🚪 Sair</a>
        </div>
    </nav>

    <main class="section">
        <div class="dashboard-header">
            <h2>📊 Painel Gerente de Negócios</h2>
            <div class="dashboard-stats">
                <div class="stat-card"><div class="value"><?= $stats['total'] ?></div><div class="label">Minha Equipe</div></div>
                <div class="stat-card"><div class="value"><?= $stats['enviadas'] ?></div><div class="label">Enviadas</div></div>
                <div class="stat-card"><div class="value"><?= $stats['aprovadas'] ?></div><div class="label">Validadas</div></div>
                <div class="stat-card"><div class="value"><?= $stats['em_analise'] ?></div><div class="label">Aguardando</div></div>
            </div>
        </div>

        <div class="table-container mt-3">
            <table>
                <thead>
                    <tr>
                        <th>Protocolo</th>
                        <th>Cliente</th>
                        <th>Operadora</th>
                        <th>Status</th>
                        <th>Decisão Gerente</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($propostas)): ?>
                        <tr><td colspan="6" class="text-center">Aguardando validações...</td></tr>
                    <?php else: ?>
                        <?php foreach ($propostas as $p): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($p['protocolo']) ?></strong></td>
                                <td><?= htmlspecialchars($p['titular_nome']) ?></td>
                                <td><?= htmlspecialchars($p['operadora_nome']) ?></td>
                                <td><span class="status-badge status-<?= strtolower($p['status']) ?>"><?= $p['status'] ?></span></td>
                                <td><?= htmlspecialchars($p['decisao_gerente'] ?? 'Pendente') ?></td>
                                <td>
                                    <?php if ($p['status'] === 'ENVIADA'): ?>
                                        <button class="btn btn-outline btn-sm open-validation-modal" 
                                                data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['titular_nome']) ?>">
                                            ✅ Validar
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

    <!-- Modal Validação Gerente -->
    <div id="modal-validacao" class="modal">
        <div class="modal-content">
            <button class="modal-close">&times;</button>
            <h3>✅ Validação do Gerente</h3>
            <p>Cliente: <strong id="modal-valid-name"></strong></p>
            <form id="form-validacao-gerente">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="action" value="update_proposta_status">
                <input type="hidden" name="id" id="validacao-proposta-id">
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="">Selecione...</option>
                        <option value="EM_ANALISE">📋 Encaminhar para Análise</option>
                        <option value="RECUSADA">❌ Recusada (Incompleta)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Observação</label>
                    <textarea name="decisao" class="form-control" rows="3" placeholder="O que precisa ser ajustado?"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">💾 Registrar Validação</button>
            </form>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.open-validation-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('modal-valid-name').textContent = btn.dataset.name;
                document.getElementById('validacao-proposta-id').value = btn.dataset.id;
                ModalManager.show('modal-validacao');
            });
        });

        document.getElementById('form-validacao-gerente').addEventListener('submit', async (e) => {
            e.preventDefault();
            Utils.showLoading('Validando proposta...');
            try {
                const formData = new FormData(e.target);
                const result = await Utils.apiRequest('', { body: formData });
                if (result.success) {
                    Utils.showAlert('Validação registrada!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else Utils.showAlert(result.error, 'error');
            } catch(err) { Utils.showAlert('Erro de conexão', 'error'); }
            finally { Utils.hideLoading(); }
        });
    });
    </script>
</body>
</html>
