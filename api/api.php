<?php
// =============================================================
// api/config.php
// Configuração de Conexão com Banco de Dados - Locaweb
// =============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Tratamento de preflight CORS (requisições OPTIONS do navegador)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// =============================================================
// CONFIGURAÇÕES DO BANCO DE DADOS — ALTERE PARA SEUS DADOS
// =============================================================
define('DB_HOST',    'cardsaude_db.mysql.dbaas.com.br'); // Host Locaweb
define('DB_NAME',    'cardsaude_db');
define('DB_USER',    'cardsaude_db');      // <- ALTERAR
define('DB_PASS',    'L91718104Ruth@');    // <- ALTERAR (nunca commitar senha real)
define('DB_CHARSET', 'utf8mb4');

// =============================================================
// CONFIGURAÇÕES DA API
// =============================================================
define('API_VERSION', '1.0.0');
define('API_DEBUG',   false); // true apenas em ambiente de desenvolvimento

// Chave secreta para assinar os tokens JWT — use uma string longa e aleatória
define('JWT_SECRET', 'TROQUE_POR_UMA_CHAVE_SECRETA_LONGA_E_ALEATORIA');

// Tempo de expiração do token JWT (em segundos) — padrão: 8 horas
define('JWT_EXPIRATION', 28800);

// =============================================================
// CONEXÃO COM O BANCO
// =============================================================

/**
 * Retorna uma conexão MySQLi ativa.
 * Encerra a execução com erro 500 caso a conexão falhe.
 */
function getDBConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        jsonResponse(null, 500, 'Erro de conexão com o banco de dados.');
    }

    $conn->set_charset(DB_CHARSET);
    return $conn;
}

// =============================================================
// UTILITÁRIOS GERAIS
// =============================================================

/**
 * Sanitiza entrada do usuário recursivamente.
 * Remove tags HTML e espaços extras.
 */
function sanitizeInput(mixed $data): mixed {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Retorna uma resposta JSON padronizada e encerra a execução.
 *
 * @param mixed  $data    Dados a retornar (null em caso de erro)
 * @param int    $status  HTTP status code
 * @param string $message Mensagem descritiva
 */
function jsonResponse(mixed $data, int $status = 200, string $message = 'OK'): void {
    http_response_code($status);
    echo json_encode([
        'success'   => $status >= 200 && $status < 300,
        'message'   => $message,
        'data'      => $data,
        'timestamp' => date('c'),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Registra uma ação no log de auditoria.
 *
 * @param mysqli $conn
 * @param string $action  Nome da ação (ex: 'login', 'create_user')
 * @param string $userId  ID do usuário que executou a ação
 * @param array  $details Dados adicionais em formato array
 */
function logAction(mysqli $conn, string $action, string $userId, array $details = []): void {
    $stmt = $conn->prepare(
        "INSERT INTO logs (action, user_id, details, created_at) VALUES (?, ?, ?, NOW())"
    );
    $detailsJson = json_encode($details, JSON_UNESCAPED_UNICODE);
    $stmt->bind_param('sss', $action, $userId, $detailsJson);
    $stmt->execute();
    $stmt->close();
}

// =============================================================
// VALIDAÇÕES
// =============================================================

/**
 * Valida um CPF brasileiro.
 */
function validateCPF(string $cpf): bool {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1+$/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $sum = 0;
        for ($i = 0; $i < $t; $i++) {
            $sum += intval($cpf[$i]) * ($t + 1 - $i);
        }
        $remainder = ($sum * 10) % 11;
        if ($remainder === 10 || $remainder === 11) $remainder = 0;
        if ($remainder !== intval($cpf[$t])) return false;
    }

    return true;
}

/**
 * Valida um CNPJ brasileiro (apenas tamanho — implemente dígitos se necessário).
 */
function validateCNPJ(string $cnpj): bool {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    return strlen($cnpj) === 14;
}

// =============================================================
// UPLOAD DE ARQUIVOS
// =============================================================

/**
 * Processa o upload de um arquivo enviado via formulário.
 *
 * @param string   $fileInput    Nome do campo no $_FILES
 * @param string[] $allowedTypes Tipos MIME aceitos
 * @param int      $maxSize      Tamanho máximo em bytes (padrão: 5 MB)
 * @return array   ['success', 'filename', 'path'] ou ['error']
 */
function handleFileUpload(
    string $fileInput,
    array  $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'],
    int    $maxSize = 5242880
): array {
    if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Erro no upload do arquivo.'];
    }

    $file = $_FILES[$fileInput];

    if ($file['size'] > $maxSize) {
        return ['error' => 'Arquivo muito grande. Máximo permitido: 5 MB.'];
    }

    if (!in_array($file['type'], $allowedTypes, true)) {
        return ['error' => 'Tipo de arquivo não permitido.'];
    }

    $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename  = uniqid('file_', true) . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success'       => true,
            'filename'      => $filename,
            'original_name' => $file['name'],
            'path'          => '/uploads/' . $filename,
        ];
    }

    return ['error' => 'Falha ao salvar o arquivo no servidor.'];
}