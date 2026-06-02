<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// 🔧 CONFIGURAÇÃO DO BANCO DE DADOS - LOCAWEB
$host = 'cardsaude_db.mysql.dbaas.com.br';
$db   = 'cardsaude_db';
$user = 'cardsaude_db';  // ← CONFIRME SEU USUÁRIO
$pass = 'L91718104Ruth@'; // ← COLOQUE SUA SENHA REAL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro conexão: ' . $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'save_proposta':
            $data = $_POST;
            
            $protocolo = 'PROT-' . date('YmdHis') . '-' . rand(1000, 9999);
            
            $stmt = $pdo->prepare("
                INSERT INTO propostas 
                (protocolo, nome, cpf, rg, data_nascimento, estado_civil, nome_mae, 
                 cep, endereco, bairro, numero, complemento, cidade_uf, 
                 telefone, email, cns, operadora_origem,
                 entidade, profissao, categoria_prof,
                 operadora, plano, valor, coparticipacao, 
                 status, vendedor, data_criacao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendente', ?, NOW())
            ");
            
            $ok = $stmt->execute([
                $protocolo,
                $data['f-nome'] ?? '',
                $data['f-cpf'] ?? '',
                $data['f-rg'] ?? '',
                $data['f-nasc'] ?? '',
                $data['f-estado'] ?? '',
                $data['f-mae'] ?? '',
                $data['f-cep'] ?? '',
                $data['f-logr'] ?? '',
                $data['f-bairro'] ?? '',
                $data['f-num'] ?? '',
                $data['f-comp'] ?? '',
                $data['f-cidade'] ?? '',
                $data['f-tel'] ?? '',
                $data['f-email'] ?? '',
                $data['f-cns'] ?? '',
                $data['f-origem'] ?? '',
                $data['f-entidade'] ?? '',
                $data['f-profissao'] ?? '',
                $data['f-cat-prof'] ?? 'publica',
                $data['f-operadora'] ?? '',
                $data['f-plano'] ?? '',
                str_replace('R$ ', '', str_replace(',', '.', $data['f-valor'] ?? '0')),
                $data['f-cobertura'] ?? 'sem',
                $data['f-vendedor'] ?? 'Corretor Ativo'
            ]);
            
            if ($ok) {
                echo json_encode(['status' => 'success', 'protocolo' => $protocolo]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Falha ao salvar']);
            }
            break;

        case 'get_propostas':
            $stmt = $pdo->query("SELECT * FROM propostas ORDER BY data_criacao DESC");
            echo json_encode($stmt->fetchAll());
            break;

        case 'update_status':
            $id = $_POST['id'] ?? '';
            $status = $_POST['status'] ?? '';
            $stmt = $pdo->prepare("UPDATE propostas SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['status' => 'success']);
            break;

        case 'buscar_cep':
            $cep = preg_replace('/[^0-9]/', '', $_GET['cep'] ?? '');
            if (strlen($cep) !== 8) {
                echo json_encode(['erro' => true]);
                break;
            }
            $url = "https://viacep.com.br/ws/{$cep}/json/";
            $response = file_get_contents($url);
            echo $response;
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Ação inválida']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>