<?php
/**
 * api/Database.php
 * Classe de abstração para operações de banco de dados
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;
    
    // Singleton pattern
    private function __construct() {
        $this->pdo = getDBConnection();
    }
    
    private function __clone() {}
    private function __wakeup() {}
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // ===== CRUD GENÉRICO =====
    
    // Insert com retorno do ID
    public function insert($table, $data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            log_system('ERROR', 'DB_INSERT_ERROR', ['table' => $table, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    // Update
    public function update($table, $data, $where, $whereValues) {
        try {
            $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $whereClause = implode(' AND ', array_map(fn($k) => "$k = :w_$k", array_keys($whereValues)));
            
            // Merge params
            $params = $data;
            foreach ($whereValues as $k => $v) {
                $params["w_$k"] = $v;
            }
            
            $sql = "UPDATE $table SET $set WHERE $whereClause";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            log_system('ERROR', 'DB_UPDATE_ERROR', ['table' => $table, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    // Delete
    public function delete($table, $where, $whereValues) {
        try {
            $whereClause = implode(' AND ', array_map(fn($k) => "$k = :$k", array_keys($whereValues)));
            $sql = "DELETE FROM $table WHERE $whereClause";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($whereValues);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            log_system('ERROR', 'DB_DELETE_ERROR', ['table' => $table, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    // Select simples
    public function select($table, $columns = '*', $where = null, $whereValues = [], $orderBy = null, $limit = null) {
        try {
            $sql = "SELECT $columns FROM $table";
            
            if ($where) {
                $whereClause = implode(' AND ', array_map(fn($k) => "$k = :$k", is_array($where) ? $where : [$where]));
                $sql .= " WHERE $whereClause";
            }
            
            if ($orderBy) $sql .= " ORDER BY $orderBy";
            if ($limit) $sql .= " LIMIT $limit";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($whereValues);
            
            return $limit === 1 ? $stmt->fetch() : $stmt->fetchAll();
        } catch (PDOException $e) {
            log_system('ERROR', 'DB_SELECT_ERROR', ['table' => $table, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    // Query personalizada com prepared statement
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            log_system('ERROR', 'DB_QUERY_ERROR', ['sql' => $sql, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    // Transaction helpers
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollBack() {
        return $this->pdo->rollBack();
    }
    
    // Escape para LIKE
    public function escapeLike($value) {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}

// Helper function para acesso rápido
function db() {
    return Database::getInstance()->getConnection();
}

function dbClass() {
    return Database::getInstance();
}
?>