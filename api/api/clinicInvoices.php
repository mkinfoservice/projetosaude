<?php
// api/clinicInvoices.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM clinicInvoices ORDER BY submittedAt DESC");
        jsonResponse($stmt->fetchAll());
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $data['id'] = $data['id'] ?? generateId();
        
        $stmt = $pdo->prepare("INSERT INTO clinicInvoices (id, clinicId, clinicName, fileName, filePath, value, rate, days, rateValue, iss, irrf, netValue, status, period, submittedAt) 
                               VALUES (:id, :clinicId, :clinicName, :fileName, :filePath, :value, :rate, :days, :rateValue, :iss, :irrf, :netValue, :status, :period, NOW())");
        $stmt->execute($data);
        
        jsonResponse(['success' => true, 'id' => $data['id']], 201);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? $_GET['id'] ?? '';
        
        $allowedFields = ['status', 'approvedAt', 'approvedBy', 'rejectedAt', 'rejectedBy', 'rejectionReason'];
        $updates = [];
        $params = ['id' => $id];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        $sql = "UPDATE clinicInvoices SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['error' => 'Método não permitido'], 405);
}
?>