<?php
// api/proposals.php
// CRUD de Propostas de Cadastro

require_once 'config.php';

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        $sellerId = $_GET['sellerId'] ?? null;
        $status = $_GET['status'] ?? null;
        
        $query = "SELECT p.id, p.clientData, p.plan, p.status, p.supervisorStatus, p.managerStatus, p.submittedAt, p.sellerId, p.sellerName, p.origin, p.generatesCommission FROM proposals p WHERE 1=1";
        $params = [];
        $types = '';
        
        if ($sellerId) {
            $query .= " AND (p.sellerId = ? OR JSON_UNQUOTE(JSON_EXTRACT(p.clientData, '$.sellerId')) = ?)";
            $params[] = $sellerId;
            $params[] = $sellerId;
            $types .= 'ss';
        }
        if ($status) {
            $query .= " AND p.status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        $query .= " ORDER BY p.submittedAt DESC";
        
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $proposals = [];
        while ($row = $result->fetch_assoc()) {
            $row['clientData'] = json_decode($row['clientData'], true);
            $proposals[] = $row;
        }
        
        jsonResponse($proposals);
        break;
        
    case 'POST':
        if (!isset($input['clientId'], $input['clientData'], $input['plan'])) {
            jsonResponse(null, 400, 'Campos obrigatórios faltando');
        }
        
        $id = $input['id'] ?? 'PROP_' . uniqid();
        $clientDataJson = json_encode($input['clientData'], JSON_UNESCAPED_UNICODE);
        $documentsJson = isset($input['documents']) ? json_encode($input['documents'], JSON_UNESCAPED_UNICODE) : null;
        
        $stmt = $conn->prepare("INSERT INTO proposals (id, clientId, clientData, plan, status, supervisorStatus, managerStatus, submittedAt, sellerId, sellerName, origin, generatesCommission, documents, contractAccepted) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssssssssssii", 
            $id, $input['clientId'], $clientDataJson, $input['plan'],
            'pending_supervisor', 'pending', 'pending',
            $input['sellerId'] ?? null, $input['sellerName'] ?? null,
            $input['origin'] ?? 'website', $input['generatesCommission'] ?? false,
            $documentsJson, $input['contractAccepted'] ?? false
        );
        
        if ($stmt->execute()) {
            logAction($conn, 'PROPOSAL_SUBMITTED', $input['clientId'], ['plan' => $input['plan'], 'origin' => $input['origin'] ?? 'website']);
            
            // Notificar vendedor se houver
            if ($input['sellerId']) {
                $notifId = 'NOTIF_' . uniqid();
                $stmtNotif = $conn->prepare("INSERT INTO notifications (id, sellerId, type, message, createdAt) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
                $message = "Nova proposta enviada: {$input['clientData']['name']} - Plano " . strtoupper($input['plan']);
                $stmtNotif->bind_param("ssss", $notifId, $input['sellerId'], 'proposal_submitted', $message);
                $stmtNotif->execute();
                $stmtNotif->close();
            }
            
            jsonResponse(['id' => $id], 201, 'Proposta enviada para análise');
        } else {
            jsonResponse(null, 500, 'Erro ao enviar proposta: ' . $stmt->error);
        }
        break;
        
    case 'PUT':
        // Aprovar/Rejeitar proposta (Supervisor ou Gerente)
        if (!isset($input['id'], $input['action'], $input['approverProfile'], $input['approverId'])) {
            jsonResponse(null, 400, 'Parâmetros obrigatórios faltando');
        }
        
        $proposalId = $input['id'];
        $action = $input['action']; // 'approve' ou 'reject'
        $profile = $input['approverProfile']; // 'supervisor' ou 'gerente'
        $approverId = $input['approverId'];
        $reason = $input['reason'] ?? null;
        
        // Buscar proposta
        $stmt = $conn->prepare("SELECT * FROM proposals WHERE id = ?");
        $stmt->bind_param("s", $proposalId);
        $stmt->execute();
        $result = $stmt->get_result();
        $proposal = $result->fetch_assoc();
        
        if (!$proposal) {
            jsonResponse(null, 404, 'Proposta não encontrada');
        }
        
        $clientData = json_decode($proposal['clientData'], true);
        $clientId = $clientData['id'];
        $plan = $proposal['plan'];
        
        if ($profile === 'supervisor') {
            if ($action === 'approve') {
                $newStatus = 'pending_manager';
                $supervisorStatus = 'approved';
                $updateQuery = "UPDATE proposals SET supervisorStatus = ?, status = ?, supervisorApprovedAt = CURRENT_TIMESTAMP, supervisorApprovedBy = ? WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("ssss", $supervisorStatus, $newStatus, $approverId, $proposalId);
            } else {
                $newStatus = 'rejected_supervisor';
                $supervisorStatus = 'rejected';
                $updateQuery = "UPDATE proposals SET supervisorStatus = ?, status = ?, supervisorRejectedAt = CURRENT_TIMESTAMP, supervisorRejectedBy = ?, supervisorReason = ? WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("sssss", $supervisorStatus, $newStatus, $approverId, $reason, $proposalId);
            }
        } elseif ($profile === 'gerente') {
            if ($action === 'approve') {
                // Aprovar: criar cliente, gerar comissão se aplicável
                $newStatus = 'approved';
                $managerStatus = 'approved';
                
                // Atualizar cliente
                $clientData['status'] = 'active';
                $clientData['availableLimit'] = $clientData['limit'];
                $clientData['paymentConfirmed'] = true;
                $clientData['paymentConfirmedAt'] = date('Y-m-d H:i:s');
                
                $stmtClient = $conn->prepare("INSERT INTO clients (id, name, cpf, rg, motherName, birthDate, gender, maritalStatus, phone, email, password, profile, plan, status, documents, dependents, cardNumber, limit, availableLimit, usedLimit, address, paymentConfirmed, paymentConfirmedAt, sellerId, origin) SELECT id, name, cpf, rg, motherName, birthDate, gender, maritalStatus, phone, email, password, profile, plan, ?, documents, dependents, cardNumber, limit, availableLimit, usedLimit, address, ?, CURRENT_TIMESTAMP, sellerId, origin FROM proposals WHERE id = ?");
                $stmtClient->bind_param("sis", $clientData['status'], $clientData['paymentConfirmed'], $proposalId);
                $stmtClient->execute();
                $stmtClient->close();
                
                // Gerar comissão se aplicável
                if ($proposal['generatesCommission'] && $proposal['sellerId']) {
                    $commissionValue = ['bronze' => 160, 'prata' => 200, 'ouro' => 300][$plan] ?? 0;
                    $iss = $commissionValue * 0.05;
                    $irrf = $commissionValue * 0.275;
                    $netValue = $commissionValue - $iss - $irrf;
                    $paymentDate = date('Y-m-d', strtotime('+5 days'));
                    
                    $commId = 'COM_' . uniqid();
                    $stmtComm = $conn->prepare("INSERT INTO commissions (id, sellerId, sellerName, clientId, clientName, plan, grossValue, iss, irrf, netValue, status, paymentDate, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', ?, CURRENT_TIMESTAMP)");
                    $stmtComm->bind_param("ssssssdddds", $commId, $proposal['sellerId'], $proposal['sellerName'], $clientId, $clientData['name'], $plan, $commissionValue, $iss, $irrf, $netValue, $paymentDate);
                    $stmtComm->execute();
                    $stmtComm->close();
                    
                    // Notificar vendedor
                    $notifId = 'NOTIF_' . uniqid();
                    $stmtNotif = $conn->prepare("INSERT INTO notifications (id, sellerId, type, message, createdAt) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
                    $message = "Comissão de R$ " . number_format($netValue, 2, ',', '.') . " agendada para " . date('d/m/Y', strtotime($paymentDate));
                    $stmtNotif->bind_param("ssss", $notifId, $proposal['sellerId'], 'commission_scheduled', $message);
                    $stmtNotif->execute();
                    $stmtNotif->close();
                }
                
                // Notificar cliente
                $notifClientId = 'NOTIF_CLI_' . uniqid();
                $stmtNotifCli = $conn->prepare("INSERT INTO notifications (id, clientId, clientCPF, type, message, createdAt) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                $messageCli = "🎉 PARABÉNS! Sua proposta foi APROVADA! Crédito liberado: R$ " . number_format($clientData['limit'], 2, ',', '.');
                $stmtNotifCli->bind_param("sssss", $notifClientId, $clientId, $clientData['cpf'], 'proposal_approved_client', $messageCli);
                $stmtNotifCli->execute();
                $stmtNotifCli->close();
                
                $updateQuery = "UPDATE proposals SET managerStatus = ?, status = ?, paymentConfirmed = ?, managerApprovedAt = CURRENT_TIMESTAMP, managerApprovedBy = ? WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                $paymentConfirmed = 1;
                $stmt->bind_param("ssisss", $managerStatus, $newStatus, $paymentConfirmed, $approverId, $proposalId);
                
            } else {
                $newStatus = 'rejected_manager';
                $managerStatus = 'rejected';
                $updateQuery = "UPDATE proposals SET managerStatus = ?, status = ?, managerRejectedAt = CURRENT_TIMESTAMP, managerRejectedBy = ?, managerReason = ? WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("ssssss", $managerStatus, $newStatus, $approverId, $reason, $proposalId);
            }
        }
        
        if ($stmt->execute()) {
            logAction($conn, 'PROPOSAL_' . strtoupper($action), $proposalId, ['by' => $approverId, 'profile' => $profile, 'reason' => $reason]);
            jsonResponse(['id' => $proposalId, 'status' => $newStatus], 200, 'Proposta ' . ($action === 'approve' ? 'APROVADA' : 'RECUSADA'));
        } else {
            jsonResponse(null, 500, 'Erro ao processar proposta: ' . $stmt->error);
        }
        break;
        
    default:
        jsonResponse(null, 405, 'Método não permitido');
}

$conn->close();
?>