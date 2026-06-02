<?php
// api/upload.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

$uploadDir = __DIR__ . '/../assets/uploads/';

// Criar pasta se não existir
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
$maxSize = 5 * 1024 * 1024; // 5MB

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(['error' => 'Erro no upload do arquivo'], 400);
}

$file = $_FILES['file'];

// Validar tipo
if (!in_array($file['type'], $allowedTypes)) {
    jsonResponse(['error' => 'Tipo de arquivo não permitido. Permitidos: JPG, PNG, PDF'], 400);
}

// Validar tamanho
if ($file['size'] > $maxSize) {
    jsonResponse(['error' => 'Arquivo muito grande. Máximo: 5MB'], 400);
}

// Gerar nome único
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$fileName = uniqid('CS_') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$filePath = $uploadDir . $fileName;

// Mover arquivo
if (move_uploaded_file($file['tmp_name'], $filePath)) {
    jsonResponse([
        'success' => true,
        'fileName' => $fileName,
        'filePath' => '/assets/uploads/' . $fileName,
        'originalName' => $file['name']
    ], 200);
} else {
    jsonResponse(['error' => 'Erro ao salvar arquivo no servidor'], 500);
}
?>