<?php
/**
 * app/models/PropostaModel.php
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

require_once APP_PATH . '/models/BaseModel.php';

class PropostaModel extends BaseModel
{
    protected string $table = 'propostas';

    public function gerarProtocolo(): string
    {
        do {
            $protocolo = 'PROP-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } while ($this->exists('protocolo', $protocolo));
        return $protocolo;
    }

    public function findByProtocolo(string $protocolo): ?array
    {
        $stmt = $this->query("SELECT * FROM propostas WHERE protocolo = ? LIMIT 1", [$protocolo]);
        return $stmt->fetch() ?: null;
    }

    /** Proposta com todos os dados relacionados (joins) */
    public function findCompleto(int $id): ?array
    {
        $stmt = $this->query("
            SELECT p.*,
                   c.nome_completo  AS cliente_nome,
                   c.cpf            AS cliente_cpf,
                   c.telefone       AS cliente_telefone,
                   c.email          AS cliente_email,
                   c.data_nascimento AS cliente_nascimento,
                   v.nome_completo  AS vendedor_nome,
                   v.perfil         AS vendedor_perfil,
                   e.razao_social   AS empresa_nome,
                   op.nome          AS operadora_nome,
                   pl.nome          AS plano_nome
            FROM propostas p
            JOIN clientes   c  ON p.cliente_id   = c.id
            JOIN vendedores v  ON p.vendedor_id  = v.id
            LEFT JOIN empresas   e  ON p.empresa_id   = e.id
            LEFT JOIN operadoras op ON p.operadora_id = op.id
            LEFT JOIN planos     pl ON p.plano_id     = pl.id
            WHERE p.id = ?
            LIMIT 1
        ", [$id]);
        return $stmt->fetch() ?: null;
    }

    /** Lista de propostas com joins — para qualquer filtro de status/vendedor/empresa */
    public function listarComFiltros(array $filtros = [], int $limit = 50): array
    {
        $where  = [];
        $params = [];

        if (!empty($filtros['vendedor_id'])) {
            $where[]  = 'p.vendedor_id = ?';
            $params[] = $filtros['vendedor_id'];
        }
        if (!empty($filtros['empresa_id'])) {
            $where[]  = 'p.empresa_id = ?';
            $params[] = $filtros['empresa_id'];
        }
        if (!empty($filtros['status'])) {
            if (is_array($filtros['status'])) {
                $places = implode(',', array_fill(0, count($filtros['status']), '?'));
                $where[]  = "p.status IN ($places)";
                $params   = array_merge($params, $filtros['status']);
            } else {
                $where[]  = 'p.status = ?';
                $params[] = $filtros['status'];
            }
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[] = $limit;

        return $this->query("
            SELECT p.id, p.protocolo, p.status, p.valor_total, p.quantidade_vidas,
                   p.criado_em, p.atualizado_em,
                   c.nome_completo AS cliente_nome,
                   v.nome_completo AS vendedor_nome,
                   e.razao_social  AS empresa_nome,
                   op.nome         AS operadora_nome,
                   pl.nome         AS plano_nome
            FROM propostas p
            JOIN clientes   c  ON p.cliente_id   = c.id
            JOIN vendedores v  ON p.vendedor_id  = v.id
            LEFT JOIN empresas   e  ON p.empresa_id   = e.id
            LEFT JOIN operadoras op ON p.operadora_id = op.id
            LEFT JOIN planos     pl ON p.plano_id     = pl.id
            $whereStr
            ORDER BY p.criado_em DESC
            LIMIT ?
        ", $params)->fetchAll();
    }

    public function avancarStatus(int $id, string $novoStatus, ?string $decisao = null, ?string $campo = null): bool
    {
        $data = ['status' => $novoStatus];
        if ($decisao !== null && $campo !== null) {
            $data[$campo] = $decisao;
        }
        return $this->update($id, $data);
    }

    public function contarPorStatus(string|array $status): int
    {
        if (is_array($status)) {
            if (empty($status)) return 0;

            $places = implode(',', array_fill(0, count($status), '?'));
            $stmt = $this->query("SELECT COUNT(*) FROM propostas WHERE status IN ($places)", $status);
            return (int) $stmt->fetchColumn();
        }

        $stmt = $this->query("SELECT COUNT(*) FROM propostas WHERE status = ?", [$status]);
        return (int) $stmt->fetchColumn();
    }

    public function valorTotalPorStatus(string|array $status): float
    {
        if (is_array($status)) {
            if (empty($status)) return 0.0;

            $places = implode(',', array_fill(0, count($status), '?'));
            $stmt = $this->query("SELECT COALESCE(SUM(valor_total), 0) FROM propostas WHERE status IN ($places)", $status);
            return (float) $stmt->fetchColumn();
        }

        $stmt = $this->query("SELECT COALESCE(SUM(valor_total), 0) FROM propostas WHERE status = ?", [$status]);
        return (float) $stmt->fetchColumn();
    }
}
