<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php'; // Nome correto do seu arquivo de conexão

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'POST') {
    $dados = json_decode(file_get_contents('php://input'), true);
    
    if (!empty($dados['nome']) && !empty($dados['cpf'])) {
        try {
            // SQL ajustado para a tabela 'propostas' que existe no seu banco físico
            $sql = "INSERT INTO propostas (cliente_nome, cliente_cpf, plano, status_geral) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $dados['nome'], 
                $dados['cpf'], 
                $dados['plano'] ?? 'Não informado', 
                'Pendente'
            ]);
            
            echo json_encode(["status" => "sucesso", "mensagem" => "Proposta gravada no Banco Físico!"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "Dados incompletos"]);
    }
}

if ($metodo === 'GET') {
    $stmt = $pdo->query("SELECT * FROM propostas ORDER BY id DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>