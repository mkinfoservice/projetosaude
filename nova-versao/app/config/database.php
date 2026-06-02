<?php
/**
 * app/config/database.php
 * Conexão PDO com MySQL — lê credenciais do .env
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

// Carrega .env se disponível
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Lê configurações
define('DB_HOST',    $_ENV['DB_HOST']    ?? 'localhost');
define('DB_NAME',    $_ENV['DB_NAME']    ?? '');
define('DB_USER',    $_ENV['DB_USER']    ?? '');
define('DB_PASS',    $_ENV['DB_PASS']    ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');
define('DB_PORT',    (int)($_ENV['DB_PORT'] ?? 3306));

/**
 * Retorna uma conexão PDO singleton
 */
function db(): PDO
{
    static $instance = null;

    if ($instance !== null) {
        return $instance;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );

    try {
        $instance = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_FOUND_ROWS   => true,
        ]);
    } catch (PDOException $e) {
        // Log sem expor credenciais
        error_log('[DB] Falha na conexão: ' . $e->getMessage());

        if (defined('APP_DEBUG') && APP_DEBUG) {
            throw new RuntimeException('Erro de conexão com o banco de dados: ' . $e->getMessage());
        }

        http_response_code(503);
        exit(json_encode(['success' => false, 'error' => 'Serviço temporariamente indisponível.']));
    }

    return $instance;
}
