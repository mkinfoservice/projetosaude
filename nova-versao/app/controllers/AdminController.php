<?php
/**
 * app/controllers/AdminController.php
 * Painéis de Supervisor e Gerente
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

require_once APP_PATH . '/models/PropostaModel.php';
require_once APP_PATH . '/models/VendedorModel.php';
require_once APP_PATH . '/services/PriceTableImportService.php';

class AdminController
{
    private PropostaModel  $model;
    private VendedorModel  $vendedorModel;

    public function __construct()
    {
        $this->model         = new PropostaModel();
        $this->vendedorModel = new VendedorModel();
        $this->initSession();
    }

    private function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => APP_ENV === 'production',
                'httponly'  => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }
    }

    // ------------------------------------------------------------------
    // Painel Supervisor
    // ------------------------------------------------------------------

    public function supervisor(): void
    {
        $this->requirePerfil(['SUPERVISOR','GERENTE','ADMIN']);

        // Processa ação POST (aprovar/reprovar)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarDecisaoSupervisor();
            header('Location: /admin/supervisor');
            exit;
        }

        $pendentes  = $this->model->listarComFiltros(['status' => 'PENDENTE_SUPERVISOR']);
        $recentes   = $this->model->listarComFiltros(['status' => ['PENDENTE_GERENTE','APROVADO_SUPERVISOR','REPROVADO_SUPERVISOR']], 20);
        $totalPendentes = $this->model->contarPorStatus('PENDENTE_SUPERVISOR');
        $totalAprovadas = $this->model->contarPorStatus(['APROVADO_SUPERVISOR','PENDENTE_GERENTE','ENVIADO_OPERADORA','CONCLUIDO']);
        $totalReprovadas = $this->model->contarPorStatus(['REPROVADO_SUPERVISOR','RECUSADO_GERENTE']);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $csrf_token   = $_SESSION['csrf_token'];
        $is_logged_in = true;
        $user_type    = 'VENDEDOR';
        $page_title   = 'Painel Supervisor';
        $current_page = 'supervisor';
        $vendedor     = $this->vendedorModel->findById((int)$_SESSION['user_id']);

        ob_start();
        include APP_PATH . '/views/pages/supervisor/index.php';
        $content = ob_get_clean();
        include APP_PATH . '/views/layouts/main.php';
    }

    private function processarDecisaoSupervisor(): void
    {
        $csrf = $_SESSION['csrf_token'] ?? '';
        if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) return;

        $id      = (int)($_POST['proposta_id'] ?? 0);
        $acao    = $_POST['acao'] ?? '';
        $decisao = trim($_POST['decisao'] ?? '');

        if (!$id || !in_array($acao, ['aprovar','reprovar'], true)) return;

        $proposta = $this->model->findById($id);
        if (!$proposta || $proposta['status'] !== 'PENDENTE_SUPERVISOR') return;

        if ($acao === 'aprovar') {
            $this->model->avancarStatus($id, 'APROVADO_SUPERVISOR', $decisao ?: 'Aprovado pelo supervisor.', 'decisao_supervisor');
            // Avança automaticamente para fila do gerente
            $this->model->avancarStatus($id, 'PENDENTE_GERENTE');
            log_action('PROPOSTA_APROVADA_SUPERVISOR', 'INFO', ['proposta_id' => $id], (int)$_SESSION['user_id']);
            $_SESSION['flash'] = 'Proposta aprovada e enviada para o gerente.';
        } else {
            if (empty($decisao)) {
                $_SESSION['flash'] = 'Informe a justificativa para reprovar.';
                return;
            }
            $this->model->avancarStatus($id, 'REPROVADO_SUPERVISOR', $decisao, 'decisao_supervisor');
            log_action('PROPOSTA_REPROVADA_SUPERVISOR', 'INFO', ['proposta_id' => $id, 'motivo' => $decisao], (int)$_SESSION['user_id']);
            $_SESSION['flash'] = 'Proposta reprovada.';
        }
    }

    // ------------------------------------------------------------------
    // Painel Gerente
    // ------------------------------------------------------------------

    public function gerente(): void
    {
        $this->requirePerfil(['GERENTE','ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarDecisaoGerente();
            header('Location: /admin/gerente');
            exit;
        }

        $pendentes = $this->model->listarComFiltros(['status' => 'PENDENTE_GERENTE']);
        $recentes  = $this->model->listarComFiltros(['status' => ['APROVADO_GERENTE','ENVIADO_OPERADORA','RECUSADO_GERENTE']], 20);
        $totalPendentes = $this->model->contarPorStatus('PENDENTE_GERENTE');
        $totalEnviadas = $this->model->contarPorStatus('ENVIADO_OPERADORA');
        $valorPipeline = $this->model->valorTotalPorStatus(['PENDENTE_GERENTE','ENVIADO_OPERADORA']);

        // Lista de concessionárias disponíveis para envio
        $pdo          = db();
        $operadoras   = $pdo->query("SELECT id, nome FROM operadoras WHERE ativo = 1 ORDER BY nome")->fetchAll();

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $csrf_token   = $_SESSION['csrf_token'];
        $is_logged_in = true;
        $user_type    = 'VENDEDOR';
        $page_title   = 'Painel Gerente';
        $current_page = 'gerente';
        $vendedor     = $this->vendedorModel->findById((int)$_SESSION['user_id']);

        ob_start();
        include APP_PATH . '/views/pages/gerente/index.php';
        $content = ob_get_clean();
        include APP_PATH . '/views/layouts/main.php';
    }

    private function processarDecisaoGerente(): void
    {
        $csrf = $_SESSION['csrf_token'] ?? '';
        if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) return;

        $id      = (int)($_POST['proposta_id'] ?? 0);
        $acao    = $_POST['acao'] ?? '';
        $decisao = trim($_POST['decisao'] ?? '');
        $destino = trim($_POST['operadora_destino'] ?? '');

        if (!$id || !in_array($acao, ['finalizar','recusar'], true)) return;

        $proposta = $this->model->findById($id);
        if (!$proposta || $proposta['status'] !== 'PENDENTE_GERENTE') return;

        if ($acao === 'finalizar') {
            if (empty($destino)) {
                $_SESSION['flash'] = 'Selecione a concessionária de destino.';
                return;
            }
            $this->model->update($id, [
                'status'            => 'ENVIADO_OPERADORA',
                'decisao_gerente'   => $decisao ?: 'Aprovado e enviado à operadora.',
                'operadora_destino' => $destino,
            ]);
            log_action('PROPOSTA_FINALIZADA', 'INFO', ['proposta_id' => $id, 'destino' => $destino], (int)$_SESSION['user_id']);
            $_SESSION['flash'] = "Proposta finalizada e enviada para: $destino.";
        } else {
            if (empty($decisao)) {
                $_SESSION['flash'] = 'Informe a justificativa para recusar.';
                return;
            }
            $this->model->avancarStatus($id, 'RECUSADO_GERENTE', $decisao, 'decisao_gerente');
            log_action('PROPOSTA_RECUSADA_GERENTE', 'INFO', ['proposta_id' => $id, 'motivo' => $decisao], (int)$_SESSION['user_id']);
            $_SESSION['flash'] = 'Proposta recusada.';
        }
    }

    // ------------------------------------------------------------------
    // Tabelas de Preco
    // ------------------------------------------------------------------

    public function tabelas(): void
    {
        $this->requirePerfil(['GERENTE','ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processarTabelaPreco();
            header('Location: /admin/gerente/tabelas');
            exit;
        }

        $pdo = db();
        $planos = $pdo->query(
            "SELECT p.*, o.nome AS operadora_nome, o.slug AS operadora_slug
             FROM planos p
             JOIN operadoras o ON p.operadora_id = o.id
             ORDER BY o.nome, p.categoria, p.nome"
        )->fetchAll();

        $operadoras = $pdo->query(
            "SELECT o.id, o.nome, o.slug, o.ativo,
                    COUNT(p.id) AS total_planos,
                    SUM(CASE WHEN p.ativo = 1 THEN 1 ELSE 0 END) AS planos_ativos
             FROM operadoras o
             LEFT JOIN planos p ON p.operadora_id = o.id
             GROUP BY o.id, o.nome, o.slug, o.ativo
             ORDER BY o.nome"
        )->fetchAll();

        $historico = $pdo->query(
            "SELECT acao, nivel, dados, criado_em
             FROM logs
             WHERE acao IN ('TABELA_PRECO_IMPORTADA', 'PLANO_STATUS_ALTERADO', 'OPERADORA_STATUS_ALTERADO')
             ORDER BY criado_em DESC
             LIMIT 12"
        )->fetchAll();

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $csrf_token   = $_SESSION['csrf_token'];
        $is_logged_in = true;
        $user_type    = 'VENDEDOR';
        $page_title   = 'Tabelas de Preco';
        $current_page = 'tabelas';
        $vendedor     = $this->vendedorModel->findById((int)$_SESSION['user_id']);

        ob_start();
        include APP_PATH . '/views/pages/gerente/tabelas.php';
        $content = ob_get_clean();
        include APP_PATH . '/views/layouts/main.php';
    }

    private function processarTabelaPreco(): void
    {
        $csrf = $_SESSION['csrf_token'] ?? '';
        if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) {
            $_SESSION['flash'] = 'Token de seguranca invalido.';
            return;
        }

        $acao = $_POST['acao'] ?? '';
        if ($acao === 'toggle_plano') {
            $this->togglePlano();
            return;
        }

        if ($acao === 'toggle_operadora') {
            $this->toggleOperadora();
            return;
        }

        if ($acao === 'importar_csv') {
            $this->importarTabelaCsv();
            return;
        }

        $_SESSION['flash'] = 'Acao nao reconhecida.';
    }

    private function togglePlano(): void
    {
        $id = (int)($_POST['plano_id'] ?? 0);
        $ativo = (int)($_POST['ativo'] ?? 0);
        if (!$id) {
            $_SESSION['flash'] = 'Plano invalido.';
            return;
        }

        $stmt = db()->prepare("UPDATE planos SET ativo = ? WHERE id = ?");
        $stmt->execute([$ativo ? 1 : 0, $id]);

        log_action('PLANO_STATUS_ALTERADO', 'INFO', [
            'plano_id' => $id,
            'ativo' => $ativo ? 1 : 0,
        ], (int)$_SESSION['user_id']);

        $_SESSION['flash'] = $ativo ? 'Plano ativado.' : 'Plano desativado.';
    }

    private function toggleOperadora(): void
    {
        $id = (int)($_POST['operadora_id'] ?? 0);
        $ativo = (int)($_POST['ativo'] ?? 0);
        if (!$id) {
            $_SESSION['flash'] = 'Operadora invalida.';
            return;
        }

        $stmt = db()->prepare("UPDATE operadoras SET ativo = ? WHERE id = ?");
        $stmt->execute([$ativo ? 1 : 0, $id]);

        log_action('OPERADORA_STATUS_ALTERADO', 'INFO', [
            'operadora_id' => $id,
            'ativo' => $ativo ? 1 : 0,
        ], (int)$_SESSION['user_id']);

        $_SESSION['flash'] = $ativo ? 'Operadora ativada para cotacoes.' : 'Operadora pausada nas cotacoes.';
    }

    private function importarTabelaCsv(): void
    {
        $file = $_FILES['arquivo_csv'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = 'Envie um arquivo CSV valido.';
            return;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $_SESSION['flash'] = 'A importacao atual aceita apenas arquivos .csv.';
            return;
        }

        try {
            $service = new PriceTableImportService(db());
            $summary = $service->importarCsv($file['tmp_name']);

            log_action('TABELA_PRECO_IMPORTADA', 'INFO', $summary, (int)$_SESSION['user_id']);

            $msg = "Importacao concluida: {$summary['criados']} criado(s), {$summary['atualizados']} atualizado(s), {$summary['ignorados']} ignorado(s).";
            if (!empty($summary['erros'])) {
                $msg .= ' Primeiros avisos: ' . implode(' | ', array_slice($summary['erros'], 0, 3));
            }
            $_SESSION['flash'] = $msg;
        } catch (Throwable $e) {
            $_SESSION['flash'] = 'Falha ao importar CSV: ' . $e->getMessage();
        }
    }

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------

    private function requirePerfil(array $perfisPermitidos): void
    {
        if (empty($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'VENDEDOR') {
            header('Location: /login');
            exit;
        }
        $perfil = $_SESSION['user_data']['perfil'] ?? '';
        if (!in_array($perfil, $perfisPermitidos, true)) {
            http_response_code(403);
            echo 'Acesso não autorizado.';
            exit;
        }
        if (!empty($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
            session_destroy();
            header('Location: /login?timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}
