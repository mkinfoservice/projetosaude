<?php
require 'config.php';
header('Content-Type: application/json');
$tipo = $_GET['tipo'] ?? 'admin';

if ($tipo === 'admin') {
  $propostas = $pdo->query("SELECT id, cliente as nome, cpf, plano, status FROM propostas")->fetchAll();
  $vendedores = $pdo->query("SELECT id, nome, cpf, criado_em as data, status FROM usuarios WHERE perfil='vendedor'")->fetchAll();
  $comissoes = $pdo->query("SELECT id, vendedor, cliente, plano, bruto, iss, irrf, neto, status FROM comissoes")->fetchAll();
  echo json_encode(['propostas'=>$propostas, 'vendedores'=>$vendedores, 'comissoes'=>$comissoes]);
} 
elseif ($tipo === 'vendedor') {
  // Ajuste conforme ID do vendedor logado (via sessão/token)
  echo json_encode(['propostas'=>[], 'comissoes'=>[]]);
}
elseif ($tipo === 'extrato') {
  // Retornar extrato do cliente logado
  echo json_encode([]);
}
?>