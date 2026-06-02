<?php
/**
 * app/services/operators/TabelaLocalProvider.php
 * Provider de cotacao baseado nas tabelas locais do banco.
 */

declare(strict_types=1);

class TabelaLocalProvider
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function cotar(array $idades, ?string $categoria = null, ?string $operadoraSlug = null): array
    {
        $idades = $this->normalizarIdades($idades);
        if (empty($idades)) {
            return [];
        }

        $where = ['p.ativo = 1', 'o.ativo = 1'];
        $params = [];

        if ($categoria) {
            $where[] = 'p.categoria = ?';
            $params[] = $categoria;
        }

        if ($operadoraSlug) {
            $where[] = 'o.slug = ?';
            $params[] = $operadoraSlug;
        }

        $whereSql = implode(' AND ', $where);
        $stmt = $this->pdo->prepare("
            SELECT p.id AS plano_id, p.nome AS plano_nome, p.categoria, p.cobertura,
                   p.faixa_0_18, p.faixa_19_28, p.faixa_29_43, p.faixa_44_58, p.faixa_59_plus,
                   o.id AS operadora_id, o.nome AS operadora_nome, o.slug AS operadora_slug
            FROM planos p
            JOIN operadoras o ON p.operadora_id = o.id
            WHERE $whereSql
            ORDER BY o.nome, p.nome
        ");
        $stmt->execute($params);

        $resultados = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $plano) {
            $vidas = [];
            $total = 0.0;

            foreach ($idades as $idade) {
                $faixa = $this->faixaPorIdade($idade);
                $valor = (float)$plano[$faixa['coluna']];
                $total += $valor;
                $vidas[] = [
                    'idade' => $idade,
                    'faixa' => $faixa['label'],
                    'valor' => $valor,
                ];
            }

            $resultados[] = [
                'provider' => 'tabela_local',
                'operadora_id' => (int)$plano['operadora_id'],
                'operadora_nome' => $plano['operadora_nome'],
                'operadora_slug' => $plano['operadora_slug'],
                'plano_id' => (int)$plano['plano_id'],
                'plano_nome' => $plano['plano_nome'],
                'categoria' => $plano['categoria'],
                'categoria_label' => $plano['categoria'] === 'COM_COPARTICIPACAO' ? 'Com coparticipacao' : 'Sem coparticipacao',
                'cobertura' => $plano['cobertura'],
                'quantidade_vidas' => count($idades),
                'valor_total' => round($total, 2),
                'vidas' => $vidas,
            ];
        }

        usort($resultados, fn(array $a, array $b): int => $a['valor_total'] <=> $b['valor_total']);
        return $resultados;
    }

    private function normalizarIdades(array $idades): array
    {
        $validas = [];
        foreach ($idades as $idade) {
            $idade = filter_var($idade, FILTER_VALIDATE_INT);
            if ($idade !== false && $idade >= 0 && $idade <= 120) {
                $validas[] = $idade;
            }
        }

        return array_slice($validas, 0, 20);
    }

    private function faixaPorIdade(int $idade): array
    {
        return match (true) {
            $idade <= 18 => ['coluna' => 'faixa_0_18', 'label' => '0-18'],
            $idade <= 28 => ['coluna' => 'faixa_19_28', 'label' => '19-28'],
            $idade <= 43 => ['coluna' => 'faixa_29_43', 'label' => '29-43'],
            $idade <= 58 => ['coluna' => 'faixa_44_58', 'label' => '44-58'],
            default => ['coluna' => 'faixa_59_plus', 'label' => '59+'],
        };
    }
}
