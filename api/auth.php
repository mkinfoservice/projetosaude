<?php
/**
 * api/Auth.php
 * Sistema de autenticação e sessões
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

class Auth {
    private $pdo;
    
    public function __construct($pdo = null) {
        $this->pdo = $pdo ?? db();
        $this->startSession();
    }
    
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'secure' => true, // HTTPS apenas
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
    }
    
    // ===== HASH DE SENHA =====
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function needsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    // ===== CSRF =====
    
    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }
    
    public function validateCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) return false;
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public function refreshCsrfToken() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        return $_SESSION['csrf_token'];
    }
    
    // ===== LOGIN =====
    
    public function loginEmpresa($email, $password) {
        try {
            $email = filter_var($email, FILTER_VALIDATE_EMAIL);
            if (!$email) return ['success' => false, 'message' => 'E-mail inválido'];
            
            $stmt = $this->pdo->prepare("
                SELECT id, cnpj, razao_social, senha_hash, status, pix_status 
                FROM empresas WHERE email = ? LIMIT 1
            ");
            $stmt->execute([$email]);
            $empresa = $stmt->fetch();
            
            if (!$empresa) {
                log_system('WARNING', 'LOGIN_FAILED_EMPRESA', ['email' => $email, 'reason' => 'not_found']);
                return ['success' => false, 'message' => 'E-mail ou senha inválidos'];
            }
            
            if (!$this->verifyPassword($password, $empresa['senha_hash'])) {
                log_system('WARNING', 'LOGIN_FAILED_EMPRESA', ['email' => $email, 'reason' => 'wrong_password']);
                return ['success' => false, 'message' => 'E-mail ou senha inválidos'];
            }
            
            if ($empresa['status'] !== 'ATIVO') {
                $msg = $empresa['status'] === 'PENDENTE_PAGAMENTO' 
                    ? 'Conta pendente de pagamento. Verifique seu PIX.' 
                    : 'Conta ' . strtolower($empresa['status']) . '. Contate o suporte.';
                return ['success' => false, 'message' => $msg];
            }
            
            // Login sucesso
            $this->setUserSession('EMPRESA', $empresa['id'], [
                'cnpj' => $empresa['cnpj'],
                'razao_social' => $empresa['razao_social']
            ]);
            
            log_system('INFO', 'LOGIN_SUCCESS_EMPRESA', ['email' => $email], $empresa['id']);
            
            // Rehash se necessário
            if ($this->needsRehash($empresa['senha_hash'])) {
                $newHash = $this->hashPassword($password);
                $stmt = $this->pdo->prepare("UPDATE empresas SET senha_hash = ? WHERE id = ?");
                $stmt->execute([$newHash, $empresa['id']]);
            }
            
            return [
                'success' => true, 
                'redirect' => '/admin/empresa.php',
                'user' => ['id' => $empresa['id'], 'cnpj' => $empresa['cnpj']]
            ];
            
        } catch (Exception $e) {
            log_system('ERROR', 'LOGIN_ERROR_EMPRESA', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro interno. Tente novamente.'];
        }
    }
    
    public function loginVendedor($telefone, $password) {
        try {
            $telefone = preg_replace('/\D/', '', $telefone);
            if (strlen($telefone) < 10) return ['success' => false, 'message' => 'Telefone inválido'];
            
            $stmt = $this->pdo->prepare("
                SELECT v.id, v.nome_completo, v.telefone, v.senha_hash, v.status, v.perfil, v.empresa_id, e.razao_social
                FROM vendedores v
                LEFT JOIN empresas e ON v.empresa_id = e.id
                WHERE v.telefone = ? LIMIT 1
            ");
            $stmt->execute([$telefone]);
            $vendedor = $stmt->fetch();
            
            if (!$vendedor) {
                log_system('WARNING', 'LOGIN_FAILED_VENDEDOR', ['telefone' => $telefone, 'reason' => 'not_found']);
                return ['success' => false, 'message' => 'Telefone ou senha inválidos'];
            }
            
            if (!$this->verifyPassword($password, $vendedor['senha_hash'])) {
                log_system('WARNING', 'LOGIN_FAILED_VENDEDOR', ['telefone' => $telefone, 'reason' => 'wrong_password']);
                return ['success' => false, 'message' => 'Telefone ou senha inválidos'];
            }
            
            if ($vendedor['status'] !== 'ATIVO') {
                $msg = $vendedor['status'] === 'PENDENTE_PAGAMENTO'
                    ? 'Conta pendente de pagamento. Verifique seu PIX.'
                    : 'Conta ' . strtolower($vendedor['status']) . '. Contate o suporte.';
                return ['success' => false, 'message' => $msg];
            }
            
            // Login sucesso
            $this->setUserSession('VENDEDOR', $vendedor['id'], [
                'nome' => $vendedor['nome_completo'],
                'perfil' => $vendedor['perfil'],
                'empresa_id' => $vendedor['empresa_id'],
                'razao_social' => $vendedor['razao_social']
            ]);
            
            log_system('INFO', 'LOGIN_SUCCESS_VENDEDOR', ['telefone' => $telefone], $vendedor['id']);
            
            // Rehash se necessário
            if ($this->needsRehash($vendedor['senha_hash'])) {
                $newHash = $this->hashPassword($password);
                $stmt = $this->pdo->prepare("UPDATE vendedores SET senha_hash = ? WHERE id = ?");
                $stmt->execute([$newHash, $vendedor['id']]);
            }
            
            $redirects = [
                'SUPERVISOR' => '/admin/supervisor.php',
                'GERENTE' => '/admin/gerente.php',
                'VENDEDOR' => '/admin/vendedor.php'
            ];
            
            return [
                'success' => true,
                'redirect' => $redirects[$vendedor['perfil']] ?? '/admin/vendedor.php',
                'user' => [
                    'id' => $vendedor['id'],
                    'nome' => $vendedor['nome_completo'],
                    'perfil' => $vendedor['perfil']
                ]
            ];
            
        } catch (Exception $e) {
            log_system('ERROR', 'LOGIN_ERROR_VENDEDOR', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro interno. Tente novamente.'];
        }
    }
    
    private function setUserSession($type, $id, $extra = []) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_type'] = $type;
        $_SESSION['user_id'] = $id;
        $_SESSION['user_data'] = $extra;
        $_SESSION['last_activity'] = time();
        
        // Regenerar ID da sessão para prevenir fixation
        session_regenerate_id(true);
    }
    
    // ===== VERIFICAÇÕES =====
    
    public function isLoggedIn() {
        if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
            return false;
        }
        
        // Verificar timeout
        if (time() - ($_SESSION['last_activity'] ?? 0) > SESSION_LIFETIME) {
            $this->logout();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public function requireLogin($allowedTypes = []) {
        if (!$this->isLoggedIn()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                json_response(false, [], 'Não autenticado', 401);
            }
            header('Location: /index.php?login_required=1');
            exit;
        }
        
        if (!empty($allowedTypes) && !in_array($_SESSION['user_type'], $allowedTypes)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                json_response(false, [], 'Acesso não autorizado', 403);
            }
            http_response_code(403);
            echo "Acesso não autorizado";
            exit;
        }
    }
    
    public function getUserData() {
        return $_SESSION['user_data'] ?? [];
    }
    
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function getUserType() {
        return $_SESSION['user_type'] ?? null;
    }
    
    // ===== LOGOUT =====
    
    public function logout() {
        $user_id = $_SESSION['user_id'] ?? null;
        $user_type = $_SESSION['user_type'] ?? null;
        
        log_system('INFO', 'LOGOUT', [], $user_id);
        
        // Limpar sessão
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    // ===== RECUPERAÇÃO DE SENHA (Básico) =====
    
    public function requestPasswordReset($email, $type = 'empresa') {
        $table = $type === 'empresa' ? 'empresas' : 'vendedores';
        $field = $type === 'empresa' ? 'email' : 'telefone';
        
        $stmt = $this->pdo->prepare("SELECT id FROM $table WHERE $field = ? LIMIT 1");
        $stmt->execute([$email]);
        
        if (!$stmt->fetch()) {
            // Não revelar se o usuário existe (segurança)
            return ['success' => true, 'message' => 'Se o cadastro existir, você receberá instruções.'];
        }
        
        // Gerar token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Salvar token (tabela password_resets necessária)
        // Implementação completa requer tabela adicional
        
        log_system('INFO', 'PASSWORD_RESET_REQUESTED', ['email' => $email, 'type' => $type]);
        
        return ['success' => true, 'message' => 'Instruções enviadas (simulado).'];
    }
}
?>
