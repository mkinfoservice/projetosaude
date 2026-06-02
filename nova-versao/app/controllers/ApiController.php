<?php
/**
 * app/controllers/ApiController.php
 * Endpoints JSON para o frontend
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

require_once APP_PATH . '/services/CotacaoService.php';

class ApiController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = db();
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }

    /**
     * Verifica sessão e retorna ID do usuário autenticado
     */
    private function requireAuth(array $types = []): int
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
            json_response(false, [], 'Não autenticado', 401);
        }

        if (!empty($types) && !in_array($_SESSION['user_type'], $types)) {
            json_response(false, [], 'Acesso não autorizado', 403);
        }

        return (int) $_SESSION['user_id'];
    }

    private function validateCsrf(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            json_response(false, [], 'Token CSRF inválido', 403);
        }
    }

    // ======================================
    // OPERADORAS
    // ======================================
    public function operadoras(): void
    {
        $stmt = $this->pdo->query(
            "SELECT id, nome, slug, logo_url, descricao FROM operadoras WHERE ativo = 1 ORDER BY nome"
        );
        json_response(true, $stmt->fetchAll());
    }

    // ======================================
    // PLANOS
    // ======================================
    public function planos(): void
    {
        $operadoraId = filter_input(INPUT_GET, 'operadora_id', FILTER_VALIDATE_INT);
        $categoria   = in_array($_GET['categoria'] ?? '', ['SEM_COPARTICIPACAO', 'COM_COPARTICIPACAO'])
            ? $_GET['categoria']
            : 'SEM_COPARTICIPACAO';

        if (!$operadoraId) {
            // Retorna todos
            $stmt = $this->pdo->prepare("
                SELECT p.*, o.nome AS operadora_nome
                FROM planos p
                JOIN operadoras o ON p.operadora_id = o.id
                WHERE p.categoria = ? AND p.ativo = 1 AND o.ativo = 1
                ORDER BY o.nome, p.nome
            ");
            $stmt->execute([$categoria]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT p.*
                FROM planos p
                JOIN operadoras o ON p.operadora_id = o.id
                WHERE p.operadora_id = ? AND p.categoria = ? AND p.ativo = 1 AND o.ativo = 1
                ORDER BY nome
            ");
            $stmt->execute([$operadoraId, $categoria]);
        }

        json_response(true, $stmt->fetchAll());
    }

    // ======================================
    // COTACAO INTELIGENTE
    // ======================================
    public function cotacao(): void
    {
        $idadesRaw = $_POST['idades'] ?? $_GET['idades'] ?? '';
        $categoriaRaw = $_POST['categoria'] ?? $_GET['categoria'] ?? '';
        $operadoraSlug = trim((string)($_POST['operadora'] ?? $_GET['operadora'] ?? ''));

        $idades = $this->parseIdades((string)$idadesRaw);
        if (empty($idades)) {
            json_response(false, [], 'Informe idades validas para cotacao.');
        }

        $categoria = match ($categoriaRaw) {
            'COM_COPARTICIPACAO', 'com', 'sim' => 'COM_COPARTICIPACAO',
            'SEM_COPARTICIPACAO', 'sem', 'nao', '' => 'SEM_COPARTICIPACAO',
            default => null,
        };

        if ($categoria === null) {
            json_response(false, [], 'Categoria de coparticipacao invalida.');
        }

        $operadoraSlug = $operadoraSlug !== '' ? preg_replace('/[^a-z0-9-]/', '', strtolower($operadoraSlug)) : null;

        $service = new CotacaoService($this->pdo);
        $cotacao = $service->cotar($idades, $categoria, $operadoraSlug);

        json_response(true, [
            'idades' => $idades,
            'categoria' => $categoria,
            'categoria_label' => $categoria === 'COM_COPARTICIPACAO' ? 'Com coparticipacao' : 'Sem coparticipacao',
            'fonte' => $cotacao['fonte'],
            'fallback_usado' => $cotacao['fallback_usado'],
            'resultados' => $cotacao['resultados'],
        ]);
    }

    private function parseIdades(string $idadesRaw): array
    {
        $idades = preg_split('/[,\s;]+/', trim($idadesRaw), -1, PREG_SPLIT_NO_EMPTY);
        if (!$idades) {
            return [];
        }

        $validas = [];
        foreach ($idades as $idade) {
            $idade = filter_var($idade, FILTER_VALIDATE_INT);
            if ($idade !== false && $idade >= 0 && $idade <= 120) {
                $validas[] = $idade;
            }
        }

        return array_slice($validas, 0, 20);
    }

    // ======================================
    // CEP
    // ======================================
    public function cep(): void
    {
        $cep = preg_replace('/\D/', '', $_GET['cep'] ?? '');
        if (strlen($cep) !== 8) {
            json_response(false, [], 'CEP inválido');
        }

        $ctx = stream_context_create(['http' => ['timeout' => 5]]);
        $raw = @file_get_contents("https://viacep.com.br/ws/{$cep}/json/", false, $ctx);

        if (!$raw) json_response(false, [], 'Erro ao consultar CEP');

        $data = json_decode($raw, true);
        if (!empty($data['erro'])) json_response(false, [], 'CEP não encontrado');

        json_response(true, $data);
    }

    // ======================================
    // AUTH (login AJAX)
    // ======================================
    public function auth(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(false, [], 'Método não permitido', 405);
        }

        // Delega ao AuthController
        require_once APP_PATH . '/controllers/AuthController.php';
        $ctrl = new AuthController();
        $ctrl->login();
    }

    // ======================================
    // EMPRESA
    // ======================================
    public function empresa(): void
    {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        match ($action) {
            'identify' => $this->identifyEmpresa(),
            default    => json_response(false, [], 'Ação não reconhecida'),
        };
    }

    private function identifyEmpresa(): void
    {
        $cnpj = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');
        if (strlen($cnpj) !== 14) json_response(false, [], 'CNPJ inválido');

        $stmt = $this->pdo->prepare("SELECT id, razao_social, status FROM empresas WHERE cnpj = ? LIMIT 1");
        $stmt->execute([$cnpj]);
        $emp = $stmt->fetch();

        if (!$emp) json_response(false, [], 'Empresa não encontrada');
        json_response(true, ['id' => $emp['id'], 'razao_social' => $emp['razao_social'], 'status' => $emp['status']]);
    }

    // ======================================
    // VENDEDOR
    // ======================================
    public function vendedor(): void
    {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        match ($action) {
            'identify_vendedor' => $this->identifyVendedor(),
            default             => json_response(false, [], 'Ação não reconhecida'),
        };
    }

    private function identifyVendedor(): void
    {
        $telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
        if (strlen($telefone) < 10) json_response(false, [], 'Telefone inválido');

        $stmt = $this->pdo->prepare("
            SELECT v.id, v.nome_completo AS nome, v.perfil, e.razao_social AS empresa
            FROM vendedores v
            LEFT JOIN empresas e ON v.empresa_id = e.id
            WHERE v.telefone = ? AND v.status = 'ATIVO' LIMIT 1
        ");
        $stmt->execute([$telefone]);
        $vendedor = $stmt->fetch();

        if (!$vendedor) {
            json_response(false, [], 'Vendedor não encontrado');
        }

        json_response(true, $vendedor);
    }

    // ======================================
    // PROPOSTA
    // ======================================
    public function proposta(): void
    {
        $userId = $this->requireAuth(['VENDEDOR']);

        $id        = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $protocolo = trim($_GET['protocolo'] ?? '');

        if (!$id && $protocolo === '') {
            json_response(false, [], 'Informe id ou protocolo.');
        }

        require_once APP_PATH . '/models/PropostaModel.php';
        $model = new PropostaModel();

        if ($id) {
            $proposta = $model->findCompleto($id);
        } else {
            $byProtocolo = $model->findByProtocolo($protocolo);
            $proposta    = $byProtocolo ? $model->findCompleto((int)$byProtocolo['id']) : null;
        }

        if (!$proposta) {
            json_response(false, [], 'Proposta não encontrada.', 404);
        }

        // Vendedor só acessa as próprias; supervisor/gerente/admin acessam todas
        if (session_status() === PHP_SESSION_NONE) session_start();
        $perfil = $_SESSION['user_data']['perfil'] ?? 'VENDEDOR';
        if (!in_array($perfil, ['SUPERVISOR','GERENTE','ADMIN'], true)
            && (int)$proposta['vendedor_id'] !== $userId) {
            json_response(false, [], 'Acesso negado.', 403);
        }

        json_response(true, [
            'id'             => $proposta['id'],
            'protocolo'      => $proposta['protocolo'],
            'status'         => $proposta['status'],
            'valor_total'    => $proposta['valor_total'],
            'quantidade_vidas' => $proposta['quantidade_vidas'],
            'cliente_nome'   => $proposta['cliente_nome'],
            'operadora_nome' => $proposta['operadora_nome'],
            'plano_nome'     => $proposta['plano_nome'],
            'criado_em'      => $proposta['criado_em'],
            'atualizado_em'  => $proposta['atualizado_em'],
        ]);
    }

    // ======================================
    // PAGAMENTO
    // ======================================
    public function payment(): void
    {
        json_response(false, [], 'Módulo de pagamento não disponível via API.');
    }

    // ======================================
    // CSRF TOKEN
    // ======================================
    public function csrf(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        json_response(true, ['csrf_token' => $_SESSION['csrf_token']]);
    }
}
