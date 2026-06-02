<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Configuração do Banco - cardsaudebank.com.br
$db_host = 'cardsaude_db.mysql.dbaas.com.br';
$db_name = 'cardsaude_db';
$db_user = 'cardsaude_db';  // ← ALTERE AQUI
$db_pass = 'L91718104Ruth@';    // ← ALTERE AQUI

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão: ' . $e->getMessage()]);
    exit;
}

$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

// LOGIN
if ($acao === 'login') {
    $perfil = $_POST['perfil'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if ($perfil === 'vendedor') {
        $stmt = $conn->prepare("SELECT * FROM vendedores WHERE telefone = ? AND senha = ?");
        $stmt->execute([$telefone, $senha]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            if ($user['pagamento_ok']) {
                echo json_encode(['success' => true, 'data' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Pagamento pendente']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuário ou senha inválidos']);
        }
    } else {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE cpf = ? AND senha = ? AND perfil = ?");
        $stmt->execute([$telefone, $senha, $perfil]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode(['success' => true, 'data' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuário ou senha inválidos']);
        }
    }
}

// CADASTRAR VENDEDOR
elseif ($acao === 'cadastrar_vendedor') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $conn->prepare("INSERT INTO vendedores (nome, cpf, telefone, email, senha) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['nome'],
        $data['cpf'],
        $data['telefone'],
        $data['email'],
        $data['senha']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Cadastro realizado']);
}

// CONFIRMAR PAGAMENTO
elseif ($acao === 'confirmar_pagamento') {
    $telefone = $_POST['telefone'] ?? '';
    $stmt = $conn->prepare("UPDATE vendedores SET pagamento_ok = 1 WHERE telefone = ?");
    $stmt->execute([$telefone]);
    echo json_encode(['success' => true]);
}

// SALVAR PROPOSTA
elseif ($acao === 'salvar_proposta') {
    $data = json_decode(file_get_contents('php://input'), true);
    $protocolo = 'PROP-' . time();
    
    $stmt = $conn->prepare("INSERT INTO propostas (protocolo, cliente_nome, cliente_cpf, cliente_rg, cliente_nascimento, cliente_sexo, cliente_civil, cliente_mae, cliente_cep, cliente_cidade, cliente_bairro, cliente_endereco, cliente_numero, cliente_complemento, cliente_telefone, cliente_email, cliente_entidade, cliente_profissao, operadora, plano, cobertura, vendedor_telefone, vendedor_nome, dados_cotacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $protocolo,
        $data['cliente_nome'],
        $data['cliente_cpf'],
        $data['cliente_rg'],
        $data['cliente_nascimento'],
        $data['cliente_sexo'],
        $data['cliente_civil'],
        $data['cliente_mae'],
        $data['cliente_cep'],
        $data['cliente_cidade'],
        $data['cliente_bairro'],
        $data['cliente_endereco'],
        $data['cliente_numero'],
        $data['cliente_complemento'],
        $data['cliente_telefone'],
        $data['cliente_email'],
        $data['cliente_entidade'],
        $data['cliente_profissao'],
        $data['operadora'],
        $data['plano'],
        $data['cobertura'],
        $data['vendedor_telefone'],
        $data['vendedor_nome'],
        json_encode($data['dados_cotacao'])
    ]);
    
    echo json_encode(['success' => true, 'protocolo' => $protocolo]);
}

// LISTAR PROPOSTAS
elseif ($acao === 'listar_propostas') {
    $tipo = $_GET['tipo'] ?? '';
    $telefone = $_GET['telefone'] ?? '';
    
    if ($tipo === 'vendedor') {
        $stmt = $conn->prepare("SELECT * FROM propostas WHERE vendedor_telefone = ? ORDER BY created_at DESC");
        $stmt->execute([$telefone]);
    } elseif ($tipo === 'supervisor') {
        $stmt = $conn->query("SELECT * FROM propostas WHERE status LIKE '%Supervisor%' ORDER BY created_at DESC");
    } elseif ($tipo === 'gerente') {
        $stmt = $conn->query("SELECT * FROM propostas WHERE status LIKE '%Gerente%' ORDER BY created_at DESC");
    }
    
    $propostas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $propostas]);
}

// ATUALIZAR STATUS
elseif ($acao === 'atualizar_status') {
    $protocolo = $_POST['protocolo'] ?? '';
    $status = $_POST['status'] ?? '';
    
    $stmt = $conn->prepare("UPDATE propostas SET status = ? WHERE protocolo = ?");
    $stmt->execute([$status, $protocolo]);
    
    echo json_encode(['success' => true]);
}

// ENVIAR WHATSAPP
elseif ($acao === 'enviar_whatsapp') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Aqui você integra com Evolution API, Z-API ou similar
    $api_url = 'http://localhost:8080/message/sendText'; // ← Configure sua API de WhatsApp
    $api_token = 'SEU_TOKEN';
    
    $payload = [
        'number' => $data['telefone'],
        'textMessage' => ['text' => $data['mensagem']]
    ];
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $api_token
    ]);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    echo json_encode(['success' => true, 'message' => 'WhatsApp enviado']);
}
?>