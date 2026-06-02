<?php
/**
 * app/models/VendedorModel.php
 * Model da tabela vendedores
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

require_once APP_PATH . '/models/BaseModel.php';

class VendedorModel extends BaseModel
{
    protected string $table = 'vendedores';

    public function findByTelefone(string $telefone): ?array
    {
        $telefone = preg_replace('/\D/', '', $telefone);
        $stmt = $this->query("SELECT * FROM vendedores WHERE telefone = ? LIMIT 1", [$telefone]);
        return $stmt->fetch() ?: null;
    }

    public function findByCpf(string $cpf): ?array
    {
        $cpf  = preg_replace('/\D/', '', $cpf);
        $stmt = $this->query("SELECT * FROM vendedores WHERE cpf = ? LIMIT 1", [$cpf]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmpresa(int $empresaId, string $status = ''): array
    {
        $sql    = "SELECT * FROM vendedores WHERE empresa_id = ?";
        $params = [$empresaId];

        if ($status) {
            $sql    .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY nome_completo";
        return $this->query($sql, $params)->fetchAll();
    }

    public function findByAsaasPaymentId(string $paymentId): ?array
    {
        $stmt = $this->query("SELECT * FROM vendedores WHERE asaas_payment_id = ? LIMIT 1", [$paymentId]);
        return $stmt->fetch() ?: null;
    }

    public function ativar(int $id): bool
    {
        return $this->update($id, [
            'status'     => 'ATIVO',
            'pix_status' => 'CONFIRMADO',
        ]);
    }

    public function totalPropostas(int $vendedorId): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) FROM propostas WHERE vendedor_id = ?",
            [$vendedorId]
        );
        return (int) $stmt->fetchColumn();
    }

    public function totalPropostasPorStatus(int $vendedorId, array $status): int
    {
        if (empty($status)) return 0;

        $places = implode(',', array_fill(0, count($status), '?'));
        $params = array_merge([$vendedorId], $status);

        $stmt = $this->query(
            "SELECT COUNT(*) FROM propostas
             WHERE vendedor_id = ? AND status IN ($places)",
            $params
        );
        return (int) $stmt->fetchColumn();
    }

    public function comissaoEstimada(int $vendedorId, float $percentual = 0.10): float
    {
        $stmt = $this->query(
            "SELECT COALESCE(SUM(valor_total), 0)
             FROM propostas
             WHERE vendedor_id = ?
               AND gera_comissao = 1
               AND status IN ('ENVIADO_OPERADORA', 'CONCLUIDO')",
            [$vendedorId]
        );

        return (float)$stmt->fetchColumn() * $percentual;
    }

    public function ultimasPropostas(int $vendedorId, int $limit = 10): array
    {
        return $this->query(
            "SELECT p.id, p.protocolo, p.status, p.valor_total, p.criado_em,
                    c.nome_completo AS cliente,
                    op.nome         AS operadora
             FROM propostas p
             JOIN clientes   c  ON p.cliente_id   = c.id
             LEFT JOIN operadoras op ON p.operadora_id = op.id
             WHERE p.vendedor_id = ?
             ORDER BY p.criado_em DESC
             LIMIT ?",
            [$vendedorId, $limit]
        )->fetchAll();
    }

    public function propostasComissionadas(int $vendedorId, float $percentual = 0.10): array
    {
        return $this->query(
            "SELECT p.id, p.protocolo, p.status, p.valor_total, p.quantidade_vidas,
                    p.atualizado_em,
                    c.nome_completo AS cliente_nome,
                    op.nome         AS operadora_nome,
                    pl.nome         AS plano_nome
             FROM propostas p
             JOIN clientes   c  ON p.cliente_id   = c.id
             LEFT JOIN operadoras op ON p.operadora_id = op.id
             LEFT JOIN planos     pl ON p.plano_id     = pl.id
             WHERE p.vendedor_id = ?
               AND p.gera_comissao = 1
               AND p.status IN ('ENVIADO_OPERADORA', 'CONCLUIDO')
             ORDER BY p.atualizado_em DESC",
            [$vendedorId]
        )->fetchAll();
    }
}
