<?php
// api/commissions.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        
        if ($action === 'getAll') {
            $stmt = $pdo->query("SELECT * FROM commissions ORDER BY createdAt DESC");
            jsonResponse($stmt->fetchAll());
        } elseif ($action === 'getBySeller') {
            $sellerId = $_GET['sellerId'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM commissions WHERE sellerId = ? ORDER BY paymentDate DESC");
            $stmt->execute([$sellerId]);
            jsonResponse($stmt->fetchAll());
        } else {
            jsonResponse(['error' => 'Ação inválida'], 400);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $data['id'] = $data['id'] ?? generateId();
        
        $stmt = $pdo->prepare("INSERT INTO commissions (id, sellerId, sellerName, clientId, clientName, plan, grossValue, iss, irrf, netValue, status, paymentDate, createdAt) 
                               VALUES (:id, :sellerId, :sellerName, :clientId, :clientName, :plan, :grossValue, :iss, :irrf, :netValue, :status, :paymentDate, NOW())");
        $stmt->execute($data);
        
        jsonResponse(['success' => true, 'id' => $data['id']], 201);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? $_GET['id'] ?? '';
        
        $allowedFields = ['status', 'paidAt'];
        $updates = [];
        $params = ['id' => $id];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        $updates[] = "updatedAt = NOW()";
        
        $sql = "UPDATE commissions SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['error' => 'Método não permitido'], 405);
}
?>