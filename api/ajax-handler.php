<?php
/**
 * api/ajax-handler.php
 * Endpoint único para todas as requisições AJAX do frontend
 */

// Headers CORS e segurança
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// CORS para desenvolvimento (ajustar em produção)
$allowedOrigins = [
    'https://cardsaudebank.com.br',      // ← Substitua
    'https://www.cardsaudebank.com.br',
    'http://cardsaude_db.mysql.dbaas.com.br',
    'http://127.0.0.1'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Carregar dependências
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/payment.php';
require_once __DIR__ . '/Upload.php';
require_once __DIR__ . '/Empresa.php';
require_once __DIR__ . '/vendedores.php';
require_once __DIR__ . '/proposta.php';

$pdo = getDBConnection();
$auth = new Auth($pdo);
$response = ['success' => false];

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    // Validar CSRF para ações de escrita
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $writeActions = [
        'register_empresa', 'register_vendedor', 'submit_proposta', 
        'confirm_payment', 'update_status', 'login'
    ];
    
    if (in_array($action, $writeActions) && !$auth->validateCsrfToken($csrfToken)) {
        throw new Exception('Token CSRF inválido ou expirado');
    }
    
    // ===== ROTAS =====
    
    switch ($action) {
        
        // ===== AUTENTICAÇÃO =====
        
        case 'login':
            $type = $_POST['type'] ?? 'vendedor';
            $identifier = $_POST['identifier'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($type === 'empresa') {
                $result = $auth->loginEmpresa($identifier, $password);
            } else {
                $result = $auth->loginVendedor($identifier, $password);
            }
            
            $response = $result;
            break;
        
        case 'logout':
            $auth->logout();
            $response = ['success' => true, 'redirect' => '/'];
            break;
        
        case 'refresh_csrf':
            $response = ['success' => true, 'csrf_token' => $auth->refreshCsrfToken()];
            break;
        
        // ===== EMPRESAS =====
        
        case 'register_empresa':
            $empresa = new Empresa($pdo);
            $response = $empresa->register($_POST, $_FILES);
            break;
        
        case 'get_empresa':
            $auth->requireLogin(['EMPRESA']);
            $empresa = new Empresa($pdo);
            $data = $empresa->getById($auth->getUserId());
            $response = ['success' => true, 'data' => $data];
            break;
        
        case 'get_empresa_dashboard':
            $auth->requireLogin(['EMPRESA']);
            $empresa = new Empresa($pdo);
            $data = $empresa->getDashboardData($auth->getUserId());
            $response = ['success' => true, 'data' => $data];
            break;
        
        // ===== VENDEDORES =====
        
        case 'register_vendedor':
            $empresaId = $auth->isLoggedIn() && $auth->getUserType() === 'EMPRESA' 
                ? $auth->getUserId() : null;
            $vendedor = new Vendedor($pdo);
            $response = $vendedor->register($_POST, $_FILES, $empresaId);
            break;
        
        case 'identify_vendedor':
            $telefone = $_POST['telefone'] ?? '';
            $vendedor = new Vendedor($pdo);
            $response = $vendedor->identifyByPhone($telefone);
            break;
        
        case 'get_vendedor_dashboard':
            $auth->requireLogin(['VENDEDOR']);
            $vendedor = new Vendedor($pdo);
            $data = $vendedor->getDashboardData($auth->getUserId());
            $response = ['success' => true, 'data' => $data];
            break;
        
        case 'list_vendedores':
            $auth->requireLogin(['EMPRESA', 'VENDEDOR']);
            $filters = array_intersect_key($_GET, array_flip(['status', 'perfil', 'search']));
            $vendedor = new Vendedor($pdo);
            $data = $vendedor->list($filters, $auth->getUserType() === 'EMPRESA' ? $auth->getUserId() : null);
            $response = ['success' => true, 'data' => $data];
            break;
        
        // ===== PROPOSTAS =====
        
        case 'submit_proposta':
            $auth->requireLogin(['VENDEDOR']);
            $proposta = new Proposta($pdo);
            $response = $proposta->submit($_POST, $_FILES, $auth->getUserId());
            break;
        
        case 'get_proposta':
            $id = $_GET['id'] ?? $_POST['id'] ?? '';
            $proposta = new Proposta($pdo);
            $data = $proposta->getById($id, $auth->isLoggedIn() ? $auth->getUserId() : null);
            $response = $data ? ['success' => true, 'data' => $data] : ['success' => false, 'error' => 'Não encontrada'];
            break;
        
        case 'list_propostas':
            $auth->requireLogin();
            $filters = array_intersect_key($_GET, array_flip(['status', 'operadora_id', 'date_from', 'search']));
            $proposta = new Proposta($pdo);
            $page = (int)($_GET['page'] ?? 1);
            $data = $proposta->list($filters, $auth->getUserId(), $page);
            $response = ['success' => true, 'data' => $data];
            break;
        
        case 'update_proposta_status':
            $auth->requireLogin(['VENDEDOR']);
            $proposta = new Proposta($pdo);
            $response = $proposta->updateStatus(
                $_POST['id'],
                $_POST['status'],
                $_POST['decisao'] ?? null,
                $auth->getUserId(),
                $auth->getUserType()
            );
            break;
        
        case 'get_propostas_stats':
            $auth->requireLogin();
            $proposta = new Proposta($pdo);
            $data = $proposta->getStats($auth->getUserId());
            $response = ['success' => true, 'data' => $data];
            break;
        
        // ===== PLANOS E OPERADORAS =====
        
        case 'get_operadoras':
            $stmt = $pdo->query("SELECT id, nome, slug, logo_url FROM operadoras WHERE ativo = 1 ORDER BY nome");
            $response = ['success' => true, 'data' => $stmt->fetchAll()];
            break;
        
        case 'get_planos':
            $operadoraId = filter_input(INPUT_GET, 'operadora_id', FILTER_VALIDATE_INT);
            $categoria = $_GET['categoria'] ?? 'COM_COPARTICIPACAO';
            
            $stmt = $pdo->prepare("
                SELECT id, operadora_id, nome, categoria, cobertura,
                       faixa_0_18, faixa_19_28, faixa_29_43, faixa_44_58, faixa_59_plus
                FROM planos 
                WHERE operadora_id = ? AND categoria = ? AND ativo = 1
                ORDER BY nome
            ");
            $stmt->execute([$operadoraId, $categoria]);
            $response = ['success' => true, 'data' => $stmt->fetchAll()];
            break;
        
        case 'calcular_valor':
            // Endpoint para cálculo em tempo real no frontend
            $idade = (int)($_POST['idade'] ?? 30);
            $planoId = (int)($_POST['plano_id'] ?? 0);
            $dependentes = (int)($_POST['dependentes'] ?? 0);
            
            if ($planoId) {
                $stmt = $pdo->prepare("SELECT faixa_0_18, faixa_19_28, faixa_29_43, faixa_44_58, faixa_59_plus FROM planos WHERE id = ?");
                $stmt->execute([$planoId]);
                $plano = $stmt->fetch();
                
                if ($plano) {
                    if ($idade <= 18) $base = $plano['faixa_0_18'];
                    elseif ($idade <= 28) $base = $plano['faixa_19_28'];
                    elseif ($idade <= 43) $base = $plano['faixa_29_43'];
                    elseif ($idade <= 58) $base = $plano['faixa_44_58'];
                    else $base = $plano['faixa_59_plus'];
                    
                    $total = $base * (1 + ($dependentes * 0.5));
                    $response = ['success' => true, 'valor' => round($total, 2), 'base' => $base];
                    break;
                }
            }
            $response = ['success' => false, 'error' => 'Plano não encontrado'];
            break;
        
        // ===== PAGAMENTOS =====
        
        case 'confirm_payment':
            $paymentId = $_POST['payment_id'] ?? '';
            $payment = new Payment();
            $status = $payment->getPayment($paymentId);
            
            if ($status['success'] && $status['data']['status'] === 'CONFIRMED') {
                // Ativar conta
                $stmt = $pdo->prepare("SELECT entity_type, entity_id FROM pagamentos WHERE asaas_payment_id = ?");
                $stmt->execute([$paymentId]);
                $record = $stmt->fetch();
                
                if ($record) {
                    $table = $record['entity_type'] === 'EMPRESA' ? 'empresas' : 'vendedores';
                    $pdo->prepare("UPDATE $table SET status = 'ATIVO', pix_status = 'CONFIRMED', updated_at = NOW() WHERE id = ?")
                        ->execute([$record['entity_id']]);
                    
                    log_system('INFO', 'PAYMENT_MANUALLY_CONFIRMED', [
                        'asaas_id' => $paymentId,
                        'entity' => $record['entity_type']
                    ]);
                }
                
                $response = ['success' => true, 'message' => 'Pagamento confirmado! Conta ativada.'];
            } else {
                $response = [
                    'success' => false, 
                    'message' => 'Pagamento ainda não confirmado. Status: ' . ($status['data']['status'] ?? 'desconhecido')
                ];
            }
            break;
        
        case 'check_payment_status':
            $paymentId = $_POST['payment_id'] ?? '';
            $payment = new Payment();
            $status = $payment->getPayment($paymentId);
            
            if ($status['success']) {
                $response = [
                    'success' => true,
                    'status' => $status['data']['status'],
                    'confirmed_at' => $status['data']['confirmedDate'] ?? null
                ];
            } else {
                $response = $status;
            }
            break;
        
        // ===== UTILITÁRIOS =====
        
        case 'search_cep':
            $cep = preg_replace('/\D/', '', $_GET['cep'] ?? '');
            if (strlen($cep) !== 8) {
                $response = ['success' => false, 'error' => 'CEP inválido'];
                break;
            }
            
            $result = file_get_contents("https://viacep.com.br/ws/$cep/json/");
            $data = json_decode($result, true);
            
            if (!empty($data['erro'])) {
                $response = ['success' => false, 'error' => 'CEP não encontrado'];
            } else {
                $response = ['success' => true, 'data' => $data];
            }
            break;
        
        case 'upload_test':
            // Endpoint para teste de upload
            $auth->requireLogin();
            $upload = new Upload();
            $result = $upload->upload('file', 'TEST', $auth->getUserId(), 'test');
            $response = $result ? ['success' => true, 'data' => $result] : ['success' => false, 'error' => $upload->getLastError()];
            break;
        
        default:
            throw new Exception('Ação não reconhecida: ' . $action);
    }
    
} catch (Exception $e) {
    $debug = defined('APP_DEBUG') && APP_DEBUG;
    log_system('ERROR', 'AJAX_HANDLER_ERROR', [
        'action' => $action ?? 'unknown',
        'error' => $e->getMessage(),
        'trace' => $debug ? $e->getTraceAsString() : null
    ]);
    
    $response = [
        'success' => false, 
        'error' => $debug ? $e->getMessage() : 'Erro interno. Tente novamente.'
    ];
    http_response_code(400);
}

// Output JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
exit;
?>
