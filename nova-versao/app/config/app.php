<?php
/**
 * app/config/app.php
 * Configurações globais da aplicação
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

// Carrega variáveis de ambiente (executado após database.php ou standalone)
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile) && !defined('APP_ENV')) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = $_ENV[trim($key)] ?? trim($value);
        }
    }
}

// Ambiente
define('APP_ENV',   $_ENV['APP_ENV']   ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL',   rtrim($_ENV['APP_URL'] ?? 'https://localhost', '/'));
define('APP_SECRET', $_ENV['APP_SECRET'] ?? 'chave_padrao_insegura_troque_no_env');

// Sessão
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 3600));
define('CSRF_TOKEN_LENGTH', 32);

// Uploads
define('UPLOAD_MAX_SIZE', (int)($_ENV['UPLOAD_MAX_SIZE_MB'] ?? 10) * 1024 * 1024);
define('UPLOAD_PATH',     dirname(__DIR__, 2) . '/storage/uploads/');
define('UPLOAD_ALLOWED_TYPES', ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp']);

// Asaas
define('ASAAS_API_URL',       rtrim($_ENV['ASAAS_API_URL'] ?? 'https://api-sandbox.asaas.com/v3', '/'));
define('ASAAS_API_KEY',       $_ENV['ASAAS_API_KEY'] ?? '');
define('ASAAS_WEBHOOK_TOKEN', $_ENV['ASAAS_WEBHOOK_TOKEN'] ?? '');

// Dados da empresa para PIX
define('EMPRESA_CNPJ',    $_ENV['EMPRESA_CNPJ']    ?? '');
define('EMPRESA_NOME',    $_ENV['EMPRESA_NOME']    ?? 'Concessionária Inteligente Bem');
define('EMPRESA_BANCO',   $_ENV['EMPRESA_BANCO']   ?? '');
define('EMPRESA_AGENCIA', $_ENV['EMPRESA_AGENCIA'] ?? '');
define('EMPRESA_CONTA',   $_ENV['EMPRESA_CONTA']   ?? '');

// Logs
define('LOG_ENABLED', filter_var($_ENV['LOG_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('LOG_PATH',    dirname(__DIR__, 2) . '/storage/logs/');

// Garante que diretórios de armazenamento existam
foreach ([UPLOAD_PATH, LOG_PATH] as $_storageDir) {
    if (!is_dir($_storageDir)) {
        @mkdir($_storageDir, 0755, true);
    }
}
unset($_storageDir);

// PHP config
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

date_default_timezone_set('America/Sao_Paulo');

/**
 * Registra uma ação no log do sistema
 */
function log_action(string $acao, string $nivel = 'INFO', array $dados = [], ?int $usuario_id = null): void
{
    if (!LOG_ENABLED) return;

    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO logs (acao, nivel, usuario_id, ip, dados)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $acao,
            $nivel,
            $usuario_id,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            !empty($dados) ? json_encode($dados, JSON_UNESCAPED_UNICODE) : null
        ]);
    } catch (Throwable $e) {
        // Fallback para arquivo de log se BD falhar
        $logFile = LOG_PATH . 'fallback_' . date('Y-m-d') . '.log';
        $entry = date('c') . " [$nivel] $acao " . json_encode($dados) . PHP_EOL;
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Gera resposta JSON e encerra
 */
function json_response(bool $success, array $data = [], string $error = '', int $httpCode = 200): never
{
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');

    $response = ['success' => $success];
    if (!empty($data))  $response['data'] = $data;
    if (!empty($error)) $response['error'] = $error;

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

/**
 * Escapa string para HTML
 */
function h(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Formata valor em BRL
 */
function money(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Retorna faixa etária baseada na idade
 */
function faixa_etaria(int $idade): string
{
    if ($idade <= 18) return '0-18';
    if ($idade <= 28) return '19-28';
    if ($idade <= 43) return '29-43';
    if ($idade <= 58) return '44-58';
    return '59+';
}

/**
 * Calcula idade a partir da data de nascimento
 */
function calcular_idade(string $nascimento): int
{
    try {
        $dt = new DateTime($nascimento);
        return (int) $dt->diff(new DateTime())->y;
    } catch (Exception) {
        return 0;
    }
}

/**
 * Formata data para padrão brasileiro
 */
function data_br(string $date, bool $hora = false): string
{
    if (empty($date)) return '-';
    try {
        $dt = new DateTime($date);
        return $hora ? $dt->format('d/m/Y H:i') : $dt->format('d/m/Y');
    } catch (Exception) {
        return '-';
    }
}
