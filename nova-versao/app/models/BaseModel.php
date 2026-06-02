<?php
/**
 * app/models/BaseModel.php
 * Modelo base com operações CRUD seguras via PDO
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

abstract class BaseModel
{
    protected PDO    $pdo;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }

    /**
     * Busca um registro por ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Busca todos (com filtros opcionais)
     */
    public function findAll(array $where = [], string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $sql    = "SELECT * FROM `{$this->table}`";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $col => $val) {
                $conditions[] = "`$col` = ?";
                $params[]     = $val;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        if ($orderBy) $sql .= " ORDER BY $orderBy";
        if ($limit)   $sql .= " LIMIT $limit";
        if ($offset)  $sql .= " OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Conta registros com filtros opcionais
     */
    public function count(array $where = []): int
    {
        $sql    = "SELECT COUNT(*) FROM `{$this->table}`";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $col => $val) {
                $conditions[] = "`$col` = ?";
                $params[]     = $val;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        return (int) $this->pdo->prepare($sql)->execute($params)
            ? $this->pdo->query("SELECT FOUND_ROWS()")->fetchColumn()
            : 0;
    }

    /**
     * Insere um registro
     *
     * @return int ID do registro inserido
     */
    public function insert(array $data): int
    {
        $cols   = array_keys($data);
        $places = array_fill(0, count($cols), '?');

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $this->table,
            '`' . implode('`, `', $cols) . '`',
            implode(', ', $places)
        );

        $this->pdo->prepare($sql)->execute(array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Atualiza um registro por ID
     *
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data)) return false;

        $sets   = [];
        $params = [];

        foreach ($data as $col => $val) {
            $sets[]   = "`$col` = ?";
            $params[] = $val;
        }

        $params[] = $id;

        $sql  = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = ?",
            $this->table,
            implode(', ', $sets),
            $this->primaryKey
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Deleta logicamente (define status = CANCELADO) ou fisicamente
     */
    public function softDelete(int $id): bool
    {
        return $this->update($id, ['status' => 'CANCELADO', 'atualizado_em' => date('Y-m-d H:i:s')]);
    }

    public function hardDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?"
        );
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Verifica se existe um registro com determinado valor em coluna
     */
    public function exists(string $column, mixed $value, ?int $excludeId = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM `{$this->table}` WHERE `$column` = ?";
        $params = [$value];

        if ($excludeId !== null) {
            $sql    .= " AND `{$this->primaryKey}` != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Executa query customizada com segurança
     */
    protected function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Paginação helper
     *
     * @return array ['data' => [], 'total' => int, 'pages' => int, 'current' => int]
     */
    public function paginate(array $where = [], int $page = 1, int $perPage = 20, string $orderBy = ''): array
    {
        $page    = max(1, $page);
        $offset  = ($page - 1) * $perPage;

        $data    = $this->findAll($where, $orderBy, $perPage, $offset);
        $total   = $this->countWhere($where);
        $pages   = (int) ceil($total / $perPage);

        return [
            'data'    => $data,
            'total'   => $total,
            'pages'   => $pages,
            'current' => $page,
            'per_page'=> $perPage,
        ];
    }

    private function countWhere(array $where): int
    {
        $sql    = "SELECT COUNT(*) FROM `{$this->table}`";
        $params = [];

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $col => $val) {
                $conditions[] = "`$col` = ?";
                $params[]     = $val;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
