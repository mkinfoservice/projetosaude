<?php
/**
 * app/controllers/AuthController.php
 * Login e logout
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

class AuthController
{
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => APP_ENV === 'production', // HTTPS só em produção
                'httponly'  => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }

    public function login(): void
    {
        $this->startSession();

        if (!empty($_SESSION['logged_in'])) {
            $this->redirectAfterLogin();
            return;
        }

        $error        = null;
        $csrf_token   = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        $_SESSION['csrf_token'] = $csrf_token;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = $this->processLogin($csrf_token);
        }

        $page_title   = 'Login | Concessionária Inteligente Bem';
        $current_page = 'login';
        $is_logged_in = false;
        $user_type    = null;

        ob_start();
        include APP_PATH . '/views/pages/login.php';
        $content = ob_get_clean();

        include APP_PATH . '/views/layouts/main.php';
    }

    private function processLogin(string $csrf_token): ?string
    {
        $submitted_csrf = $_POST['csrf_token'] ?? '';
        if (!hash_equals($csrf_token, $submitted_csrf)) {
            return 'Token de segurança inválido. Atualize a página e tente novamente.';
        }

        $tipo       = $_POST['tipo']       ?? '';
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password']   ?? '';

        if (!$identifier || !$password || !$tipo) {
            return 'Preencha todos os campos.';
        }

        $pdo = db();

        if ($tipo === 'empresa') {
            $email = filter_var($identifier, FILTER_VALIDATE_EMAIL);
            if (!$email) return 'E-mail inválido.';

            $stmt = $pdo->prepare("SELECT id, razao_social, senha_hash, status FROM empresas WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['senha_hash'])) {
                log_action('LOGIN_FAILED_EMPRESA', 'WARNING', ['email' => $email, 'reason' => 'invalid_credentials']);
                return 'E-mail ou senha inválidos.';
            }

            if ($user['status'] !== 'ATIVO') {
                return $user['status'] === 'PENDENTE_PAGAMENTO'
                    ? 'Conta pendente de pagamento. Conclua o PIX para ativar.'
                    : 'Conta ' . strtolower($user['status']) . '. Entre em contato com o suporte.';
            }

            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = 'EMPRESA';
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_data'] = ['razao_social' => $user['razao_social']];
            $_SESSION['last_activity'] = time();

            log_action('LOGIN_SUCCESS_EMPRESA', 'INFO', ['email' => $email], $user['id']);

            if (password_needs_rehash($user['senha_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
                $pdo->prepare("UPDATE empresas SET senha_hash = ? WHERE id = ?")
                    ->execute([password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $user['id']]);
            }

            header('Location: /admin/empresa');
            exit;
        }

        if ($tipo === 'vendedor') {
            $telefone = preg_replace('/\D/', '', $identifier);
            if (strlen($telefone) < 10) return 'Telefone inválido.';

            $stmt = $pdo->prepare("
                SELECT v.id, v.nome_completo, v.senha_hash, v.status, v.perfil, v.empresa_id
                FROM vendedores v
                WHERE v.telefone = ? LIMIT 1
            ");
            $stmt->execute([$telefone]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['senha_hash'])) {
                log_action('LOGIN_FAILED_VENDEDOR', 'WARNING', ['telefone' => $telefone, 'reason' => 'invalid_credentials']);
                return 'Telefone ou senha inválidos.';
            }

            if ($user['status'] !== 'ATIVO') {
                return $user['status'] === 'PENDENTE_PAGAMENTO'
                    ? 'Conta pendente de pagamento.'
                    : 'Conta ' . strtolower($user['status']) . '.';
            }

            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            $_SESSION['user_type'] = 'VENDEDOR';
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_data'] = [
                'nome'       => $user['nome_completo'],
                'perfil'     => $user['perfil'],
                'empresa_id' => $user['empresa_id'],
            ];
            $_SESSION['last_activity'] = time();

            log_action('LOGIN_SUCCESS_VENDEDOR', 'INFO', ['telefone' => $telefone, 'perfil' => $user['perfil']], $user['id']);

            // Atualiza último acesso
            $pdo->prepare("UPDATE vendedores SET ultimo_acesso = NOW() WHERE id = ?")
                ->execute([$user['id']]);

            if (password_needs_rehash($user['senha_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
                $pdo->prepare("UPDATE vendedores SET senha_hash = ? WHERE id = ?")
                    ->execute([password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $user['id']]);
            }

            $this->redirectAfterLogin();
        }

        return 'Tipo de acesso inválido.';
    }

    private function redirectAfterLogin(): void
    {
        $perfil = $_SESSION['user_data']['perfil'] ?? '';
        $type   = $_SESSION['user_type'] ?? '';

        if ($type === 'EMPRESA') {
            header('Location: /admin/empresa'); exit;
        }

        $redirects = [
            'SUPERVISOR' => '/admin/supervisor',
            'GERENTE'    => '/admin/gerente',
            'ADMIN'      => '/admin/supervisor',
            'VENDEDOR'   => '/admin/vendedor',
        ];

        header('Location: ' . ($redirects[$perfil] ?? '/admin/vendedor'));
        exit;
    }

    public function logout(): void
    {
        $this->startSession();

        $user_id = $_SESSION['user_id'] ?? null;
        log_action('LOGOUT', 'INFO', [], $user_id);

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        header('Location: /');
        exit;
    }
}
