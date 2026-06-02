<?php
require 'config.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$data = json_decode(file_get_contents('php://input'), true);
$cpf = preg_replace('/\D/', '', $data['cpf'] ?? '');
$senha = $data['senha'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cpf = :cpf LIMIT 1");
$stmt->execute([':cpf' => $cpf]);
$user = $stmt->fetch();

if (!$user || !password_verify($senha, $user['senha'])) {
  http_response_code(401);
  echo json_encode(['erro' => 'CPF ou senha inválidos']);
  exit;
}

// Retorna usuário sem senha
unset($user['senha']);
echo json_encode(['sucesso' => true, 'user' => $user]);
?>