<?php
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $d = json_decode(file_get_contents('php://input'), true);
  $senhaHash = password_hash($d['senha'], PASSWORD_DEFAULT);
  
  $pdo->beginTransaction();
  try {
    // 1. Inserir proposta
    $stmt = $pdo->prepare("INSERT INTO propostas (cliente, cpf, plano, status, criado_em) VALUES (:nome, :cpf, :plano, 'pending_supervisor', NOW())");
    $stmt->execute([
      ':nome' => $d['nome'], ':cpf' => $d['cpf'], ':plano' => $d['plano']
    ]);
    
    // 2. Inserir usuário (cliente) com senha hash
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, cpf, senha, perfil, status) VALUES (:nome, :cpf, :senha, 'cliente', 'pending')");
    $stmt->execute([':nome'=>$d['nome'], ':cpf'=>$d['cpf'], ':senha'=>$senhaHash]);
    
    $pdo->commit();
    echo json_encode(['sucesso' => true, 'mensagem' => 'Proposta enviada']);
  } catch(Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao salvar']);
  }
}
?>