<?php
/**
 * api/webhook-asaas.php
 * Endpoint para receber webhooks do Asaas
 * URL para configurar no Asaas: https://cardsaudebank.com.br/api/webhook-asaas.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// ===== VALIDAR TOKEN DO WEBHOOK =====
$receivedToken = $_SERVER['HTTP_ACCESS_TOKEN'] ?? $_GET['access_token'] ?? '';
if ($receivedToken !== ASAAS_WEBHOOK_TOKEN) {
    http_response_code(403);
    echo "Unauthorized";
    log_system('WARNING', 'WEBHOOK_UNAUTHORIZED', ['received_token' => substr($receivedToken ?? '', 0, 10)]);
    exit;
}

// ===== LER PAYLOAD =====
$input = file_get_contents('php://input');
$event = json_decode($input, true);

if (!$event || !isset($event['event'], $event['payment'])) {
    http_response_code(400);
    echo "Invalid payload";
    log_system('WARNING', 'WEBHOOK_INVALID_PAYLOAD', ['raw' => substr($input, 0, 200)]);
    exit;
}

$pdo = getDBConnection();
$payment = $event['payment'];
$eventType = $event['event'];

log_system('INFO', 'ASAAS_WEBHOOK_RECEIVED', [
    'event' => $eventType,
    'payment_id' => $payment['id'],
    'status' => $payment['status'] ?? null,
    'value' => $payment['value'] ?? null
]);

// ===== PROCESSAR EVENTOS =====

try {
    // Buscar registro de pagamento no banco
    $stmt = $pdo->prepare("
        SELECT p.*, 
               CASE WHEN p.entity_type = 'EMPRESA' THEN 'empresas' ELSE 'vendedores' END as entity_table
        FROM pagamentos p
        WHERE p.asaas_payment_id = ? LIMIT 1
    ");
    $stmt->execute([$payment['id']]);
    $record = $stmt->fetch();
    
    if (!$record) {
        log_system('WARNING', 'WEBHOOK_PAYMENT_NOT_FOUND', ['asaas_id' => $payment['id']]);
        http_response_code(200); // Asaas espera 200 mesmo se não encontrar
        echo "OK - Payment not found in our system";
        exit;
    }
    
    // Mapear status do Asaas para nosso sistema
    $statusMap = [
        'PENDING' => 'PENDING',
        'RECEIVED' => 'RECEIVED',
        'CONFIRMED' => 'CONFIRMED',
        'OVERDUE' => 'FAILED',
        'REFUNDED' => 'REFUNDED',
        'FAILED' => 'FAILED'
    ];
    $newStatus = $statusMap[$payment['status']] ?? 'PENDING';
    
    // Atualizar pagamento
    $updateData = [
        'status' => $newStatus,
        'webhook_received_at' => date('Y-m-d H:i:s')
    ];
    
    if ($payment['status'] === 'CONFIRMED') {
        $updateData['confirmed_at'] = date('Y-m-d H:i:s');
    }
    
    dbClass()->update('pagamentos', $updateData, ['id' => ':id'], ['id' => $record['id']]);
    
    // Se confirmado, ativar a conta
    if ($payment['status'] === 'CONFIRMED' && $record['status'] !== 'CONFIRMED') {
        $entityTable = $record['entity_table'];
        $entityId = $record['entity_id'];
        
        // Ativar entidade
        $stmt = $pdo->prepare("
            UPDATE $entityTable 
            SET status = 'ATIVO', updated_at = NOW(), pix_status = 'CONFIRMED'
            WHERE id = ?
        ");
        $stmt->execute([$entityId]);
        
        log_system('INFO', 'ACCOUNT_ACTIVATED_VIA_WEBHOOK', [
            'entity_type' => $record['entity_type'],
            'entity_id' => $entityId,
            'asaas_payment_id' => $payment['id']
        ]);
        
        // Aqui poderia enviar e-mail de boas-vindas
        // sendWelcomeEmail($entityType, $entityId);
    }
    
    http_response_code(200);
    echo "OK";
    
} catch (Exception $e) {
    log_system('ERROR', 'WEBHOOK_PROCESSING_ERROR', [
        'error' => $e->getMessage(),
        'payment_id' => $payment['id'] ?? 'unknown'
    ]);
    
    // Mesmo com erro, retornar 200 para Asaas não reenviar infinitamente
    http_response_code(200);
    echo "ERROR - Internal processing error";
}

exit;
?>
