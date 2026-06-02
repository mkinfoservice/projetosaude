<?php
/**
 * app/controllers/VendedorController.php
 * Cadastro de vendedor (pela empresa) e painel do vendedor
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

require_once APP_PATH . '/models/VendedorModel.php';
require_once APP_PATH . '/models/EmpresaModel.php';
require_once APP_PATH . '/services/AsaasService.php';

class VendedorController
{
    private VendedorModel $model;
    private EmpresaModel  $empresaModel;

    public function __construct()
    {
        $this->model        = new VendedorModel();
        $this->empresaModel = new EmpresaModel();
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
    // Lista de vendedores (painel empresa)
    // ------------------------------------------------------------------

    public function lista(): void
    {
        $this->requireEmpresa();

        $empresaId  = (int) $_SESSION['user_id'];
        $empresa    = $this->empresaModel->findById($empresaId);
        $vendedores = $this->model->findByEmpresa($empresaId);

        $csrf_token   = $_SESSION['csrf_token'];
        $is_logged_in = true;
        $user_type    = 'EMPRESA';
        $page_title   = 'Vendedores';
        $current_page = 'vendedores';

        ob_start();
        include APP_PATH . '/views/pages/vendedor/lista.php';
        $content = ob_get_clean();
        include APP_PATH . '/views/layouts/main.php';
    }

    // ------------------------------------------------------------------
    // Cadastro de vendedor (feito pela empresa logada)
    // ------------------------------------------------------------------

    public function novo(): void
    {
        $this->requireEmpresa();

        $empresaId = (int) $_SESSION['user_id'];
        $empresa   = $this->empresaModel->findById($empresaId);
        $errors    = [];
        $input     = [];
        $csrf_token = $_SESSION['csrf_token'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$errors, $input] = $this->processarCadastro($csrf_token, $empresaId);

            if (empty($errors)) {
                $_SESSION['flash'] = 'Vendedor cadastrado com sucesso! PIX de ativação gerado.';
                header('Location: /admin/empresa/vendedores');
                exit;
            }
        }

        $page_title   = 'Cadastrar Vendedor';
        $current_page = 'vendedores';
        $is_logged_in = true;
        $user_type    = 'EMPRESA';

        ob_start();
        include APP_PATH . '/views/pages/vendedor/novo.php';
        $content = ob_get_clean();
        include APP_PATH . '/views/layouts/main.php';
    }

    private function processarCadastro(string $csrf_token, int $empresaId): array
    {
        $errors = [];
        $input  = $this->sanitizeInput($_POST);

        // CSRF
        if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) {
            return [['csrf' => 'Token de segurança inválido.'], $input];
        }

        // Validações básicas
        if (empty($input['nome_completo']) || strlen($input['nome_completo']) < 5) {
            $errors['nome_completo'] = 'Nome completo obrigatório (mínimo 5 caracteres).';
        }

        $cpf = preg_replace('/\D/', '', $input['cpf'] ?? '');
        if (!$this->validarCpf($cpf)) {
            $errors['cpf'] = 'CPF inválido.';
        }

        if (empty($input['rg'])) {
            $errors['rg'] = 'RG obrigatório.';
        }

        $telefone = preg_replace('/\D/', '', $input['telefone'] ?? '');
        if (strlen($telefone) < 10 || strlen($telefone) > 11) {
            $errors['telefone'] = 'Telefone inválido.';
        }

        if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido.';
        }

        if (!in_array($input['perfil'] ?? '', ['VENDEDOR', 'SUPERVISOR', 'GERENTE'], true)) {
            $errors['perfil'] = 'Perfil inválido.';
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
            $errors['senha'] = 'Senha deve ter mínimo 8 caracteres.';
        } elseif ($input['senha'] !== ($input['senha_confirmacao'] ?? '')) {
            $errors['senha_confirmacao'] = 'As senhas não conferem.';
        }

        // Uploads de documento (frente obrigatória, verso opcional)
        $docFrente = $_FILES['doc_frente'] ?? null;
        if (!$docFrente || $docFrente['error'] !== UPLOAD_ERR_OK) {
            $errors['doc_frente'] = 'Documento (frente) obrigatório.';
        } else {
            $uploadError = $this->validarUpload($docFrente);
            if ($uploadError) $errors['doc_frente'] = $uploadError;
        }

        $docVerso = $_FILES['doc_verso'] ?? null;
        $docVersoUrl = null;
        if ($docVerso && $docVerso['error'] === UPLOAD_ERR_OK) {
            $uploadError = $this->validarUpload($docVerso);
            if ($uploadError) $errors['doc_verso'] = $uploadError;
        }

        if (!empty($errors)) {
            return [$errors, $input];
        }

        // Unicidade
        if ($this->model->findByCpf($cpf)) {
            $errors['cpf'] = 'CPF já cadastrado.';
        }
        if ($this->model->findByTelefone($telefone)) {
            $errors['telefone'] = 'Telefone já cadastrado.';
        }

        if (!empty($errors)) {
            return [$errors, $input];
        }

        // Faz uploads
        $docFrenteUrl = $this->moverUpload($docFrente, 'documentos');
        if (!$docFrenteUrl) {
            return [['doc_frente' => 'Falha ao salvar o arquivo. Tente novamente.'], $input];
        }

        if ($docVerso && $docVerso['error'] === UPLOAD_ERR_OK) {
            $docVersoUrl = $this->moverUpload($docVerso, 'documentos');
        }

        // Salva vendedor
        $vendedorId = $this->model->insert([
            'empresa_id'    => $empresaId,
            'nome_completo' => $input['nome_completo'],
            'cpf'           => $cpf,
            'rg'            => $input['rg'],
            'telefone'      => $telefone,
            'email'         => $input['email'] ?: null,
            'senha_hash'    => password_hash($input['senha'], PASSWORD_BCRYPT, ['cost' => 12]),
            'perfil'        => $input['perfil'],
            'cep'           => $cep,
            'endereco'      => $input['endereco'],
            'bairro'        => $input['bairro'] ?? '',
            'numero'        => $input['numero'] ?? '',
            'cidade'        => $input['cidade'],
            'uf'            => strtoupper($input['uf']),
            'doc_frente_url'=> $docFrenteUrl,
            'doc_verso_url' => $docVersoUrl,
            'status'        => 'PENDENTE_PAGAMENTO',
            'pix_status'    => 'PENDENTE',
        ]);

        log_action('VENDEDOR_CADASTRO', 'INFO', [
            'empresa_id'  => $empresaId,
            'vendedor_id' => $vendedorId,
            'cpf'         => $cpf,
            'perfil'      => $input['perfil'],
        ], $vendedorId);

        // Gera PIX de mensalidade
        $this->gerarPixVendedor($vendedorId, $input['nome_completo'], $cpf, $input['email'], $telefone);

        return [[], $input];
    }

    private function gerarPixVendedor(int $vendedorId, string $nome, string $cpf, string $email, string $telefone): void
    {
        $asaas = new AsaasService();

        if (!$asaas->isConfigured()) {
            $paymentId = 'pay_simulado_v_' . $vendedorId;
            $this->model->update($vendedorId, [
                'asaas_payment_id' => $paymentId,
            ]);
            $this->registrarPagamentoVendedor($vendedorId, 57.00, $paymentId, 'PENDING');
            log_action('PIX_VENDEDOR_SIMULADO', 'WARNING', ['vendedor_id' => $vendedorId]);
            return;
        }

        try {
            $emailAsaas = $email ?: $telefone . '@cib.local';
            $cliente    = $asaas->criarCliente($nome, $cpf, $emailAsaas, $telefone);
            $payment    = $asaas->criarCobrancaPix(
                $cliente['id'],
                57.00,
                'Mensalidade — Vendedor Concessionária Inteligente Bem',
                5
            );

            $this->model->update($vendedorId, [
                'asaas_payment_id' => $payment['id'],
            ]);
            $this->registrarPagamentoVendedor($vendedorId, 57.00, $payment['id'], $payment['status'] ?? 'PENDING', $payment['pixTransaction']['payload'] ?? null);

            log_action('PIX_VENDEDOR_GERADO', 'INFO', [
                'vendedor_id' => $vendedorId,
                'payment_id'  => $payment['id'],
            ]);
        } catch (RuntimeException $e) {
            log_action('PIX_VENDEDOR_ERRO', 'ERROR', [
                'vendedor_id' => $vendedorId,
                'erro'        => $e->getMessage(),
            ]);
        }
    }

    private function registrarPagamentoVendedor(int $vendedorId, float $valor, string $paymentId, string $status, ?string $pixPayload = null): void
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                "INSERT INTO pagamentos (entity_type, entity_id, valor, asaas_payment_id, pix_copia_cola, status)
                 VALUES ('VENDEDOR', ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$vendedorId, $valor, $paymentId, $pixPayload, $status]);
        } catch (Throwable $e) {
            log_action('PAGAMENTO_VENDEDOR_REGISTRO_ERRO', 'ERROR', [
                'vendedor_id' => $vendedorId,
                'payment_id'  => $paymentId,
                'erro'        => $e->getMessage(),
            ], $vendedorId);
        }
    }

    // ------------------------------------------------------------------
    // Dashboard do vendedor (requer login como VENDEDOR/SUPERVISOR/GERENTE)
    // ------------------------------------------------------------------

    public function dashboard(): void
    {
        $this->requireVendedor();

        $vendedorId = (int) $_SESSION['user_id'];
        $vendedor   = $this->model->findById($vendedorId);

        if (!$vendedor) {
            header('Location: /logout');
            exit;
        }

        $totalPropostas  = $this->model->totalPropostas($vendedorId);
        $propostasAtivas = $this->model->totalPropostasPorStatus($vendedorId, ['PENDENTE_SUPERVISOR','PENDENTE_GERENTE','ENVIADO_OPERADORA']);
        $comissaoEstimada = $this->model->comissaoEstimada($vendedorId);
        $ultimasPropostas = $this->model->ultimasPropostas($vendedorId);

        // Empresa vinculada
        $empresa = $vendedor['empresa_id']
            ? $this->empresaModel->findById((int) $vendedor['empresa_id'])
            : null;

        $csrf_token   = $_SESSION['csrf_token'];
        $is_logged_in = true;
        $user_type    = 'VENDEDOR';
        $page_title   = 'Meu Painel';
        $current_page = 'dashboard';

        // Flash message
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        ob_start();
        include APP_PATH . '/views/pages/vendedor/dashboard.php';
        $content = ob_get_clean();
        include APP_PATH . '/views/layouts/main.php';
    }

    // ------------------------------------------------------------------
    // Comissões do vendedor
    // ------------------------------------------------------------------

    public function comissoes(): void
    {
        $this->requireVendedor();

        $vendedorId = (int) $_SESSION['user_id'];
        $vendedor   = $this->model->findById($vendedorId);

        if (!$vendedor) {
            header('Location: /logout');
            exit;
        }

        $percentual           = 0.10;
        $comissaoEstimada     = $this->model->comissaoEstimada($vendedorId, $percentual);
        $propostasComissionadas = $this->model->propostasComissionadas($vendedorId, $percentual);

        $csrf_token   = $_SESSION['csrf_token'];
        $is_logged_in = true;
        $user_type    = 'VENDEDOR';
        $page_title   = 'Minhas Comissões';
        $current_page = 'comissoes';

        ob_start();
        include APP_PATH . '/views/pages/vendedor/comissoes.php';
        $content = ob_get_clean();
        include APP_PATH . '/views/layouts/main.php';
    }

    // ------------------------------------------------------------------
    // Webhook Asaas para vendedores (ativação pós-pagamento)
    // Reutiliza o endpoint /api/webhook/asaas do EmpresaController —
    // ou pode ser chamado internamente. Aqui tratamos via método separado.
    // ------------------------------------------------------------------

    public function ativarViaWebhook(string $paymentId): void
    {
        $vendedor = $this->model->findByAsaasPaymentId($paymentId);
        if ($vendedor && $vendedor['status'] === 'PENDENTE_PAGAMENTO') {
            $this->model->ativar($vendedor['id']);
            log_action('VENDEDOR_ATIVADO', 'INFO', [
                'vendedor_id' => $vendedor['id'],
                'payment_id'  => $paymentId,
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function requireEmpresa(): void
    {
        if (empty($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'EMPRESA') {
            header('Location: /login');
            exit;
        }
        $this->touchSession();
    }

    private function requireVendedor(): void
    {
        if (empty($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'VENDEDOR') {
            header('Location: /login');
            exit;
        }
        $this->touchSession();
    }

    private function touchSession(): void
    {
        if (!empty($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
            session_destroy();
            header('Location: /login?timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }

    private function sanitizeInput(array $post): array
    {
        $fields = ['nome_completo','cpf','rg','telefone','email','perfil',
                   'cep','endereco','bairro','numero','cidade','uf',
                   'senha','senha_confirmacao'];
        $out = [];
        foreach ($fields as $f) {
            $out[$f] = trim($post[$f] ?? '');
        }
        return $out;
    }

    private function validarCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) return false;

        $calc = function (string $cpf, int $len): int {
            $sum = 0;
            for ($i = 0; $i < $len; $i++) {
                $sum += (int)$cpf[$i] * ($len + 1 - $i);
            }
            $r = $sum % 11;
            return $r < 2 ? 0 : 11 - $r;
        };

        return (int)$cpf[9] === $calc($cpf, 9) && (int)$cpf[10] === $calc($cpf, 10);
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
            return 'Tipo não permitido. Use PDF, JPG ou PNG.';
        }
        return null;
    }

    private function moverUpload(array $file, string $subdir): ?string
    {
        $uploadDir = UPLOAD_PATH . $subdir . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $ext = match($mimeType) {
            'application/pdf'          => 'pdf',
            'image/jpeg', 'image/jpg'  => 'jpg',
            'image/png'                => 'png',
            default                    => 'bin',
        };

        $filename = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return null;
        }

        return '/storage/uploads/' . $subdir . '/' . $filename;
    }
}
