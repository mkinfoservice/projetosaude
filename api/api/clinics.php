<?php
// api/clinics.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        
        if ($action === 'getAll') {
            $stmt = $pdo->query("SELECT * FROM clinics ORDER BY createdAt DESC");
            $clinics = $stmt->fetchAll();
            foreach ($clinics as &$c) {
                $c['address'] = json_decode($c['address'] ?? '{}', true);
                $c['documents'] = json_decode($c['documents'] ?? '[]', true);
            }
            jsonResponse($clinics);
        } elseif ($action === 'getByAtividade') {
            $atividade = $_GET['atividade'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM clinics WHERE atividade = ? AND status = 'active'");
            $stmt->execute([$atividade]);
            jsonResponse($stmt->fetchAll());
        } else {
            jsonResponse(['error' => 'Ação inválida'], 400);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $data = sanitizeInput($data);
        
        $data['id'] = $data['id'] ?? generateId();
        $data['address'] = json_encode($data['address'] ?? []);
        $data['documents'] = json_encode($data['documents'] ?? []);
        
        $stmt = $pdo->prepare("INSERT INTO clinics (id, companyName, cnpj, cpf, phone, email, password, address, atividade, especialidade, documents, contractAccepted, status, submittedAt) 
                               VALUES (:id, :companyName, :cnpj, :cpf, :phone, :email, :password, :address, :atividade, :especialidade, :documents, :contractAccepted, :status, NOW())");
        $stmt->execute($data);
        
        jsonResponse(['success' => true, 'id' => $data['id']], 201);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? $_GET['id'] ?? '';
        
        if (isset($data['address'])) $data['address'] = json_encode($data['address']);
        
        $allowedFields = ['status', 'approvedAt', 'approvedBy', 'rejectedAt', 'rejectedBy', 'rejectionReason'];
        $updates = [];
        $params = ['id' => $id];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        $updates[] = "updatedAt = NOW()";
        
        $sql = "UPDATE clinics SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['error' => 'Método não permitido'], 405);
}
?>