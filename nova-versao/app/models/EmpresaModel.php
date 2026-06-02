<?php
/**
 * app/models/EmpresaModel.php
 * Model da tabela empresas
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

require_once APP_PATH . '/models/BaseModel.php';

class EmpresaModel extends BaseModel
{
    protected string $table = 'empresas';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->query("SELECT * FROM empresas WHERE email = ? LIMIT 1", [$email]);
        return $stmt->fetch() ?: null;
    }

    public function findByCnpj(string $cnpj): ?array
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        $stmt = $this->query("SELECT * FROM empresas WHERE cnpj = ? LIMIT 1", [$cnpj]);
        return $stmt->fetch() ?: null;
    }

    public function findByAsaasPaymentId(string $paymentId): ?array
    {
        $stmt = $this->query("SELECT * FROM empresas WHERE asaas_payment_id = ? LIMIT 1", [$paymentId]);
        return $stmt->fetch() ?: null;
    }

    public function ativar(int $id): bool
    {
        return $this->update($id, [
            'status'     => 'ATIVO',
            'pix_status' => 'CONFIRMADO',
        ]);
    }

    /**
     * Conta vendedores vinculados à empresa
     */
    public function totalVendedores(int $empresaId): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) FROM vendedores WHERE empresa_id = ? AND status = 'ATIVO'",
            [$empresaId]
        );
        return (int) $stmt->fetchColumn();
    }

    /**
     * Conta propostas da empresa
     */
    public function totalPropostas(int $empresaId): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) FROM propostas p
             JOIN vendedores v ON p.vendedor_id = v.id
             WHERE v.empresa_id = ?",
            [$empresaId]
        );
        return (int) $stmt->fetchColumn();
    }

    public function propostasRecentes(int $empresaId, int $limit = 50): array
    {
        return $this->query(
            "SELECT p.id, p.protocolo, p.status, p.valor_total, p.quantidade_vidas,
                    p.criado_em, p.atualizado_em,
                    c.nome_completo AS cliente_nome,
                    v.nome_completo AS vendedor_nome,
                    op.nome AS operadora_nome,
                    pl.nome AS plano_nome
             FROM propostas p
             JOIN vendedores v ON p.vendedor_id = v.id
             JOIN clientes c ON p.cliente_id = c.id
             LEFT JOIN operadoras op ON p.operadora_id = op.id
             LEFT JOIN planos pl ON p.plano_id = pl.id
             WHERE v.empresa_id = ?
             ORDER BY p.criado_em DESC
             LIMIT ?",
            [$empresaId, $limit]
        )->fetchAll();
    }

    public function pagamentosRecentes(int $empresaId, int $limit = 50): array
    {
        return $this->query(
            "SELECT id, entity_type, entity_id, valor, asaas_payment_id, status,
                    confirmado_em, criado_em, atualizado_em
             FROM pagamentos
             WHERE (entity_type = 'EMPRESA' AND entity_id = ?)
                OR (entity_type = 'VENDEDOR' AND entity_id IN (
                    SELECT id FROM vendedores WHERE empresa_id = ?
                ))
             ORDER BY criado_em DESC
             LIMIT ?",
            [$empresaId, $empresaId, $limit]
        )->fetchAll();
    }

    public function totalPagamentos(int $empresaId): int
    {
        $stmt = $this->query(
            "SELECT COUNT(*) FROM pagamentos
             WHERE (entity_type = 'EMPRESA' AND entity_id = ?)
                OR (entity_type = 'VENDEDOR' AND entity_id IN (
                    SELECT id FROM vendedores WHERE empresa_id = ?
                ))",
            [$empresaId, $empresaId]
        );
        return (int) $stmt->fetchColumn();
    }
}
