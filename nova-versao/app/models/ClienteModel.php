<?php
/**
 * app/models/ClienteModel.php
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

require_once APP_PATH . '/models/BaseModel.php';

class ClienteModel extends BaseModel
{
    protected string $table = 'clientes';

    public function findByCpf(string $cpf): ?array
    {
        $cpf  = preg_replace('/\D/', '', $cpf);
        $stmt = $this->query("SELECT * FROM clientes WHERE cpf = ? ORDER BY id DESC LIMIT 1", [$cpf]);
        return $stmt->fetch() ?: null;
    }
}
