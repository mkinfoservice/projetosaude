<?php
// api/upload.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = '../assets/uploads/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!isset($_FILES['file'])) {
        jsonResponse(['error' => 'Nenhum arquivo enviado'], 400);
    }
    
    $file = $_FILES['file'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        jsonResponse(['error' => 'Tipo de arquivo não permitido'], 400);
    }
    
    if ($file['size'] > $maxSize) {
        jsonResponse(['error' => 'Arquivo muito grande (máximo 5MB)'], 400);
    }
    
    $fileName = uniqid() . '_' . basename($file['name']);
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        jsonResponse([
            'success' => true,
            'fileName' => $fileName,
            'filePath' => '/assets/uploads/' . $fileName
        ], 200);
    } else {
        jsonResponse(['error' => 'Erro ao salvar arquivo'], 500);
    }
} else {
    jsonResponse(['error' => 'Método não permitido'], 405);
}
?>