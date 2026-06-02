<?php
// api/users.php
// CRUD de Usuários do Sistema

require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        // Listar usuários com filtros
        $profile = $_GET['profile'] ?? null;
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $query = "SELECT id, name, cpf, cnpj, profile, phone, email, sellerId, status, createdAt FROM users WHERE 1=1";
        $params = [];
        $types = '';
        
        if ($profile) {
            $query .= " AND profile = ?";
            $params[] = $profile;
            $types .= 's';
        }
        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
            $types .= 's';
        }
        if ($search) {
            $query .= " AND (name LIKE ? OR cpf LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'ss';
        }
        
        $query .= " ORDER BY createdAt DESC";
        
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        jsonResponse($users);
        break;
        
    case 'POST':
        // Criar novo usuário
        if (!isset($input['name'], $input['cpf'], $input['profile'], $input['password'])) {
            jsonResponse(null, 400, 'Campos obrigatórios faltando');
        }
        
        if (!validateCPF($input['cpf'])) {
            jsonResponse(null, 400, 'CPF inválido');
        }
        
        // Verificar se CPF já existe
        $stmt = $conn->prepare("SELECT id FROM users WHERE cpf = ?");
        $stmt->bind_param("s", $input['cpf']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            jsonResponse(null, 409, 'CPF já cadastrado');
        }
        
        $id = $input['id'] ?? 'USR_' . uniqid();
        $password = password_hash($input['password'], PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (id, name, cpf, cnpj, profile, password, phone, email, sellerId, status, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $cnpj = $input['cnpj'] ?? null;
        $phone = $input['phone'] ?? null;
        $email = $input['email'] ?? null;
        $sellerId = $input['sellerId'] ?? null;
        $status = $input['status'] ?? 'pending';
        $address = isset($input['address']) ? json_encode($input['address'], JSON_UNESCAPED_UNICODE) : null;
        
        $stmt->bind_param("sssssssssss", $id, $input['name'], $input['cpf'], $cnpj, $input['profile'], $password, $phone, $email, $sellerId, $status, $address);
        
        if ($stmt->execute()) {
            logAction($conn, 'USER_CREATED', $id, ['profile' => $input['profile']]);
            jsonResponse(['id' => $id], 201, 'Usuário criado com sucesso');
        } else {
            jsonResponse(null, 500, 'Erro ao criar usuário: ' . $stmt->error);
        }
        break;
        
    case 'PUT':
        // Atualizar usuário
        if (!isset($input['id'])) {
            jsonResponse(null, 400, 'ID do usuário obrigatório');
        }
        
        $updates = [];
        $types = '';
        $params = [];
        
        $allowedFields = ['name', 'phone', 'email', 'sellerId', 'status', 'rejectionReason', 'address'];
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updates[] = "$field = ?";
                $types .= 's';
                $params[] = $field === 'address' && is_array($input[$field]) ? json_encode($input[$field], JSON_UNESCAPED_UNICODE) : $input[$field];
            }
        }
        
        if (empty($updates)) {
            jsonResponse(null, 400, 'Nenhum campo para atualizar');
        }
        
        $params[] = $input['id'];
        $types .= 's';
        
        $query = "UPDATE users SET " . implode(', ', $updates) . ", updatedAt = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            logAction($conn, 'USER_UPDATED', $input['id'], ['fields' => array_keys($updates)]);
            jsonResponse(['id' => $input['id']], 200, 'Usuário atualizado com sucesso');
        } else {
            jsonResponse(null, 500, 'Erro ao atualizar usuário: ' . $stmt->error);
        }
        break;
        
    case 'DELETE':
        // Deletar usuário (soft delete via status)
        parse_str(file_get_contents('php://input'), $input);
        $id = $input['id'] ?? $_GET['id'] ?? null;
        
        if (!$id) {
            jsonResponse(null, 400, 'ID do usuário obrigatório');
        }
        
        $stmt = $conn->prepare("UPDATE users SET status = 'rejected', updatedAt = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("s", $id);
        
        if ($stmt->execute()) {
            logAction($conn, 'USER_DELETED', $id);
            jsonResponse(['id' => $id], 200, 'Usuário removido com sucesso');
        } else {
            jsonResponse(null, 500, 'Erro ao remover usuário: ' . $stmt->error);
        }
        break;
        
    default:
        jsonResponse(null, 405, 'Método não permitido');
}

$conn->close();
?>