<?php
// api/attendances.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        
        if ($action === 'getAll') {
            $stmt = $pdo->query("SELECT * FROM attendances ORDER BY createdAt DESC");
            $attendances = $stmt->fetchAll();
            foreach ($attendances as &$a) {
                $a['procedures'] = json_decode($a['procedures'] ?? '[]', true);
            }
            jsonResponse($attendances);
        } elseif ($action === 'getByClinic') {
            $clinicId = $_GET['clinicId'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM attendances WHERE clinicId = ? ORDER BY createdAt DESC");
            $stmt->execute([$clinicId]);
            jsonResponse($stmt->fetchAll());
        } elseif ($action === 'getByClient') {
            $clientCPF = $_GET['clientCPF'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM attendances WHERE clientCPF = ? ORDER BY createdAt DESC");
            $stmt->execute([$clientCPF]);
            jsonResponse($stmt->fetchAll());
        } else {
            jsonResponse(['error' => 'Ação inválida'], 400);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $data = sanitizeInput($data);
        
        $data['id'] = $data['id'] ?? generateId();
        $data['procedures'] = json_encode($data['procedures'] ?? []);
        
        $stmt = $pdo->prepare("INSERT INTO attendances (id, clinicId, clinicName, clientId, clientCPF, clientName, procedures, total, paymentMethod, status, repasseStatus, createdAt) 
                               VALUES (:id, :clinicId, :clinicName, :clientId, :clientCPF, :clientName, :procedures, :total, :paymentMethod, :status, :repasseStatus, NOW())");
        $stmt->execute($data);
        
        jsonResponse(['success' => true, 'id' => $data['id']], 201);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? $_GET['id'] ?? '';
        
        if (isset($data['procedures'])) $data['procedures'] = json_encode($data['procedures']);
        
        $allowedFields = ['status', 'authorizedAt', 'rejectedAt', 'clientAuthorized', 'repasseStatus', 'repasseApprovedAt'];
        $updates = [];
        $params = ['id' => $id];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        $updates[] = "updatedAt = NOW()";
        
        $sql = "UPDATE attendances SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['error' => 'Método não permitido'], 405);
}
?>