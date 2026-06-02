<?php
// api/clients.php
// CRUD de Clientes do Cartão Saúde

require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $cpf = $_GET['cpf'] ?? null;
        $cardNumber = $_GET['cardNumber'] ?? null;
        $sellerId = $_GET['sellerId'] ?? null;
        
        if ($cpf) {
            $stmt = $conn->prepare("SELECT id, name, cpf, plan, status, limit, availableLimit, usedLimit, cardNumber, paymentConfirmed FROM clients WHERE cpf = ?");
            $stmt->bind_param("s", $cpf);
        } elseif ($cardNumber) {
            $stmt = $conn->prepare("SELECT id, name, cpf, plan, status, limit, availableLimit, usedLimit, cardNumber, paymentConfirmed FROM clients WHERE cardNumber = ?");
            $stmt->bind_param("s", $cardNumber);
        } elseif ($sellerId) {
            $stmt = $conn->prepare("SELECT id, name, cpf, plan, status, limit, availableLimit, usedLimit, cardNumber, paymentConfirmed, createdAt FROM clients WHERE sellerId = ? ORDER BY createdAt DESC");
            $stmt->bind_param("s", $sellerId);
        } else {
            $stmt = $conn->prepare("SELECT id, name, cpf, plan, status, limit, availableLimit, usedLimit, cardNumber, paymentConfirmed, createdAt FROM clients ORDER BY createdAt DESC LIMIT 100");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $clients = [];
        while ($row = $result->fetch_assoc()) {
            $clients[] = $row;
        }
        
        jsonResponse($clients);
        break;
        
    case 'POST':
        if (!isset($input['name'], $input['cpf'], $input['plan'], $input['password'])) {
            jsonResponse(null, 400, 'Campos obrigatórios faltando');
        }
        
        if (!validateCPF($input['cpf'])) {
            jsonResponse(null, 400, 'CPF inválido');
        }
        
        $stmt = $conn->prepare("SELECT id FROM clients WHERE cpf = ?");
        $stmt->bind_param("s", $input['cpf']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            jsonResponse(null, 409, 'CPF já cadastrado');
        }
        
        $id = $input['id'] ?? 'CLI_' . uniqid();
        $password = password_hash($input['password'], PASSWORD_DEFAULT);
        $cardNumber = generateCardNumber();
        $limit = getPlanLimit($input['plan']);
        
        $stmt = $conn->prepare("INSERT INTO clients (id, name, cpf, rg, motherName, birthDate, gender, maritalStatus, phone, email, password, profile, plan, status, documents, dependents, cardNumber, limit, availableLimit, usedLimit, address, sellerId, origin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $documents = isset($input['documents']) ? json_encode($input['documents'], JSON_UNESCAPED_UNICODE) : null;
        $dependents = isset($input['dependents']) ? json_encode($input['dependents'], JSON_UNESCAPED_UNICODE) : null;
        $address = isset($input['address']) ? json_encode($input['address'], JSON_UNESCAPED_UNICODE) : null;
        
        $stmt->bind_param("sssssssssssssssssssssss", 
            $id, $input['name'], $input['cpf'], 
            $input['rg'] ?? null, $input['motherName'] ?? null, $input['birthDate'] ?? null,
            $input['gender'] ?? null, $input['maritalStatus'] ?? null,
            $input['phone'] ?? null, $input['email'] ?? null,
            $password, 'cliente', $input['plan'], $input['status'] ?? 'pending',
            $documents, $dependents, $cardNumber, $limit, 0, 0, $address,
            $input['sellerId'] ?? null, $input['origin'] ?? 'website'
        );
        
        if ($stmt->execute()) {
            logAction($conn, 'CLIENT_CREATED', $id, ['plan' => $input['plan']]);
            jsonResponse(['id' => $id, 'cardNumber' => $cardNumber, 'limit' => $limit], 201, 'Cliente cadastrado com sucesso');
        } else {
            jsonResponse(null, 500, 'Erro ao cadastrar cliente: ' . $stmt->error);
        }
        break;
        
    case 'PUT':
        if (!isset($input['id'])) {
            jsonResponse(null, 400, 'ID do cliente obrigatório');
        }
        
        $updates = [];
        $types = '';
        $params = [];
        
        $allowedFields = ['usedLimit', 'availableLimit', 'status', 'paymentConfirmed', 'paymentConfirmedAt'];
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                $types .= $field === 'paymentConfirmed' ? 'i' : 's';
                $params[] = $input[$field];
            }
        }
        
        if (empty($updates)) {
            jsonResponse(null, 400, 'Nenhum campo para atualizar');
        }
        
        $params[] = $input['id'];
        $types .= 's';
        
        $query = "UPDATE clients SET " . implode(', ', $updates) . ", updatedAt = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            logAction($conn, 'CLIENT_UPDATED', $input['id'], ['fields' => array_keys($updates)]);
            jsonResponse(['id' => $input['id']], 200, 'Cliente atualizado com sucesso');
        } else {
            jsonResponse(null, 500, 'Erro ao atualizar cliente: ' . $stmt->error);
        }
        break;
        
    default:
        jsonResponse(null, 405, 'Método não permitido');
}

// Funções auxiliares
function generateCardNumber() {
    return '2026 ' . implode(' ', array_map(fn() => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT), range(1, 3))) . ' ' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
}

function getPlanLimit($plan) {
    return ['bronze' => 800, 'prata' => 1000, 'ouro' => 1500][$plan] ?? 0;
}

$conn->close();
?>