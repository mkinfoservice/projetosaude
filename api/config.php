<?php
// api/config.php
// ⚠️ Mova este arquivo para fora do public_html se possível, ou proteja com .htaccess

// Banco de Dados via variáveis de ambiente
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'app_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// Asaas API (Sandbox → Produção depois)
define('ASAAS_API_URL', getenv('ASAAS_API_URL') ?: 'https://api-sandbox.asaas.com/v3'); // Mude para api.asaas.com em produção
define('ASAAS_API_KEY', getenv('ASAAS_API_KEY') ?: '');           
define('ASAAS_WEBHOOK_TOKEN', getenv('ASAAS_WEBHOOK_TOKEN') ?: '');

// Configurações do Sistema
define('SITE_URL', 'https://cardsaudebank.com.br');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_TYPES', ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']);
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');

// Segurança
define('SESSION_LIFETIME', 3600); // 1 hora
define('CSRF_TOKEN_LENGTH', 32);

// Dados da Empresa para PIX
define('EMPRESA_CNPJ', '28.058.674/0001-66');
define('EMPRESA_NOME', 'NOMADE IMPORTACAO E EXPORTACAO LTDA');
define('EMPRESA_BANCO', '461');
define('EMPRESA_AGENCIA', '0001');
define('EMPRESA_CONTA', '6551844-1');

// Logs (fora do web root idealmente)
define('LOG_PATH', __DIR__ . '/../logs/');
define('LOG_ENABLED', true);

// Função de log seguro
function log_system($action, $data = [], $user_id = null) {
    if (!LOG_ENABLED) return;
    $log_file = LOG_PATH . 'system_' . date('Y-m-d') . '.log';
    $entry = [
        'timestamp' => date('c'),
        'action' => $action,
        'user_id' => $user_id,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'data' => $data
    ];
    file_put_contents($log_file, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Conexão PDO segura
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_SSL_CA => '/path/to/certificate.pem' // Locaweb pode exigir SSL
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        if (LOG_ENABLED) {
            error_log("DB Connection Error: " . $e->getMessage());
        }
        http_response_code(500);
        echo json_encode(['error' => 'Erro de conexão com o banco de dados']);
        exit;
    }
}
?>