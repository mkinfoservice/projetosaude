<?php
/**
 * app/services/AsaasService.php
 * Integração com a API Asaas (PIX, clientes, cobranças)
 *
 * Concessionária Inteligente Bem
 *
 * Documentação: https://docs.asaas.com
 * Sandbox:      https://api-sandbox.asaas.com/v3
 * Produção:     https://api.asaas.com/v3
 */

declare(strict_types=1);

class AsaasService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = ASAAS_API_URL;
        $this->apiKey  = ASAAS_API_KEY;
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Cria ou recupera cliente no Asaas pelo CPF/CNPJ
     *
     * @return array ['id' => 'cus_xxx', ...]
     * @throws RuntimeException
     */
    public function criarCliente(string $nome, string $cpfCnpj, string $email, string $telefone): array
    {
        $cpfCnpj = preg_replace('/\D/', '', $cpfCnpj);

        // Verifica se cliente já existe
        $existente = $this->get('/customers', ['cpfCnpj' => $cpfCnpj]);
        if (!empty($existente['data'][0]['id'])) {
            return $existente['data'][0];
        }

        return $this->post('/customers', [
            'name'        => $nome,
            'cpfCnpj'     => $cpfCnpj,
            'email'       => $email,
            'mobilePhone' => preg_replace('/\D/', '', $telefone),
        ]);
    }

    /**
     * Cria cobrança PIX
     *
     * @return array {
     *   id: string,
     *   status: string,
     *   value: float,
     *   pixTransaction: { qrCode: string, payload: string, expirationDate: string }
     * }
     * @throws RuntimeException
     */
    public function criarCobrancaPix(
        string $customerId,
        float  $valor,
        string $descricao,
        int    $vencimentoDias = 3
    ): array {
        $dueDate = date('Y-m-d', strtotime("+{$vencimentoDias} days"));

        $payment = $this->post('/payments', [
            'customer'              => $customerId,
            'billingType'           => 'PIX',
            'value'                 => $valor,
            'dueDate'               => $dueDate,
            'description'           => $descricao,
            'externalReference'     => 'CIB_' . time(),
            'postalService'         => false,
        ]);

        if (empty($payment['id'])) {
            throw new RuntimeException('Falha ao criar cobrança: ' . json_encode($payment));
        }

        // Busca QR Code PIX
        $pixInfo = $this->get("/payments/{$payment['id']}/pixQrCode");
        $payment['pixTransaction'] = $pixInfo;

        return $payment;
    }

    /**
     * Busca status de um pagamento
     */
    public function buscarPagamento(string $paymentId): array
    {
        return $this->get("/payments/{$paymentId}");
    }

    /**
     * Valida o token do webhook Asaas
     */
    public static function validarWebhookToken(string $token): bool
    {
        return !empty(ASAAS_WEBHOOK_TOKEN) && hash_equals(ASAAS_WEBHOOK_TOKEN, $token);
    }

    // ------------------------------------------------------------------
    // HTTP helpers
    // ------------------------------------------------------------------

    private function get(string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $this->request('GET', $url);
    }

    private function post(string $endpoint, array $body): array
    {
        return $this->request('POST', $this->baseUrl . $endpoint, $body);
    }

    private function request(string $method, string $url, array $body = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Chave da API Asaas não configurada. Defina ASAAS_API_KEY no .env');
        }

        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'access_token: ' . $this->apiKey,
                'Content-Type: application/json',
                'User-Agent: CIB/1.0',
            ],
        ];

        if ($method === 'POST') {
            $opts[CURLOPT_POST]       = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        $ch  = curl_init();
        curl_setopt_array($ch, $opts);
        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new RuntimeException("Erro cURL ao chamar Asaas: $err");
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException("Resposta inválida da Asaas (HTTP $code): $raw");
        }

        if ($code >= 400) {
            $msg = $data['errors'][0]['description'] ?? $data['message'] ?? "HTTP $code";
            throw new RuntimeException("Asaas retornou erro: $msg");
        }

        return $data;
    }
}
