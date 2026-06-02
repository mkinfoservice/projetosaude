<?php
/**
 * app/services/PriceTableImportService.php
 * Importacao CSV de tabelas de preco.
 */

declare(strict_types=1);

class PriceTableImportService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function importarCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (!$handle) {
            throw new RuntimeException('Nao foi possivel abrir o arquivo CSV.');
        }

        $header = fgetcsv($handle, 0, ';');
        if ($header === false || count($header) < 9) {
            fclose($handle);
            throw new RuntimeException('CSV invalido. Verifique o cabecalho.');
        }

        $header = array_map(fn(string $col): string => trim(strtolower($col)), $header);
        $required = [
            'operadora_slug', 'nome', 'categoria', 'cobertura',
            'faixa_0_18', 'faixa_19_28', 'faixa_29_43', 'faixa_44_58', 'faixa_59_plus',
        ];

        foreach ($required as $column) {
            if (!in_array($column, $header, true)) {
                fclose($handle);
                throw new RuntimeException("Coluna obrigatoria ausente: {$column}");
            }
        }

        $summary = [
            'criados' => 0,
            'atualizados' => 0,
            'ignorados' => 0,
            'erros' => [],
        ];

        $line = 1;
        $this->pdo->beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $line++;
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $data = array_combine($header, array_pad($row, count($header), ''));
                if ($data === false) {
                    $summary['ignorados']++;
                    $summary['erros'][] = "Linha {$line}: quantidade de colunas invalida.";
                    continue;
                }

                try {
                    $result = $this->upsertPlano($data);
                    $summary[$result]++;
                } catch (Throwable $e) {
                    $summary['ignorados']++;
                    $summary['erros'][] = "Linha {$line}: " . $e->getMessage();
                }
            }

            $this->pdo->commit();
            fclose($handle);
            return $summary;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            fclose($handle);
            throw $e;
        }
    }

    private function upsertPlano(array $data): string
    {
        $operadoraSlug = trim((string)($data['operadora_slug'] ?? ''));
        $nome = trim((string)($data['nome'] ?? ''));
        $categoria = trim((string)($data['categoria'] ?? ''));

        if ($operadoraSlug === '' || $nome === '') {
            throw new RuntimeException('operadora_slug e nome sao obrigatorios.');
        }

        if (!in_array($categoria, ['SEM_COPARTICIPACAO', 'COM_COPARTICIPACAO'], true)) {
            throw new RuntimeException('categoria deve ser SEM_COPARTICIPACAO ou COM_COPARTICIPACAO.');
        }

        $operadoraId = $this->findOperadoraId($operadoraSlug);
        if (!$operadoraId) {
            throw new RuntimeException("operadora_slug nao encontrado: {$operadoraSlug}");
        }

        $payload = [
            'operadora_id' => $operadoraId,
            'nome' => $nome,
            'categoria' => $categoria,
            'cobertura' => trim((string)($data['cobertura'] ?? '')),
            'faixa_0_18' => $this->money($data['faixa_0_18'] ?? '0'),
            'faixa_19_28' => $this->money($data['faixa_19_28'] ?? '0'),
            'faixa_29_43' => $this->money($data['faixa_29_43'] ?? '0'),
            'faixa_44_58' => $this->money($data['faixa_44_58'] ?? '0'),
            'faixa_59_plus' => $this->money($data['faixa_59_plus'] ?? '0'),
            'vigencia_inicio' => $this->dateOrNull($data['vigencia_inicio'] ?? null),
            'vigencia_fim' => $this->dateOrNull($data['vigencia_fim'] ?? null),
            'ativo' => $this->boolInt($data['ativo'] ?? '1'),
        ];

        $stmt = $this->pdo->prepare(
            "SELECT id, versao FROM planos
             WHERE operadora_id = ? AND nome = ? AND categoria = ?
             LIMIT 1"
        );
        $stmt->execute([$operadoraId, $nome, $categoria]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $payload['versao'] = ((int)$existing['versao']) + 1;
            $this->updatePlano((int)$existing['id'], $payload);
            return 'atualizados';
        }

        $payload['versao'] = 1;
        $this->insertPlano($payload);
        return 'criados';
    }

    private function findOperadoraId(string $slug): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM operadoras WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    private function updatePlano(int $id, array $payload): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE planos SET
                cobertura = ?, faixa_0_18 = ?, faixa_19_28 = ?, faixa_29_43 = ?,
                faixa_44_58 = ?, faixa_59_plus = ?, vigencia_inicio = ?, vigencia_fim = ?,
                ativo = ?, versao = ?
             WHERE id = ?"
        );
        $stmt->execute([
            $payload['cobertura'], $payload['faixa_0_18'], $payload['faixa_19_28'], $payload['faixa_29_43'],
            $payload['faixa_44_58'], $payload['faixa_59_plus'], $payload['vigencia_inicio'], $payload['vigencia_fim'],
            $payload['ativo'], $payload['versao'], $id,
        ]);
    }

    private function insertPlano(array $payload): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO planos (
                operadora_id, nome, categoria, cobertura, faixa_0_18, faixa_19_28,
                faixa_29_43, faixa_44_58, faixa_59_plus, vigencia_inicio,
                vigencia_fim, ativo, versao
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $payload['operadora_id'], $payload['nome'], $payload['categoria'], $payload['cobertura'],
            $payload['faixa_0_18'], $payload['faixa_19_28'], $payload['faixa_29_43'], $payload['faixa_44_58'],
            $payload['faixa_59_plus'], $payload['vigencia_inicio'], $payload['vigencia_fim'],
            $payload['ativo'], $payload['versao'],
        ]);
    }

    private function money(mixed $value): float
    {
        $value = str_replace(['R$', ' ', '.'], '', (string)$value);
        $value = str_replace(',', '.', $value);
        return round(max(0, (float)$value), 2);
    }

    private function dateOrNull(mixed $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') return null;

        $date = DateTime::createFromFormat('Y-m-d', $value)
            ?: DateTime::createFromFormat('d/m/Y', $value);

        return $date ? $date->format('Y-m-d') : null;
    }

    private function boolInt(mixed $value): int
    {
        $value = strtolower(trim((string)$value));
        return in_array($value, ['1', 'sim', 's', 'true', 'ativo'], true) ? 1 : 0;
    }

    private function isEmptyRow(array $row): bool
    {
        return implode('', array_map('trim', $row)) === '';
    }
}
