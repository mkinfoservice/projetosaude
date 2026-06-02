<?php
/**
 * api/Vendedor.php
 * Operações de CRUD para vendedores
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/payment.php';
require_once __DIR__ . '/Upload.php';

class Vendedor {
    private $pdo;
    private $auth;
    
    public function __construct($pdo = null) {
        $this->pdo = $pdo ?? db();
        $this->auth = new Auth($this->pdo);
    }
    
    // ===== CADASTRAR VENDEDOR =====
    
    public function register($data, $files, $empresaId = null) {
        try {
            // Validar dados
            $errors = $this->validate($data, $files);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Uploads
            $upload = new Upload();
            $uploads = $upload->uploadMultiple([
                'doc_frente' => ['prefix' => 'DOC_FRENTE', 'entity_id' => 0, 'field_name' => 'frente'],
                'doc_verso' => ['prefix' => 'DOC_VERSO', 'entity_id' => 0, 'field_name' => 'verso']
            ]);
            
            if (!$uploads) {
                return ['success' => false, 'errors' => ['documents' => $upload->getLastError()]];
            }
            
            // Hash da senha
            $senhaHash = $this->auth->hashPassword($data['senha']);
            
            // Inserir vendedor
            $vendedorData = [
                'empresa_id' => $empresaId,
                'nome_completo' => sanitize($data['nome_completo']),
                'cpf' => preg_replace('/\D/', '', $data['cpf']),
                'rg' => sanitize($data['rg'] ?? ''),
                'cep' => preg_replace('/\D/', '', $data['cep']),
                'endereco' => sanitize($data['endereco']),
                'bairro' => sanitize($data['bairro']),
                'numero' => sanitize($data['numero']),
                'telefone' => preg_replace('/\D/', '', $data['telefone']),
                'email' => filter_var($data['email'], FILTER_VALIDATE_EMAIL),
                'senha_hash' => $senhaHash,
                'doc_frente_path' => $uploads['doc_frente']['filename'],
                'doc_verso_path' => $uploads['doc_verso']['filename'],
                'perfil' => $data['perfil'] ?? 'VENDEDOR',
                'status' => 'PENDENTE_PAGAMENTO',
                'comissao_percentual' => $data['comissao'] ?? 10.00
            ];
            
            $db = dbClass();
            $vendedorId = $db->insert('vendedores', $vendedorData);
            
            // Gerar PIX
            $payment = new Payment();
            $externalRef = 'VEND_' . $vendedorId;
            
            $pixResult = $payment->createPixPayment([
                'cpfCnpj' => $vendedorData['cpf'],
                'amount' => VALOR_CADASTRO_VENDEDOR,
                'description' => 'Cadastro Vendedor - Concessionária Bem',
                'external_reference' => $externalRef,
                'postal_code' => $vendedorData['cep']
            ]);
            
            if ($pixResult['success']) {
                // Salvar pagamento
                $db->insert('pagamentos', [
                    'entity_type' => 'VENDEDOR',
                    'entity_id' => $vendedorId,
                    'asaas_payment_id' => $pixResult['data']['id'],
                    'valor' => VALOR_CADASTRO_VENDEDOR,
                    'descricao' => 'Cadastro Vendedor',
                    'pix_qr_code' => $pixResult['data']['pixQrCode'] ?? null,
                    'pix_copia_cola' => $pixResult['data']['pixCopiaECola'] ?? null,
                    'status' => 'PENDING'
                ]);
                
                log_system('INFO', 'VENDEDOR_REGISTERED_WITH_PIX', [
                    'vendedor_id' => $vendedorId,
                    'asaas_payment_id' => $pixResult['data']['id']
                ]);
                
                return [
                    'success' => true,
                    'vendedor_id' => $vendedorId,
                    'pix_qr_code' => $pixResult['data']['pixQrCode'],
                    'pix_copia_cola' => $pixResult['data']['pixCopiaECola'],
                    'payment_id' => $pixResult['data']['id'],
                    'next_step' => 'show_payment_modal'
                ];
            }
            
            // Fallback
            return [
                'success' => true,
                'vendedor_id' => $vendedorId,
                'qr_fallback' => $payment->generateFallbackQR(VALOR_CADASTRO_VENDEDOR, $externalRef),
                'message' => 'Cadastro realizado! PIX gerado com método alternativo.',
                'next_step' => 'payment_pending'
            ];
            
        } catch (Exception $e) {
            log_system('ERROR', 'VENDEDOR_REGISTER_ERROR', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['system' => 'Erro ao processar cadastro']];
        }
    }
    
    // ===== VALIDAR =====
    
    private function validate($data, $files) {
        $errors = [];
        
        // CPF
        if (!validarCPF($data['cpf'] ?? '')) {
            $errors['cpf'] = 'CPF inválido';
        }
        
        // Email
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido';
        }
        
        // Telefone
        $telefone = preg_replace('/\D/', '', $data['telefone'] ?? '');
        if (strlen($telefone) < 10) {
            $errors['telefone'] = 'Telefone inválido';
        }
        
        // Duplicidade
        if (empty($errors)) {
            $stmt = $this->pdo->prepare("SELECT id FROM vendedores WHERE cpf = ? OR email = ? OR telefone = ?");
            $stmt->execute([
                preg_replace('/\D/', '', $data['cpf']),
                $data['email'],
                $telefone
            ]);
            if ($stmt->fetch()) {
                $errors['duplicate'] = 'CPF, E-mail ou Telefone já cadastrado';
            }
        }
        
        // Senha
        if (strlen($data['senha'] ?? '') < PASSWORD_MIN_LENGTH) {
            $errors['senha'] = 'Senha deve ter no mínimo ' . PASSWORD_MIN_LENGTH . ' caracteres';
        }
        
        // Documentos
        foreach (['doc_frente', 'doc_verso'] as $field) {
            if (!isset($files[$field]) || $files[$field]['error'] !== UPLOAD_ERR_OK) {
                $errors[$field] = 'Documento obrigatório';
            }
        }
        
        return $errors;
    }
    
    // ===== IDENTIFICAR VENDEDOR POR TELEFONE =====
    
    public function identifyByPhone($telefone) {
        $telefone = preg_replace('/\D/', '', $telefone);
        
        $stmt = $this->pdo->prepare("
            SELECT v.id, v.nome_completo, v.perfil, v.status, v.empresa_id, e.razao_social
            FROM vendedores v
            LEFT JOIN empresas e ON v.empresa_id = e.id
            WHERE v.telefone = ? LIMIT 1
        ");
        $stmt->execute([$telefone]);
        $vendedor = $stmt->fetch();
        
        if (!$vendedor) {
            return ['success' => false, 'message' => 'Vendedor não encontrado'];
        }
        
        if ($vendedor['status'] !== 'ATIVO') {
            return ['success' => false, 'message' => 'Conta não está ativa'];
        }
        
        return [
            'success' => true,
            'data' => [
                'id' => $vendedor['id'],
                'nome' => $vendedor['nome_completo'],
                'perfil' => $vendedor['perfil'],
                'empresa' => $vendedor['razao_social']
            ]
        ];
    }
    
    // ===== BUSCAR VENDEDOR =====
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT v.*, e.razao_social as empresa_nome
            FROM vendedores v
            LEFT JOIN empresas e ON v.empresa_id = e.id
            WHERE v.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // ===== LISTAR VENDEDORES =====
    
    public function list($filters = [], $empresaId = null, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        
        if ($empresaId) {
            $where[] = 'empresa_id = :empresa_id';
            $params['empresa_id'] = $empresaId;
        }
        
        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['perfil'])) {
            $where[] = 'perfil = :perfil';
            $params['perfil'] = $filters['perfil'];
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . dbClass()->escapeLike($filters['search']) . '%';
            $where[] = '(nome_completo LIKE :search OR cpf LIKE :search OR email LIKE :search)';
            $params['search'] = $search;
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Total
        $totalStmt = $this->pdo->prepare("SELECT COUNT(*) FROM vendedores $whereClause");
        $totalStmt->execute($params);
        $total = $totalStmt->fetchColumn();
        
        // Dados
        $stmt = $this->pdo->prepare("
            SELECT v.id, v.nome_completo, v.cpf, v.telefone, v.email, v.perfil, v.status, 
                   v.comissao_percentual, v.created_at, e.razao_social as empresa_nome
            FROM vendedores v
            LEFT JOIN empresas e ON v.empresa_id = e.id
            $whereClause
            ORDER BY v.created_at DESC
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
    
    // ===== ATIVAR VENDEDOR =====
    
    public function activate($id) {
        $stmt = $this->pdo->prepare("
            UPDATE vendedores SET status = 'ATIVO', updated_at = NOW() WHERE id = ?
        ");
        $stmt->execute([$id]);
        
        log_system('INFO', 'VENDEDOR_ACTIVATED', ['vendedor_id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    // ===== DASHBOARD VENDEDOR =====
    
    public function getDashboardData($vendedorId) {
        // Minhas propostas
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'APROVADA' THEN 1 ELSE 0 END) as aprovadas,
                SUM(CASE WHEN status = 'EM_ANALISE' THEN 1 ELSE 0 END) as em_analise,
                SUM(CASE WHEN status = 'RECUSADA' THEN 1 ELSE 0 END) as recusadas
            FROM propostas WHERE vendedor_id = ?
        ");
        $stmt->execute([$vendedorId]);
        $propostas = $stmt->fetch();
        
        // Comissões
        $stmt = $this->pdo->prepare("
            SELECT 
                v.comissao_percentual,
                COALESCE(SUM(p.valor_total * v.comissao_percentual / 100), 0) as comissao_total
            FROM vendedores v
            LEFT JOIN propostas p ON p.vendedor_id = v.id AND p.status = 'APROVADA'
            WHERE v.id = ?
            GROUP BY v.id
        ");
        $stmt->execute([$vendedorId]);
        $comissoes = $stmt->fetch();
        
        // Propostas recentes
        $stmt = $this->pdo->prepare("
            SELECT protocolo, titular_nome, operadora_id, status, valor_total, created_at
            FROM propostas WHERE vendedor_id = ?
            ORDER BY created_at DESC LIMIT 10
        ");
        $stmt->execute([$vendedorId]);
        $recentes = $stmt->fetchAll();
        
        return [
            'propostas' => $propostas,
            'comissoes' => $comissoes,
            'recentes' => $recentes
        ];
    }
}
?>
