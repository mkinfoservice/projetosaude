<?php
/**
 * api/Payment.php
 * Integração com API Asaas para pagamentos PIX
 */

require_once __DIR__ . '/config.php';

class Payment {
    private $apiUrl;
    private $apiKey;
    private $timeout = 30;
    
    public function __construct($sandbox = true) {
        $this->apiUrl = $sandbox 
            ? 'https://api-sandbox.asaas.com/v3' 
            : 'https://api.asaas.com/v3';
        $this->apiKey = ASAAS_API_KEY;
    }
    
    // ===== CRIAR CLIENTE NO ASAAS =====
    
    public function createCustomer($name, $cpfCnpj, $email, $phone = null, $postalCode = null) {
        $endpoint = $this->apiUrl . '/customers';
        
        $payload = [
            'name' => $name,
            'cpfCnpj' => preg_replace('/\D/', '', $cpfCnpj),
            'email' => $email,
            'phone' => $phone ? preg_replace('/\D/', '', $phone) : null,
            'postalCode' => $postalCode ? preg_replace('/\D/', '', $postalCode) : '20000-000',
            'addressNumber' => 'S/N',
            'province' => 'Centro',
            'externalReference' => null,
            'notificationDisabled' => false,
            'additionalEmails' => [],
            'mobilePhone' => null
        ];
        
        // Remover campos nulos
        $payload = array_filter($payload, fn($v) => $v !== null);
        
        return $this->request('POST', $endpoint, $payload);
    }
    
    // ===== CRIAR PAGAMENTO PIX =====
    
    public function createPixPayment($params) {
        $endpoint = $this->apiUrl . '/payments';
        
        $payload = [
            'customer' => $params['customer_id'] ?? $params['cpfCnpj'], // ID ou CPF/CNPJ
            'billingType' => 'PIX',
            'value' => number_format($params['amount'], 2, '.', ''),
            'description' => $params['description'] ?? 'Pagamento - Concessionária Bem',
            'externalReference' => $params['external_reference'] ?? uniqid('PIX_'),
            'dueDate' => $params['due_date'] ?? date('Y-m-d', strtotime('+3 days')),
            'postalCode' => $params['postal_code'] ?? '20000-000',
            'cpfCnpj' => $params['cpfCnpj'] ?? EMPRESA_CNPJ,
            'personType' => strlen(preg_replace('/\D/', '', $params['cpfCnpj'] ?? '')) > 11 ? 'JURIDICA' : 'FISICA',
            'notificationEnabled' => true,
            'remoteIp' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'interest' => ['value' => 0],
            'fine' => ['value' => 0],
            'discount' => ['value' => 0]
        ];
        
        return $this->request('POST', $endpoint, $payload);
    }
    
    // ===== CONSULTAR PAGAMENTO =====
    
    public function getPayment($paymentId) {
        $endpoint = $this->apiUrl . '/payments/' . urlencode($paymentId);
        return $this->request('GET', $endpoint);
    }
    
    public function getPaymentByExternalRef($externalRef) {
        $endpoint = $this->apiUrl . '/payments?externalReference=' . urlencode($externalRef);
        $result = $this->request('GET', $endpoint);
        
        if ($result['success'] && isset($result['data']['data'][0])) {
            $result['data'] = $result['data']['data'][0];
        }
        return $result;
    }
    
    // ===== LISTAR PAGAMENTOS =====
    
    public function listPayments($filters = []) {
        $endpoint = $this->apiUrl . '/payments';
        $query = http_build_query(array_filter($filters));
        if ($query) $endpoint .= '?' . $query;
        
        return $this->request('GET', $endpoint);
    }
    
    // ===== WEBHOOK =====
    
    public function verifyWebhookSignature($payload, $signature) {
        // Asaas não usa assinatura HMAC por padrão no sandbox
        // Em produção, validar conforme documentação oficial
        return true;
    }
    
    // ===== REQUEST HTTP =====
    
    private function request($method, $url, $data = null) {
        $ch = curl_init();
        
        $headers = [
            'access_token: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            log_system('ERROR', 'ASAAS_CURL_ERROR', ['error' => $error, 'url' => $url]);
            return ['success' => false, 'error' => 'Erro de conexão: ' . $error];
        }
        
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 400) {
            log_system('WARNING', 'ASAAS_API_ERROR', [
                'http_code' => $httpCode,
                'url' => $url,
                'response' => $decoded
            ]);
            return [
                'success' => false,
                'error' => $decoded['errors'][0]['description'] ?? 'Erro na API Asaas',
                'http_code' => $httpCode,
                'raw' => $decoded
            ];
        }
        
        return ['success' => true, 'data' => $decoded];
    }
    
    // ===== HELPERS =====
    
    public function getStatusLabel($status) {
        $labels = [
            'PENDING' => 'Pendente',
            'RECEIVED' => 'Recebido',
            'CONFIRMED' => 'Confirmado',
            'OVERDUE' => 'Vencido',
            'REFUNDED' => 'Estornado',
            'FAILED' => 'Falhou'
        ];
        return $labels[$status] ?? $status;
    }
    
    public function generateFallbackQR($amount, $reference) {
        $qrData = sprintf(
            "PIX_CONCESSIONARIA_BEM_%s_R%.2f",
            urlencode($reference),
            $amount
        );
        return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrData);
    }
}
?>