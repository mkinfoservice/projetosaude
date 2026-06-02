<?php
// api/auth.php
// Autenticação de Usuários

require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if ($method === 'POST' && isset($input['cpf'], $input['password'])) {
    $cpf = preg_replace('/[^0-9]/', '', $input['cpf']);
    $formattedCPF = preg_replace('/(\d{3})(\d)/', '$1.$2', $cpf);
    $formattedCPF = preg_replace('/(\d{3})(\d)/', '$1.$2', $formattedCPF);
    $formattedCPF = preg_replace('/(\d{3})(\d{1,2})$/', '$1-$2', $formattedCPF);
    
    $password = $input['password'];
    $profile = $input['profile'] ?? null;
    
    // Buscar em users
    $stmt = $conn->prepare("SELECT * FROM users WHERE cpf = ? OR cpf = ?");
    $stmt->bind_param("ss", $formattedCPF, $cpf);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        // Buscar em clients
        $stmt = $conn->prepare("SELECT * FROM clients WHERE cpf = ? OR cpf = ?");
        $stmt->bind_param("ss", $formattedCPF, $cpf);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user) {
            // Verificar se é cliente ativo
            if ($user['status'] !== 'active') {
                jsonResponse(null, 403, 'Cliente não está ativo');
            }
            $user['profile'] = 'cliente';
        }
    }
    
    if (!$user) {
        // Verificar proposta pendente
        $stmt = $conn->prepare("SELECT id, status FROM proposals WHERE JSON_UNQUOTE(JSON_EXTRACT(clientData, '$.cpf')) IN (?, ?)");
        $stmt->bind_param("ss", $formattedCPF, $cpf);
        $stmt->execute();
        $result = $stmt->get_result();
        $proposal = $result->fetch_assoc();
        
        if ($proposal) {
            $statusMsg = match($proposal['status']) {
                'pending_supervisor' => '⏳ Aguardando Supervisor',
                'pending_manager' => '⏳ Aguardando Gerente',
                'approved' => '✅ Aprovado! Aguarde liberação do acesso.',
                default => '❌ Proposta Recusada'
            };
            jsonResponse(null, 403, $statusMsg);
        }
        
        jsonResponse(null, 404, 'CPF não encontrado');
    }
    
    // Verificar senha (em produção usar password_verify com hash)
    if ($user['password'] !== $password && !password_verify($password, $user['password'])) {
        jsonResponse(null, 401, 'Senha incorreta');
    }
    
    // Verificar perfil
    if ($profile && $profile !== 'cliente' && $user['profile'] !== $profile) {
        jsonResponse(null, 403, 'Perfil não corresponde ao CPF');
    }
    
    // Verificar bloqueio
    if ($user['status'] === 'blocked') {
        jsonResponse(null, 403, 'Usuário BLOQUEADO');
    }
    
    // Login bem-sucedido
    logAction($conn, 'LOGIN', $user['id'], ['profile' => $user['profile'], 'cpf' => $user['cpf']]);
    
    // Gerar token simples (em produção usar JWT)
    $token = base64_encode(json_encode([
        'id' => $user['id'],
        'profile' => $user['profile'],
        'name' => $user['name'],
        'exp' => time() + 86400 // 24 horas
    ]));
    
    // Preparar dados de resposta
    $responseData = [
        'id' => $user['id'],
        'name' => $user['name'],
        'cpf' => $user['cpf'],
        'profile' => $user['profile'],
        'status' => $user['status']
    ];
    
    if ($user['profile'] === 'cliente') {
        $responseData['plan'] = $user['plan'] ?? null;
        $responseData['limit'] = floatval($user['limit'] ?? 0);
        $responseData['usedLimit'] = floatval($user['usedLimit'] ?? 0);
        $responseData['cardNumber'] = $user['cardNumber'] ?? null;
    }
    
    jsonResponse([
        'user' => $responseData,
        'token' => $token
    ], 200, 'Login realizado com sucesso');
    
} else {
    jsonResponse(null, 400, 'CPF e senha são obrigatórios');
}

$conn->close();
?>