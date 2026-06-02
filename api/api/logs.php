<?php
// api/logs.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        
        if ($action === 'getAll') {
            $limit = $_GET['limit'] ?? 100;
            $stmt = $pdo->prepare("SELECT * FROM logs ORDER BY timestamp DESC LIMIT ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll();
            foreach ($logs as &$log) {
                $log['details'] = json_decode($log['details'] ?? '{}', true);
            }
            jsonResponse($logs);
        } else {
            jsonResponse(['error' => 'Ação inválida'], 400);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $pdo->prepare("INSERT INTO logs (action, userId, details, timestamp) VALUES (:action, :userId, :details, NOW())");
        $stmt->execute([
            'action' => $data['action'] ?? '',
            'userId' => $data['userId'] ?? null,
            'details' => json_encode($data['details'] ?? [])
        ]);
        
        jsonResponse(['success' => true], 201);
        break;

    default:
        jsonResponse(['error' => 'Método não permitido'], 405);
}
?>