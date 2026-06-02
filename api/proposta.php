<?php
/**
 * api/Proposta.php
 * Gestão de propostas e cotações
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/Upload.php';

class Proposta {
    private $pdo;
    
    public function __construct($pdo = null) {
        $this->pdo = $pdo ?? db();
    }
    
    // ===== ENVIAR PROPOSTA =====
    
    public function submit($data, $files, $vendedorId) {
        try {
            // Validar dados
            $errors = $this->validate($data, $files);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Uploads de documentos
            $upload = new Upload();
            $uploads = $upload->uploadMultiple([
                'doc_frente' => ['prefix' => 'PROP_FRENTE', 'entity_id' => 0, 'field_name' => 'frente'],
                'doc_verso' => ['prefix' => 'PROP_VERSO', 'entity_id' => 0, 'field_name' => 'verso'],
                'comprovante_residencia' => ['prefix' => 'PROP_RESID', 'entity_id' => 0, 'field_name' => 'residencia'],
                'contracheque' => ['prefix' => 'PROP_CONTR', 'entity_id' => 0, 'field_name' => 'contracheque']
            ]);
            
            if (!$uploads) {
                return ['success' => false, 'errors' => ['documents' => $upload->getLastError()]];
            }
            
            // Calcular valor total (simplificado)
            $valorTotal = $this->calcularValor($data);
            
            // Preparar dependentes como JSON
            $dependentes = !empty($data['dependentes']) 
                ? json_encode($data['dependentes'], JSON_UNESCAPED_UNICODE) 
                : null;
            
            // Gerar protocolo único
            $protocolo = gerarProtocolo('PROP');
            
            // Inserir proposta
            $propostaData = [
                'protocolo' => $protocolo,
                'vendedor_id' => $vendedorId,
                'operadora_id' => (int)$data['operadora_id'],
                'plano_id' => (int)$data['plano_id'],
                'titular_nome' => sanitize($data['titular_nome']),
                'titular_cpf' => preg_replace('/\D/', '', $data['titular_cpf']),
                'titular_rg' => sanitize($data['titular_rg'] ?? ''),
                'titular_nascimento' => $data['titular_nascimento'],
                'titular_sexo' => $data['titular_sexo'] ?? null,
                'titular_estado_civil' => $data['titular_estado_civil'] ?? null,
                'titular_nome_mae' => sanitize($data['titular_nome_mae']),
                'cep' => preg_replace('/\D/', '', $data['cep']),
                'cidade_uf' => sanitize($data['cidade_uf'] ?? ''),
                'bairro' => sanitize($data['bairro']),
                'numero' => sanitize($data['numero']),
                'endereco_completo' => sanitize($data['endereco']),
                'complemento' => sanitize($data['complemento'] ?? ''),
                'telefone' => preg_replace('/\D/', '', $data['telefone']),
                'email' => filter_var($data['email'], FILTER_VALIDATE_EMAIL),
                'entidade' => sanitize($data['entidade'] ?? ''),
                'profissao' => sanitize($data['profissao']),
                'cobertura' => $data['cobertura'] ?? 'PRATA',
                'coparticipacao' => !empty($data['coparticipacao']) ? 1 : 0,
                'doc_frente_path' => $uploads['doc_frente']['filename'],
                'doc_verso_path' => $uploads['doc_verso']['filename'],
                'comprovante_residencia_path' => $uploads['comprovante_residencia']['filename'],
                'contracheque_path' => $uploads['contracheque']['filename'],
                'status' => 'ENVIADA',
                'valor_total' => $valorTotal,
                'dependentes_json' => $dependentes
            ];
            
            $db = dbClass();
            $propostaId = $db->insert('propostas', $propostaData);
            
            log_system('INFO', 'PROPOSTA_SUBMITTED', [
                'proposta_id' => $propostaId,
                'protocolo' => $protocolo,
                'vendedor_id' => $vendedorId
            ]);
            
            return [
                'success' => true,
                'proposta_id' => $propostaId,
                'protocolo' => $protocolo,
                'message' => 'Proposta enviada com sucesso! Protocolo: ' . $protocolo
            ];
            
        } catch (Exception $e) {
            log_system('ERROR', 'PROPOSTA_SUBMIT_ERROR', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['system' => 'Erro ao enviar proposta']];
        }
    }
    
    // ===== VALIDAR =====
    
    private function validate($data, $files) {
        $errors = [];
        
        // CPF do titular
        if (!validarCPF($data['titular_cpf'] ?? '')) {
            $errors['titular_cpf'] = 'CPF do titular inválido';
        }
        
        // Data de nascimento
        if (empty($data['titular_nascimento'])) {
            $errors['titular_nascimento'] = 'Data de nascimento obrigatória';
        }
        
        // Email
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail inválido';
        }
        
        // Telefone
        $telefone = preg_replace('/\D/', '', $data['telefone'] ?? '');
        if (strlen($telefone) < 10) {
            $errors['telefone'] = 'Telefone inválido';
        }
        
        // Documentos obrigatórios
        $requiredDocs = ['doc_frente', 'doc_verso', 'comprovante_residencia'];
        foreach ($requiredDocs as $doc) {
            if (!isset($files[$doc]) || $files[$doc]['error'] !== UPLOAD_ERR_OK) {
                $errors[$doc] = 'Documento obrigatório';
            }
        }
        
        return $errors;
    }
    
    // ===== CALCULAR VALOR =====
    
    private function calcularValor($data) {
        // Buscar preço base do plano
        $stmt = $this->pdo->prepare("
            SELECT faixa_0_18, faixa_19_28, faixa_29_43, faixa_44_58, faixa_59_plus
            FROM planos WHERE id = ? LIMIT 1
        ");
        $stmt->execute([(int)$data['plano_id']]);
        $plano = $stmt->fetch();
        
        if (!$plano) return 0;
        
        // Calcular idade do titular
        $nascimento = new DateTime($data['titular_nascimento']);
        $hoje = new DateTime();
        $idade = $hoje->diff($nascimento)->y;
        
        // Selecionar faixa de preço
        if ($idade <= 18) $precoBase = $plano['faixa_0_18'];
        elseif ($idade <= 28) $precoBase = $plano['faixa_19_28'];
        elseif ($idade <= 43) $precoBase = $plano['faixa_29_43'];
        elseif ($idade <= 58) $precoBase = $plano['faixa_44_58'];
        else $precoBase = $plano['faixa_59_plus'];
        
        // Adicionar dependentes (simplificado: +50% por dependente)
        $dependentes = !empty($data['dependentes']) ? count($data['dependentes']) : 0;
        $total = $precoBase * (1 + ($dependentes * 0.5));
        
        return round($total, 2);
    }
    
    // ===== BUSCAR PROPOSTA =====
    
    public function getById($id, $vendedorId = null) {
        $sql = "
            SELECT p.*, o.nome as operadora_nome, pl.nome as plano_nome,
                   v.nome_completo as vendedor_nome
            FROM propostas p
            JOIN operadoras o ON p.operadora_id = o.id
            JOIN planos pl ON p.plano_id = pl.id
            JOIN vendedores v ON p.vendedor_id = v.id
            WHERE p.id = ?
        ";
        $params = [$id];
        
        // Filtro por vendedor (para não acessar propostas de outros)
        if ($vendedorId !== null) {
            $sql .= " AND (p.vendedor_id = ? OR v.empresa_id = (SELECT empresa_id FROM vendedores WHERE id = ?))";
            $params[] = $vendedorId;
            $params[] = $vendedorId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $proposta = $stmt->fetch();
        
        if ($proposta && $proposta['dependentes_json']) {
            $proposta['dependentes'] = json_decode($proposta['dependentes_json'], true);
        }
        
        return $proposta;
    }
    
    public function getByProtocolo($protocolo) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, o.nome as operadora_nome, pl.nome as plano_nome
            FROM propostas p
            JOIN operadoras o ON p.operadora_id = o.id
            JOIN planos pl ON p.plano_id = pl.id
            WHERE p.protocolo = ? LIMIT 1
        ");
        $stmt->execute([$protocolo]);
        return $stmt->fetch();
    }
    
    // ===== LISTAR PROPOSTAS =====
    
    public function list($filters = [], $vendedorId = null, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];
        
        // Filtro por vendedor/empresa
        if ($vendedorId) {
            $stmt = $this->pdo->prepare("SELECT perfil, empresa_id FROM vendedores WHERE id = ?");
            $stmt->execute([$vendedorId]);
            $vendedor = $stmt->fetch();
            
            if ($vendedor['perfil'] === 'VENDEDOR') {
                $where[] = 'p.vendedor_id = :vendedor_id';
                $params['vendedor_id'] = $vendedorId;
            } elseif ($vendedor['perfil'] === 'GERENTE' && $vendedor['empresa_id']) {
                $where[] = 'v.empresa_id = :empresa_id';
                $params['empresa_id'] = $vendedor['empresa_id'];
            }
            // SUPERVISOR vê tudo
        }
        
        // Filtros adicionais
        if (!empty($filters['status'])) {
            $where[] = 'p.status = :status';
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['operadora_id'])) {
            $where[] = 'p.operadora_id = :operadora_id';
            $params['operadora_id'] = $filters['operadora_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = 'p.created_at >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['search'])) {
            $search = '%' . dbClass()->escapeLike($filters['search']) . '%';
            $where[] = '(p.titular_nome LIKE :search OR p.protocolo LIKE :search)';
            $params['search'] = $search;
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Total
        $totalStmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM propostas p
            JOIN vendedores v ON p.vendedor_id = v.id
            $whereClause
        ");
        $totalStmt->execute($params);
        $total = $totalStmt->fetchColumn();
        
        // Dados
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.protocolo, p.titular_nome, p.titular_cpf, o.nome as operadora_nome,
                   p.status, p.valor_total, p.created_at, v.nome_completo as vendedor_nome,
                   v.perfil as vendedor_perfil
            FROM propostas p
            JOIN operadoras o ON p.operadora_id = o.id
            JOIN vendedores v ON p.vendedor_id = v.id
            $whereClause
            ORDER BY p.created_at DESC
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
    
    // ===== ATUALIZAR STATUS =====
    
    public function updateStatus($id, $status, $decisao = null, $userId, $userType) {
        $allowedStatus = ['RASCUNHO', 'ENVIADA', 'EM_ANALISE', 'APROVADA', 'RECUSADA', 'CANCELADA'];
        if (!in_array($status, $allowedStatus)) {
            return ['success' => false, 'error' => 'Status inválido'];
        }
        
        $data = ['status' => $status];
        
        if ($decisao !== null) {
            $field = $userType === 'GERENTE' ? 'decisao_gerente' : 'decisao_supervisor';
            $data[$field] = sanitize($decisao);
        }
        
        $updated = dbClass()->update('propostas', $data, ['id' => ':id'], ['id' => $id]);
        
        if ($updated) {
            log_system('INFO', 'PROPOSTA_STATUS_UPDATED', [
                'proposta_id' => $id,
                'status' => $status,
                'user_id' => $userId,
                'user_type' => $userType
            ]);
        }
        
        return ['success' => $updated > 0];
    }
    
    // ===== DASHBOARD PROPOSTAS =====
    
    public function getStats($vendedorId = null) {
        $where = '';
        $params = [];
        
        if ($vendedorId) {
            $where = 'WHERE vendedor_id = :vendedor_id';
            $params['vendedor_id'] = $vendedorId;
        }
        
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'ENVIADA' THEN 1 ELSE 0 END) as enviadas,
                SUM(CASE WHEN status = 'EM_ANALISE' THEN 1 ELSE 0 END) as em_analise,
                SUM(CASE WHEN status = 'APROVADA' THEN 1 ELSE 0 END) as aprovadas,
                SUM(CASE WHEN status = 'RECUSADA' THEN 1 ELSE 0 END) as recusadas,
                COALESCE(SUM(CASE WHEN status = 'APROVADA' THEN valor_total ELSE 0 END), 0) as valor_aprovado
            FROM propostas $where
        ");
        $stmt->execute($params);
        
        return $stmt->fetch();
    }
}
?>
