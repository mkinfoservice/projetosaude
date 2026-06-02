<?php
require_once 'config.php';

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

if ($acao === 'salvar') {
  $data = json_decode(file_get_contents('php://input'), true);
  $protocolo = 'PROP-' . time();
  
  $stmt = $conn->prepare("INSERT INTO propostas (protocolo, cliente, cpf, rg, nascimento, sexo, estado_civil, mae, cep, cidade, bairro, numero, endereco, complemento, telefone, email, entidade, profissao, plano, cobertura, operadora, vendedor_telefone, dados_cotacao, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendente Supervisor')");
  
  $dadosCotacao = json_encode($data['dadosCotacao']);
  
  $stmt->bind_param("ssssssssssssssssssssss", 
    $protocolo, $data['cliente'], $data['cpf'], $data['rg'], $data['nascimento'],
    $data['sexo'], $data['estadoCivil'], $data['mae'], $data['cep'], $data['cidade'],
    $data['bairro'], $data['numero'], $data['endereco'], $data['complemento'],
    $data['telefone'], $data['email'], $data['entidade'], $data['profissao'],
    $data['plano'], $data['cobertura'], $data['operadora'], $data['vendedorTelefone'],
    $dadosCotacao
  );
  
  if ($stmt->execute()) {
    echo json_encode(['success' => true, 'protocolo' => $protocolo]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar']);
  }
  
} elseif ($acao === 'listar') {
  $tipo = $_GET['tipo'];
  $telefone = $_GET['telefone'] ?? '';
  
  if ($tipo === 'vendedor' && $telefone) {
    $sql = "SELECT * FROM propostas WHERE vendedor_telefone = '$telefone' ORDER BY data_proposta DESC";
  } elseif ($tipo === 'supervisor') {
    $sql = "SELECT * FROM propostas WHERE status LIKE '%Supervisor%' ORDER BY data_proposta DESC";
  } elseif ($tipo === 'gerente') {
    $sql = "SELECT * FROM propostas WHERE status LIKE '%Gerente%' ORDER BY data_proposta DESC";
  }
  
  $result = $conn->query($sql);
  $propostas = $result->fetch_all(MYSQLI_ASSOC);
  
  echo json_encode(['success' => true, 'data' => $propostas]);
  
} elseif ($acao === 'atualizar_status') {
  $protocolo = $_POST['protocolo'];
  $status = $_POST['status'];
  
  $stmt = $conn->prepare("UPDATE propostas SET status = ? WHERE protocolo = ?");
  $stmt->bind_param("ss", $status, $protocolo);
  
  if ($stmt->execute()) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar']);
  }
}
?>