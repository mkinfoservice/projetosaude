<?php
// api/notifications.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        
        if ($action === 'getByClient') {
            $clientCPF = $_GET['clientCPF'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE clientCPF = ? AND read = 0 ORDER BY createdAt DESC");
            $stmt->execute([$clientCPF]);
            jsonResponse($stmt->fetchAll());
        } elseif ($action === 'getBySeller') {
            $sellerId = $_GET['sellerId'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE sellerId = ? AND read = 0 ORDER BY createdAt DESC");
            $stmt->execute([$sellerId]);
            jsonResponse($stmt->fetchAll());
        } elseif ($action === 'getAll') {
            $stmt = $pdo->query("SELECT * FROM notifications ORDER BY createdAt DESC LIMIT 100");
            jsonResponse($stmt->fetchAll());
        } else {
            jsonResponse(['error' => 'Ação inválida'], 400);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $data['id'] = $data['id'] ?? generateId();
        $data['read'] = $data['read'] ?? 0;
        
        $stmt = $pdo->prepare("INSERT INTO notifications (id, clientId, clientCPF, sellerId, profile, type, message, read, createdAt) 
                               VALUES (:id, :clientId, :clientCPF, :sellerId, :profile, :type, :message, :read, NOW())");
        $stmt->execute($data);
        
        jsonResponse(['success' => true, 'id' => $data['id']], 201);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? $_GET['id'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE notifications SET read = :read WHERE id = :id");
        $stmt->execute(['read' => $data['read'] ?? 1, 'id' => $id]);
        
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['error' => 'Método não permitido'], 405);
}
?>