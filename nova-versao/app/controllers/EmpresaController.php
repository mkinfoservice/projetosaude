<?php
/**
 * app/controllers/EmpresaController.php
 * Cadastro de empresa, painel administrativo e fluxo PIX
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

require_once APP_PATH . '/models/EmpresaModel.php';
require_once APP_PATH . '/services/AsaasService.php';

class EmpresaController
{
    private EmpresaModel $model;

    public function __construct()
    {
        $this->model = new EmpresaModel();
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
    // Cadastro
    // ------------------------------------------------------------------

    public function cadastro(): void
    {
        // Redireciona se já logado
        if (!empty($_SESSION['logged_in']) && $_SESSION['user_type'] === 'EMPRESA') {
            header('Location: /admin/empresa');
            exit;
        }

        $errors     = [];
        $input      = [];
        $pixData    = null;
        $csrf_token = $_SESSION['csrf_token'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$errors, $input, $pixData] = $this->processarCadastro($csrf_token);

            if (empty($errors) && $pixData !== null) {
                // Sucesso: redireciona para a página de aguardo de pagamento
                header('Location: /empresa/aguardando-pagamento');
                exit;
            }
        }

        $page_title   = 'Cadastro de Empresa';
        $current_page = 'cadastro';
        $is_logged_in = false;
        $user_type    = null;

        ob_start();
        include APP_PATH . '/views/pages/empresa/cadastro.php';
        $content = ob_get_clean();

        include APP_PATH . '/views/layouts/main.php';
    }

    private function processarCadastro(string $csrf_token): array
    {
        $errors = [];
        $input  = $this->sanitizeInput($_POST);

        // CSRF
        if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) {
            return [['csrf' => 'Token de segurança inválido.'], $input, null];
        }

        // Validações
        if (empty($input['cnpj']) || !$this->validarCnpj($input['cnpj'])) {
            $errors['cnpj'] = 'CNPJ inválido.';
        }

        if (empty($input['razao_social']) || strlen($input['razao_social']) < 3) {
            $errors['razao_social'] = 'Razão social obrigatória (mínimo 3 caracteres).';
        }

        if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido.';
        }

        $telefone = preg_replace('/\D/', '', $input['telefone'] ?? '');
        if (strlen($telefone) < 10) {
            $errors['telefone'] = 'Telefone inválido.';
        }

        $cep = preg_replace('/\D/', '', $input['cep'] ?? '');
        if (strlen($cep) !== 8) {
            $errors['cep'] = 'CEP inválido.';
        }

        if (empty($input['endereco'])) {
            $errors['endereco'] = 'Endereço obrigatório.';
        }

        if (empty($input['cidade'])) {
            $errors['cidade'] = 'Cidade obrigatória.';
        }

        if (empty($input['uf']) || strlen($input['uf']) !== 2) {
            $errors['uf'] = 'UF inválida.';
        }

        if (empty($input['senha']) || strlen($input['senha']) < 8) {
            $errors['senha'] = 'Senha deve ter no mínimo 8 caracteres.';
        } elseif ($input['senha'] !== ($input['senha_confirmacao'] ?? '')) {
            $errors['senha_confirmacao'] = 'As senhas não conferem.';
        }

        if (!isset($_FILES['contrato_social']) || $_FILES['contrato_social']['error'] !== UPLOAD_ERR_OK) {
            $errors['contrato_social'] = 'Envie o contrato social (PDF, JPG ou PNG, máx. 10MB).';
        } else {
            $uploadError = $this->validarUpload($_FILES['contrato_social']);
            if ($uploadError) {
                $errors['contrato_social'] = $uploadError;
            }
        }

        if (!empty($errors)) {
            return [$errors, $input, null];
        }

        // Unicidade
        $cnpjLimpo = preg_replace('/\D/', '', $input['cnpj']);
        if ($this->model->findByCnpj($cnpjLimpo)) {
            $errors['cnpj'] = 'CNPJ já cadastrado.';
        }
        if ($this->model->findByEmail($input['email'])) {
            $errors['email'] = 'E-mail já cadastrado.';
        }

        if (!empty($errors)) {
            return [$errors, $input, null];
        }

        // Upload do contrato
        $contratoUrl = $this->moverUpload($_FILES['contrato_social'], 'contratos');
        if (!$contratoUrl) {
            return [['contrato_social' => 'Falha ao salvar o arquivo. Tente novamente.'], $input, null];
        }

        // Salva empresa com status PENDENTE_PAGAMENTO
        $empresaId = $this->model->insert([
            'cnpj'               => $cnpjLimpo,
            'razao_social'       => $input['razao_social'],
            'email'              => $input['email'],
            'senha_hash'         => password_hash($input['senha'], PASSWORD_BCRYPT, ['cost' => 12]),
            'telefone'           => $telefone,
            'cep'                => $cep,
            'endereco'           => $input['endereco'],
            'bairro'             => $input['bairro'] ?? '',
            'numero'             => $input['numero'] ?? '',
            'complemento'        => $input['complemento'] ?? '',
            'cidade'             => $input['cidade'],
            'uf'                 => strtoupper($input['uf']),
            'contrato_social_url'=> $contratoUrl,
            'status'             => 'PENDENTE_PAGAMENTO',
            'pix_status'         => 'PENDENTE',
        ]);

        log_action('EMPRESA_CADASTRO', 'INFO', ['cnpj' => $cnpjLimpo, 'email' => $input['email']], $empresaId);

        // Gera cobrança PIX via Asaas
        $pixData = $this->gerarPix($empresaId, $input['razao_social'], $cnpjLimpo, $input['email'], $telefone);

        // Guarda dados do PIX na sessão para exibir na próxima página
        $_SESSION['pix_empresa_id']  = $empresaId;
        $_SESSION['pix_data']        = $pixData;
        $_SESSION['pix_razao_social']= $input['razao_social'];

        return [$errors, $input, $pixData];
    }

    private function gerarPix(int $empresaId, string $nome, string $cnpj, string $email, string $telefone): array
    {
        $asaas = new AsaasService();

        if (!$asaas->isConfigured()) {
            // Modo simulação: sem chave Asaas configurada
            $pixSimulado = [
                'id'             => 'pay_simulado_' . $empresaId,
                'status'         => 'PENDING',
                'value'          => 97.00,
                'simulado'       => true,
                'pixTransaction' => [
                    'payload'        => '00020126580014br.gov.bcb.pix0136' . bin2hex(random_bytes(16)) . '5204000053039865802BR5925Concessionaria Inteligente6009SAO PAULO62070503***6304' . strtoupper(substr(md5(random_bytes(8)), 0, 4)),
                    'qrCode'         => null, // sem imagem no modo simulado
                    'expirationDate' => date('Y-m-d\TH:i:s', strtotime('+3 days')),
                ],
            ];

            $this->model->update($empresaId, [
                'asaas_payment_id' => $pixSimulado['id'],
                'pix_status'       => 'PENDENTE',
            ]);
            $this->registrarPagamento('EMPRESA', $empresaId, 97.00, $pixSimulado['id'], 'PENDING', $pixSimulado['pixTransaction']['payload'] ?? null);

            log_action('PIX_SIMULADO', 'WARNING', ['empresa_id' => $empresaId, 'motivo' => 'ASAAS_API_KEY não configurada']);
            return $pixSimulado;
        }

        try {
            $cliente = $asaas->criarCliente($nome, $cnpj, $email, $telefone);
            $payment = $asaas->criarCobrancaPix(
                $cliente['id'],
                97.00,
                'Taxa de adesão — Concessionária Inteligente Bem'
            );

            $this->model->update($empresaId, [
                'asaas_payment_id' => $payment['id'],
                'pix_status'       => 'PENDENTE',
            ]);
            $this->registrarPagamento('EMPRESA', $empresaId, 97.00, $payment['id'], $payment['status'] ?? 'PENDING', $payment['pixTransaction']['payload'] ?? null);

            log_action('PIX_GERADO', 'INFO', ['empresa_id' => $empresaId, 'payment_id' => $payment['id']]);
            return $payment;

        } catch (RuntimeException $e) {
            log_action('PIX_ERRO', 'ERROR', ['empresa_id' => $empresaId, 'erro' => $e->getMessage()]);
            // Retorna modo simulado como fallback para não bloquear o cadastro
            return [
                'id'       => 'pay_erro_' . $empresaId,
                'status'   => 'PENDING',
                'value'    => 97.00,
                'simulado' => true,
                'erro'     => 'Falha ao gerar PIX automático. Entre em contato via WhatsApp.',
                'pixTransaction' => ['payload' => null, 'qrCode' => null],
            ];
        }
    }

    private function registrarPagamento(string $entityType, int $entityId, float $valor, string $paymentId, string $status, ?string $pixPayload = null): void
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                "INSERT INTO pagamentos (entity_type, entity_id, valor, asaas_payment_id, pix_copia_cola, status)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$entityType, $entityId, $valor, $paymentId, $pixPayload, $status]);
        } catch (Throwable $e) {
            log_action('PAGAMENTO_REGISTRO_ERRO', 'ERROR', [
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'payment_id'  => $paymentId,
                'erro'        => $e->getMessage(),
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Aguardando pagamento
    // ------------------------------------------------------------------

    public function aguardandoPagamento(): void
    {
        if (empty($_SESSION['pix_empresa_id'])) {
            header('Location: /empresa/cadastro');
            exit;
        }

        $empresaId   = (int) $_SESSION['pix_empresa_id'];
        $pixData     = $_SESSION['pix_data']         ?? [];
        $razaoSocial = $_SESSION['pix_razao_social'] ?? '';
        $empresa     = $this->model->findById($empresaId);

        // Se já ativou (ex: admin ativou manualmente), redireciona para login
        if ($empresa && $empresa['status'] === 'ATIVO') {
            unset($_SESSION['pix_empresa_id'], $_SESSION['pix_data'], $_SESSION['pix_razao_social']);
            header('Location: /login?ativado=1');
            exit;
        }

        $csrf_token   = $_SESSION['csrf_token'];
        $page_title   = 'Aguardando Pagamento';
        $current_page = '';
        $is_logged_in = false;
        $user_type    = null;

        ob_start();
        include APP_PATH . '/views/pages/empresa/aguardando-pagamento.php';
        $content = ob_get_clean();

        include APP_PATH . '/views/layouts/main.php';
    }

    // ------------------------------------------------------------------
    // Dashboard (requer login)
    // ------------------------------------------------------------------

    public function dashboard(): void
    {
        $this->requireLogin('EMPRESA');

        $empresaId   = (int) $_SESSION['user_id'];
        $empresa     = $this->model->findById($empresaId);

        if (!$empresa) {
            header('Location: /logout');
            exit;
        }

        $totalVendedores = $this->model->totalVendedores($empresaId);
        $totalPropostas  = $this->model->totalPropostas($empresaId);
        $totalPagamentos = $this->model->totalPagamentos($empresaId);

        // Últimos vendedores
        $pdo           = db();
        $stmtVendedores = $pdo->prepare(
            "SELECT id, nome_completo, perfil, status, criado_em
             FROM vendedores WHERE empresa_id = ? ORDER BY criado_em DESC LIMIT 10"
        );
        $stmtVendedores->execute([$empresaId]);
        $vendedores = $stmtVendedores->fetchAll();

        // Últimas propostas da empresa
        $stmtPropostas = $pdo->prepare(
            "SELECT p.protocolo, p.status, p.criado_em, c.nome_completo as cliente,
                    v.nome_completo as vendedor
             FROM propostas p
             JOIN vendedores v ON p.vendedor_id = v.id
             JOIN clientes c ON p.cliente_id = c.id
             WHERE v.empresa_id = ?
             ORDER BY p.criado_em DESC LIMIT 10"
        );
        $stmtPropostas->execute([$empresaId]);
        $propostas = $stmtPropostas->fetchAll();

        $csrf_token   = $_SESSION['csrf_token'];
        $is_logged_in = true;
        $user_type    = 'EMPRESA';
        $page_title   = 'Painel da Empresa';
        $current_page = 'dashboard';

        ob_start();
        include APP_PATH . '/views/pages/empresa/dashboard.php';
        $content = ob_get_clean();

        include APP_PATH . '/views/layouts/main.php';
    }

    public function propostas(): void
    {
        $this->requireLogin('EMPRESA');

        $empresaId = (int) $_SESSION['user_id'];
        $empresa   = $this->model->findById($empresaId);

        if (!$empresa) {
            header('Location: /logout');
            exit;
        }

        $propostas = $this->model->propostasRecentes($empresaId);

        $csrf_token   = $_SESSION['csrf_token'];
        $is_logged_in = true;
        $user_type    = 'EMPRESA';
        $page_title   = 'Propostas da Empresa';
        $current_page = 'propostas';

        ob_start();
        include APP_PATH . '/views/pages/empresa/propostas.php';
        $content = ob_get_clean();

        include APP_PATH . '/views/layouts/main.php';
    }

    public function pagamentos(): void
    {
        $this->requireLogin('EMPRESA');

        $empresaId = (int) $_SESSION['user_id'];
        $empresa   = $this->model->findById($empresaId);

        if (!$empresa) {
            header('Location: /logout');
            exit;
        }

        $pagamentos = $this->model->pagamentosRecentes($empresaId);

        $csrf_token   = $_SESSION['csrf_token'];
        $is_logged_in = true;
        $user_type    = 'EMPRESA';
        $page_title   = 'Histórico de Pagamentos';
        $current_page = 'pagamentos';

        ob_start();
        include APP_PATH . '/views/pages/empresa/pagamentos.php';
        $content = ob_get_clean();

        include APP_PATH . '/views/layouts/main.php';
    }

    // ------------------------------------------------------------------
    // Webhook Asaas (chamado pelo servidor Asaas, não pelo browser)
    // ------------------------------------------------------------------

    public function webhookAsaas(): void
    {
        // Valida token
        $token = $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? ($_GET['token'] ?? '');
        if (!AsaasService::validarWebhookToken($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!$payload) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid payload']);
            exit;
        }

        $event     = $payload['event']          ?? '';
        $paymentId = $payload['payment']['id']  ?? '';
        $status    = $payload['payment']['status'] ?? '';

        log_action('WEBHOOK_ASAAS', 'INFO', ['event' => $event, 'payment_id' => $paymentId, 'status' => $status]);

        if ($event === 'PAYMENT_CONFIRMED' || $event === 'PAYMENT_RECEIVED') {
            $stmtPagamento = db()->prepare(
                "UPDATE pagamentos
                 SET status = ?, confirmado_em = COALESCE(confirmado_em, NOW())
                 WHERE asaas_payment_id = ?"
            );
            $stmtPagamento->execute([$status ?: 'CONFIRMED', $paymentId]);

            // Tenta ativar empresa
            $empresa = $this->model->findByAsaasPaymentId($paymentId);
            if ($empresa && $empresa['status'] === 'PENDENTE_PAGAMENTO') {
                $this->model->ativar($empresa['id']);
                log_action('EMPRESA_ATIVADA', 'INFO', ['empresa_id' => $empresa['id'], 'payment_id' => $paymentId]);
            }

            // Tenta ativar vendedor
            require_once APP_PATH . '/models/VendedorModel.php';
            $vendedorModel = new VendedorModel();
            $vendedor = $vendedorModel->findByAsaasPaymentId($paymentId);
            if ($vendedor && $vendedor['status'] === 'PENDENTE_PAGAMENTO') {
                $vendedorModel->ativar($vendedor['id']);
                log_action('VENDEDOR_ATIVADO_WEBHOOK', 'INFO', ['vendedor_id' => $vendedor['id'], 'payment_id' => $paymentId]);
            }
        }

        http_response_code(200);
        echo json_encode(['received' => true]);
        exit;
    }

    // ------------------------------------------------------------------
    // API: checa status do pagamento (polling pelo frontend)
    // ------------------------------------------------------------------

    public function checkPaymentStatus(): void
    {
        if (empty($_SESSION['pix_empresa_id'])) {
            json_response(false, [], 'Sessão inválida.', 400);
        }

        $empresaId = (int) $_SESSION['pix_empresa_id'];
        $empresa   = $this->model->findById($empresaId);

        if (!$empresa) {
            json_response(false, [], 'Empresa não encontrada.', 404);
        }

        // Se já ativo no banco: retorna ativo
        if ($empresa['status'] === 'ATIVO') {
            json_response(true, ['status' => 'ATIVO', 'redirect' => '/login?ativado=1']);
        }

        // Consulta Asaas se tiver payment_id real
        if (!empty($empresa['asaas_payment_id']) && !str_starts_with($empresa['asaas_payment_id'], 'pay_simulado_')) {
            try {
                $asaas   = new AsaasService();
                $payment = $asaas->buscarPagamento($empresa['asaas_payment_id']);

                if (in_array($payment['status'] ?? '', ['CONFIRMED', 'RECEIVED'], true)) {
                    $this->model->ativar($empresaId);
                    log_action('EMPRESA_ATIVADA_POLLING', 'INFO', ['empresa_id' => $empresaId]);
                    json_response(true, ['status' => 'ATIVO', 'redirect' => '/login?ativado=1']);
                }

                json_response(true, ['status' => $payment['status'] ?? 'PENDING']);
            } catch (RuntimeException $e) {
                json_response(true, ['status' => 'PENDENTE', 'aviso' => 'Não foi possível checar em tempo real.']);
            }
        }

        json_response(true, ['status' => 'PENDENTE']);
    }

    // ------------------------------------------------------------------
    // Helpers privados
    // ------------------------------------------------------------------

    private function requireLogin(string $tipoEsperado): void
    {
        if (empty($_SESSION['logged_in']) || ($_SESSION['user_type'] ?? '') !== $tipoEsperado) {
            header('Location: /login');
            exit;
        }

        // Timeout por inatividade
        if (!empty($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
            session_destroy();
            header('Location: /login?timeout=1');
            exit;
        }

        $_SESSION['last_activity'] = time();
    }

    private function sanitizeInput(array $post): array
    {
        $fields = ['cnpj','razao_social','email','telefone','cep','endereco',
                   'bairro','numero','complemento','cidade','uf','senha','senha_confirmacao'];
        $out = [];
        foreach ($fields as $f) {
            $out[$f] = trim($post[$f] ?? '');
        }
        return $out;
    }

    private function validarCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        if (strlen($cnpj) !== 14) return false;
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) return false;

        $calc = function (string $cnpj, int $length): int {
            $sum    = 0;
            $pos    = $length - 7;
            for ($i = $length; $i >= 1; $i--) {
                $sum += (int)$cnpj[$length - $i] * $pos--;
                if ($pos < 2) $pos = 9;
            }
            $result = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
            return $result;
        };

        $d1 = $calc($cnpj, 12);
        $d2 = $calc($cnpj, 13);

        return (int)$cnpj[12] === $d1 && (int)$cnpj[13] === $d2;
    }

    private function validarUpload(array $file): ?string
    {
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return 'Arquivo muito grande. Máximo: ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . 'MB.';
        }

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $permitidos = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($mimeType, $permitidos, true)) {
            return 'Tipo de arquivo não permitido. Use PDF, JPG ou PNG.';
        }

        return null;
    }

    private function moverUpload(array $file, string $subdir): ?string
    {
        $uploadDir = UPLOAD_PATH . $subdir . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = match(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name'])) {
            'application/pdf' => 'pdf',
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png'  => 'png',
            default      => 'bin',
        };
        $filename = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return null;
        }

        return '/storage/uploads/' . $subdir . '/' . $filename;
    }
}
