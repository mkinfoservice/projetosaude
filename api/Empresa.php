<?php
/**
 * api/Empresa.php
 * Operações de CRUD para empresas
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/payment.php';
require_once __DIR__ . '/Upload.php';

class Empresa {
    private $pdo;
    private $auth;
    
    public function __construct($pdo = null) {
        $this->pdo = $pdo ?? db();
        $this->auth = new Auth($this->pdo);
    }
    
    // ===== CADASTRAR EMPRESA =====
    
    public function register($data, $files) {
        try {
            // Validar dados
            $errors = $this->validate($data, $files);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Upload do contrato social
            $upload = new Upload();
            $uploadResult = $upload->upload('contrato_social', 'CONTRATO', 0, 'social');
            if (!$uploadResult) {
                return ['success' => false, 'errors' => ['contrato_social' => $upload->getLastError()]];
            }
            
            // Hash da senha
            $senhaHash = $this->auth->hashPassword($data['senha']);
            
            // Inserir empresa (status pendente até pagamento)
            $empresaData = [
                'cnpj' => preg_replace('/\D/', '', $data['cnpj']),
                'razao_social' => sanitize($data['razao_social'] ?? ''),
                'cep' => preg_replace('/\D/', '', $data['cep']),
                'endereco' => sanitize($data['endereco']),
                'bairro' => sanitize($data['bairro']),
                'numero' => sanitize($data['numero']),
                'telefone' => preg_replace('/\D/', '', $data['telefone']),
                'email' => filter_var($data['email'], FILTER_VALIDATE_EMAIL),
                'senha_hash' => $senhaHash,
                'contrato_social_path' => $uploadResult['filename'],
                'status' => 'PENDENTE_PAGAMENTO'
            ];
            
            $db = dbClass();
            $empresaId = $db->insert('empresas', $empresaData);
            
            // Gerar PIX via Asaas
            $payment = new Payment();
            
            // Tentar criar cliente no Asaas primeiro
            $customerResult = $payment->createCustomer(
                $empresaData['razao_social'] ?: 'Empresa',
                $empresaData['cnpj'],
                $empresaData['email'],
                $empresaData['telefone'],
                $empresaData['cep']
            );
            
            $customerId = $customerResult['success'] 
                ? ($customerResult['data']['id'] ?? $empresaData['cnpj'])
                : $empresaData['cnpj'];
            
            $pixResult = $payment->createPixPayment([
                'customer_id' => $customerId,
                'cpfCnpj' => $empresaData['cnpj'],
                'amount' => VALOR_CADASTRO_EMPRESA,
                'description' => 'Cadastro Empresa - Concessionária Bem',
                'external_reference' => 'EMP_' . $empresaId,
                'postal_code' => $empresaData['cep']
            ]);
            
            if ($pixResult['success']) {
                // Salvar pagamento
                $db->insert('pagamentos', [
                    'entity_type' => 'EMPRESA',
                    'entity_id' => $empresaId,
                    'asaas_payment_id' => $pixResult['data']['id'],
                    'valor' => VALOR_CADASTRO_EMPRESA,
                    'descricao' => 'Cadastro Empresa',
                    'pix_qr_code' => $pixResult['data']['pixQrCode'] ?? null,
                    'pix_copia_cola' => $pixResult['data']['pixCopiaECola'] ?? null,
                    'status' => 'PENDING'
                ]);
                
                log_system('INFO', 'EMPRESA_REGISTERED_WITH_PIX', [
                    'empresa_id' => $empresaId,
                    'asaas_payment_id' => $pixResult['data']['id']
                ]);
                
                return [
                    'success' => true,
                    'empresa_id' => $empresaId,
                    'pix_qr_code' => $pixResult['data']['pixQrCode'],
                    'pix_copia_cola' => $pixResult['data']['pixCopiaECola'],
                    'invoice_url' => $pixResult['data']['invoiceUrl'] ?? null,
                    'payment_id' => $pixResult['data']['id'],
                    'next_step' => 'show_payment_modal'
                ];
            } else {
                // Fallback: registrar sem PIX automático
                log_system('WARNING', 'EMPRESA_REGISTERED_FALLBACK_PIX', [
                    'empresa_id' => $empresaId,
                    'asaas_error' => $pixResult['error']
                ]);
                
                return [
                    'success' => true,
                    'empresa_id' => $empresaId,
                    'qr_fallback' => $payment->generateFallbackQR(VALOR_CADASTRO_EMPRESA, 'EMP_' . $empresaId),
                    'message' => 'Cadastro realizado! PIX gerado com método alternativo.',
                    'next_step' => 'payment_pending'
                ];
            }
            
        } catch (Exception $e) {
            log_system('ERROR', 'EMPRESA_REGISTER_ERROR', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['system' => 'Erro ao processar cadastro']];
        }
    }
    
    // ===== VALIDAR DADOS =====
    
    private function validate($data, $files) {
        $errors = [];
        
        // CNPJ
        $cnpj = preg_replace('/\D/', '', $data['cnpj'] ?? '');
        if (!validarCNPJ($cnpj)) {
            $errors['cnpj'] = 'CNPJ inválido';
        }
        
        // Email
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido';
        }
        
        // Verificar duplicidade
        if (empty($errors)) {
            $stmt = $this->pdo->prepare("SELECT id FROM empresas WHERE cnpj = ? OR email = ?");
            $stmt->execute([$cnpj, $data['email']]);
            if ($stmt->fetch()) {
                $errors['duplicate'] = 'CNPJ ou E-mail já cadastrado';
            }
        }
        
        // Senha
        if (strlen($data['senha'] ?? '') < PASSWORD_MIN_LENGTH) {
            $errors['senha'] = 'Senha deve ter no mínimo ' . PASSWORD_MIN_LENGTH . ' caracteres';
        } elseif (PASSWORD_REQUIRE_MIXED && !preg_match('/[a-zA-Z]/', $data['senha']) || !preg_match('/\d/', $data['senha'])) {
            $errors['senha'] = 'Senha deve conter letras e números';
        }
        
        // Telefone
        $telefone = preg_replace('/\D/', '', $data['telefone'] ?? '');
        if (strlen($telefone) < 10) {
            $errors['telefone'] = 'Telefone inválido';
        }
        
        // CEP
        $cep = preg_replace('/\D/', '', $data['cep'] ?? '');
        if (strlen($cep) !== 8) {
            $errors['cep'] = 'CEP inválido';
        }
        
        // Arquivo
        if (!isset($files['contrato_social']) || $files['contrato_social']['error'] !== UPLOAD_ERR_OK) {
            $errors['contrato_social'] = 'Contrato social é obrigatório';
        }
        
        return $errors;
    }
    
    // ===== BUSCAR EMPRESA =====
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT id, cnpj, razao_social, cep, endereco, bairro, numero, telefone, email, 
                   contrato_social_path, status, created_at 
            FROM empresas WHERE id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getByCnpj($cnpj) {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        $stmt = $this->pdo->prepare("
            SELECT id, cnpj, razao_social, status 
            FROM empresas WHERE cnpj = ? LIMIT 1
        ");
        $stmt->execute([$cnpj]);
        return $stmt->fetch();
    }
    
    // ===== ATUALIZAR EMPRESA =====
    
    public function update($id, $data) {
        $allowed = ['razao_social', 'cep', 'endereco', 'bairro', 'numero', 'telefone', 'email'];
        $updateData = array_intersect_key($data, array_flip($allowed));
        
        if (empty($updateData)) return 0;
        
        return dbClass()->update('empresas', $updateData, ['id' => ':id'], ['id' => $id]);
    }
    
    // ===== LISTAR EMPRESAS (Admin) =====
    
    public function list($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . dbClass()->escapeLike($filters['search']) . '%';
            $where[] = '(razao_social LIKE :search OR cnpj LIKE :search OR email LIKE :search)';
            $params['search'] = $search;
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Total para paginação
        $totalStmt = $this->pdo->prepare("SELECT COUNT(*) FROM empresas $whereClause");
        $totalStmt->execute($params);
        $total = $totalStmt->fetchColumn();
        
        // Dados
        $stmt = $this->pdo->prepare("
            SELECT id, cnpj, razao_social, email, telefone, status, created_at 
            FROM empresas $whereClause 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'data' => $stmt->fetchAll(),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }
    
    // ===== ATIVAR EMPRESA (após pagamento confirmado) =====
    
    public function activate($id) {
        $stmt = $this->pdo->prepare("
            UPDATE empresas SET status = 'ATIVO', updated_at = NOW() WHERE id = ?
        ");
        $stmt->execute([$id]);
        
        log_system('INFO', 'EMPRESA_ACTIVATED', ['empresa_id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    // ===== DASHBOARD EMPRESA =====
    
    public function getDashboardData($empresaId) {
        // Vendedores vinculados
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total, 
                   SUM(CASE WHEN status = 'ATIVO' THEN 1 ELSE 0 END) as ativos
            FROM vendedores WHERE empresa_id = ?
        ");
        $stmt->execute([$empresaId]);
        $vendedores = $stmt->fetch();
        
        // Propostas recentes
        $stmt = $this->pdo->prepare("
            SELECT p.protocolo, p.titular_nome, o.nome as operadora, p.status, p.created_at,
                   v.nome_completo as vendedor
            FROM propostas p
            JOIN operadoras o ON p.operadora_id = o.id
            JOIN vendedores v ON p.vendedor_id = v.id
            WHERE v.empresa_id = ?
            ORDER BY p.created_at DESC LIMIT 10
        ");
        $stmt->execute([$empresaId]);
        $propostas = $stmt->fetchAll();
        
        // Estatísticas
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_propostas,
                SUM(CASE WHEN status = 'APROVADA' THEN 1 ELSE 0 END) as aprovadas,
                SUM(CASE WHEN status = 'EM_ANALISE' THEN 1 ELSE 0 END) as em_analise
            FROM propostas p
            JOIN vendedores v ON p.vendedor_id = v.id
            WHERE v.empresa_id = ?
        ");
        $stmt->execute([$empresaId]);
        $stats = $stmt->fetch();
        
        return [
            'vendedores' => $vendedores,
            'propostas_recentes' => $propostas,
            'estatisticas' => $stats
        ];
    }
}
?>
