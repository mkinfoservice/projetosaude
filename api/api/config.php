<?php
// Credenciais da Locaweb (altere conforme seu painel)
define('DB_HOST', 'cardsaude_db.mysql.dbaas.com.br');
define('DB_NAME', 'cardsaude_db');
define('DB_USER', 'cardsaude_db');
define('DB_PASS', 'L91718104Ruth@');

try {
  $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['erro' => 'Erro na conexão com o banco.']);
  exit;
}
?>