<?php
/**
 * app/controllers/PropostaController.php
 * Criação e visualização de propostas pelo vendedor
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

require_once APP_PATH . '/models/PropostaModel.php';
require_once APP_PATH . '/models/ClienteModel.php';
require_once APP_PATH . '/models/VendedorModel.php';

class PropostaController
{
    private PropostaModel $model;
    private ClienteModel  $clienteModel;
    private VendedorModel $vendedorModel;

    public function __construct()
    {
        $this->model         = new PropostaModel();
        $this->clienteModel  = new ClienteModel();
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
    // Nova proposta
    // ------------------------------------------------------------------

    public function nova(): void
    {
        $this->requireVendedor();

        $vendedorId = (int) $_SESSION['user_id'];
        $vendedor   = $this->vendedorModel->findById($vendedorId);

        $pdo        = db();
        $operadoras = $pdo->query("SELECT id, nome, slug FROM operadoras WHERE ativo = 1 ORDER BY nome")->fetchAll();
        $planos     = $pdo->query("SELECT p.id, p.operadora_id, p.nome, p.categoria, p.cobertura,
                                         p.faixa_0_18, p.faixa_19_28, p.faixa_29_43, p.faixa_44_58, p.faixa_59_plus
                                  FROM planos p
                                  JOIN operadoras o ON o.id = p.operadora_id
                                  WHERE p.ativo = 1 AND o.ativo = 1
                                  ORDER BY p.operadora_id, p.nome")->fetchAll();

        $errors     = [];
        $input      = [];
        $csrf_token = $_SESSION['csrf_token'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$errors, $input, $protocolo] = $this->processarProposta($csrf_token, $vendedor);

            if (empty($errors)) {
                $_SESSION['flash'] = "Proposta $protocolo enviada! Aguardando análise do supervisor.";
                header('Location: /admin/vendedor/propostas');
                exit;
            }
        }

        $page_title   = 'Nova Proposta';
        $current_page = 'nova-proposta';
        $is_logged_in = true;
        $user_type    = 'VENDEDOR';

        ob_start();
        include APP_PATH . '/views/pages/proposta/nova.php';
        $content = ob_get_clean();
        include APP_PATH . '/views/layouts/main.php';
    }

    private function processarProposta(string $csrf_token, array $vendedor): array
    {
        $errors = [];
        $input  = $this->sanitizeInput($_POST);

        if (!hash_equals($csrf_token, $_POST['csrf_token'] ?? '')) {
            return [['csrf' => 'Token inválido.'], $input, null];
        }

        // Valida beneficiário
        if (empty($input['nome_completo']) || strlen($input['nome_completo']) < 5) {
            $errors['nome_completo'] = 'Nome completo obrigatório.';
        }

        $cpf = preg_replace('/\D/', '', $input['cpf'] ?? '');
        if (!$this->validarCpf($cpf)) {
            $errors['cpf'] = 'CPF inválido.';
        }

        if (empty($input['data_nascimento'])) {
            $errors['data_nascimento'] = 'Data de nascimento obrigatória.';
        }

        if (empty($input['telefone']) || strlen(preg_replace('/\D/', '', $input['telefone'])) < 10) {
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

        // Valida plano
        $operadoraId = (int)($input['operadora_id'] ?? 0);
        $planoId     = (int)($input['plano_id'] ?? 0);
        $categoria   = $input['categoria'] ?? '';

        if (!$operadoraId) {
            $errors['operadora_id'] = 'Selecione uma operadora.';
        }

        if (!$planoId) {
            $errors['plano_id'] = 'Selecione um plano.';
        }

        if (!in_array($categoria, ['SEM_COPARTICIPACAO','COM_COPARTICIPACAO'], true)) {
            $errors['categoria'] = 'Categoria inválida.';
        }

        $qtdVidas = max(1, (int)($input['quantidade_vidas'] ?? 1));

        // Valida uploads (frente obrigatória)
        $docFrente = $_FILES['doc_frente'] ?? null;
        if (!$docFrente || $docFrente['error'] !== UPLOAD_ERR_OK) {
            $errors['doc_frente'] = 'Documento de identidade (frente) obrigatório.';
        } else {
            $err = $this->validarUpload($docFrente);
            if ($err) $errors['doc_frente'] = $err;
        }

        if (!empty($errors)) {
            return [$errors, $input, null];
        }

        // Busca ou cria cliente
        $clienteExistente = $this->clienteModel->findByCpf($cpf);
        $clienteData = [
            'nome_completo'  => $input['nome_completo'],
            'cpf'            => $cpf,
            'rg'             => $input['rg'] ?? null,
            'data_nascimento'=> $input['data_nascimento'],
            'sexo'           => in_array($input['sexo']??'', ['M','F','OUTRO']) ? $input['sexo'] : null,
            'estado_civil'   => $input['estado_civil'] ?? null,
            'nome_mae'       => $input['nome_mae'] ?? null,
            'email'          => filter_var($input['email']??'', FILTER_VALIDATE_EMAIL) ?: null,
            'telefone'       => preg_replace('/\D/', '', $input['telefone']),
            'cep'            => $cep,
            'endereco'       => $input['endereco'],
            'bairro'         => $input['bairro'] ?? null,
            'numero'         => $input['numero'] ?? null,
            'complemento'    => $input['complemento'] ?? null,
            'cidade'         => $input['cidade'],
            'uf'             => strtoupper($input['uf']),
            'profissao'      => $input['profissao'] ?? null,
        ];

        if ($clienteExistente) {
            $this->clienteModel->update($clienteExistente['id'], $clienteData);
            $clienteId = $clienteExistente['id'];
        } else {
            $clienteId = $this->clienteModel->insert($clienteData);
        }

        // Uploads
        $docFrenteUrl    = $this->moverUpload($docFrente, 'propostas');
        $docVersoUrl     = null;
        $docResidenciaUrl= null;
        $docContraUrl    = null;

        if (isset($_FILES['doc_verso']) && $_FILES['doc_verso']['error'] === UPLOAD_ERR_OK) {
            $docVersoUrl = $this->moverUpload($_FILES['doc_verso'], 'propostas');
        }
        if (isset($_FILES['doc_residencia']) && $_FILES['doc_residencia']['error'] === UPLOAD_ERR_OK) {
            $docResidenciaUrl = $this->moverUpload($_FILES['doc_residencia'], 'propostas');
        }
        if (isset($_FILES['doc_contracheque']) && $_FILES['doc_contracheque']['error'] === UPLOAD_ERR_OK) {
            $docContraUrl = $this->moverUpload($_FILES['doc_contracheque'], 'propostas');
        }

        // Calcula valor estimado pela faixa etaria de cada vida cotada.
        $pdo   = db();
        $plano = $pdo->prepare("
            SELECT faixa_0_18, faixa_19_28, faixa_29_43, faixa_44_58, faixa_59_plus
            FROM planos
            WHERE id = ?
            LIMIT 1
        ");
        $plano->execute([$planoId]);
        $planoValores = $plano->fetch() ?: [];
        $idadeTitular = $this->calcularIdade($input['data_nascimento']);
        $idadesVidas = $this->parseIdadesVidas($input['idades_vidas'] ?? '');
        if (empty($idadesVidas)) {
            $idadesVidas = [$idadeTitular];
            for ($i = 1; $i < $qtdVidas; $i++) {
                $idadesVidas[] = $idadeTitular;
            }
        }

        $qtdVidas = max(1, count($idadesVidas));
        $valorTotal = 0.0;
        foreach ($idadesVidas as $idadeVida) {
            $valorTotal += (float)($planoValores[$this->colunaFaixaEtaria($idadeVida)] ?? 0);
        }

        $observacoes = trim($input['observacoes'] ?? '');
        $observacoes .= ($observacoes !== '' ? "\n" : '') . 'Idades cotadas: ' . implode(', ', $idadesVidas);

        $protocolo = $this->model->gerarProtocolo();

        $this->model->insert([
            'protocolo'          => $protocolo,
            'cliente_id'         => $clienteId,
            'vendedor_id'        => (int)$vendedor['id'],
            'empresa_id'         => $vendedor['empresa_id'] ?? null,
            'operadora_id'       => $operadoraId,
            'plano_id'           => $planoId,
            'categoria'          => $categoria,
            'quantidade_vidas'   => $qtdVidas,
            'valor_total'        => $valorTotal,
            'status'             => 'PENDENTE_SUPERVISOR',
            'observacoes'        => $observacoes,
            'doc_frente_url'     => $docFrenteUrl,
            'doc_verso_url'      => $docVersoUrl,
            'doc_residencia_url' => $docResidenciaUrl,
            'doc_contracheque_url'=> $docContraUrl,
        ]);

        log_action('PROPOSTA_CRIADA', 'INFO', [
            'protocolo'   => $protocolo,
            'vendedor_id' => $vendedor['id'],
            'cliente_cpf' => $cpf,
        ], (int)$vendedor['id']);

        return [[], $input, $protocolo];
    }

    // ------------------------------------------------------------------
    // Lista de propostas do vendedor
    // ------------------------------------------------------------------

    public function lista(): void
    {
        $this->requireVendedor();

        $vendedorId = (int) $_SESSION['user_id'];
        $propostas  = $this->model->listarComFiltros(['vendedor_id' => $vendedorId]);

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $csrf_token   = $_SESSION['csrf_token'];
        $is_logged_in = true;
        $user_type    = 'VENDEDOR';
        $page_title   = 'Minhas Propostas';
        $current_page = 'propostas';

        ob_start();
        include APP_PATH . '/views/pages/proposta/lista.php';
        $content = ob_get_clean();
        include APP_PATH . '/views/layouts/main.php';
    }

    // ------------------------------------------------------------------
    // Detalhe da proposta
    // ------------------------------------------------------------------

    public function show(): void
    {
        $this->requireVendedor();

        $id      = (int)($_GET['id'] ?? 0);
        $proposta = $this->model->findCompleto($id);

        if (!$proposta) {
            http_response_code(404);
            $proposta = null;
        }

        // Garante que o vendedor só vê as próprias propostas (exceto supervisor/gerente)
        $perfil = $_SESSION['user_data']['perfil'] ?? '';
        if ($proposta && (int)$proposta['vendedor_id'] !== (int)$_SESSION['user_id']
            && !in_array($perfil, ['SUPERVISOR','GERENTE','ADMIN'], true)) {
            http_response_code(403);
            $proposta = null;
        }

        $csrf_token   = $_SESSION['csrf_token'];
        $is_logged_in = true;
        $user_type    = 'VENDEDOR';
        $page_title   = $proposta ? 'Proposta ' . $proposta['protocolo'] : 'Proposta não encontrada';
        $current_page = 'propostas';

        ob_start();
        include APP_PATH . '/views/pages/proposta/show.php';
        $content = ob_get_clean();
        include APP_PATH . '/views/layouts/main.php';
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function requireVendedor(): void
    {
        if (empty($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'VENDEDOR') {
            header('Location: /login');
            exit;
        }
        if (!empty($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
            session_destroy();
            header('Location: /login?timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }

    private function sanitizeInput(array $post): array
    {
        $fields = ['nome_completo','cpf','rg','data_nascimento','sexo','estado_civil',
                   'nome_mae','email','telefone','cep','endereco','bairro','numero',
                   'complemento','cidade','uf','profissao','operadora_id','plano_id',
                   'categoria','quantidade_vidas','idades_vidas','observacoes'];
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
            for ($i = 0; $i < $len; $i++) $sum += (int)$cpf[$i] * ($len + 1 - $i);
            $r = $sum % 11;
            return $r < 2 ? 0 : 11 - $r;
        };
        return (int)$cpf[9] === $calc($cpf, 9) && (int)$cpf[10] === $calc($cpf, 10);
    }

    private function calcularIdade(string $dataNascimento): int
    {
        try {
            $nascimento = new DateTime($dataNascimento);
            return (int)$nascimento->diff(new DateTime('today'))->y;
        } catch (Throwable) {
            return 0;
        }
    }

    private function colunaFaixaEtaria(int $idade): string
    {
        return match (true) {
            $idade <= 18 => 'faixa_0_18',
            $idade <= 28 => 'faixa_19_28',
            $idade <= 43 => 'faixa_29_43',
            $idade <= 58 => 'faixa_44_58',
            default => 'faixa_59_plus',
        };
    }

    private function parseIdadesVidas(string $idades): array
    {
        $out = [];
        foreach (preg_split('/[,\s;]+/', $idades) ?: [] as $idade) {
            if ($idade === '' || !preg_match('/^\d{1,3}$/', $idade)) {
                continue;
            }
            $idade = (int)$idade;
            if ($idade >= 0 && $idade <= 120) {
                $out[] = $idade;
            }
        }
        return $out;
    }

    private function validarUpload(array $file): ?string
    {
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return 'Arquivo muito grande. Máximo: ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . 'MB.';
        }
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mime     = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, ['application/pdf','image/jpeg','image/png','image/jpg'], true)) {
            return 'Tipo não permitido. Use PDF, JPG ou PNG.';
        }
        return null;
    }

    private function moverUpload(array $file, string $subdir): ?string
    {
        $dir = UPLOAD_PATH . $subdir . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $ext  = match($mime) {
            'application/pdf'         => 'pdf',
            'image/jpeg','image/jpg'  => 'jpg',
            'image/png'               => 'png',
            default                   => 'bin',
        };
        $name = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $dir . $name);
        return '/storage/uploads/' . $subdir . '/' . $name;
    }
}
