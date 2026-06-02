<?php
/**
 * app/services/CotacaoService.php
 * Orquestra providers de cotacao e aplica fallback para tabela local.
 */

declare(strict_types=1);

require_once APP_PATH . '/services/operators/TabelaLocalProvider.php';
require_once APP_PATH . '/services/operators/AmilProvider.php';
require_once APP_PATH . '/services/operators/UnimedProvider.php';
require_once APP_PATH . '/services/operators/CemeruProvider.php';
require_once APP_PATH . '/services/operators/KliniSaudeProvider.php';
require_once APP_PATH . '/services/operators/SamocProvider.php';

class CotacaoService
{
    private TabelaLocalProvider $localProvider;

    public function __construct(private readonly PDO $pdo)
    {
        $this->localProvider = new TabelaLocalProvider($pdo);
    }

    public function cotar(array $idades, ?string $categoria = null, ?string $operadoraSlug = null): array
    {
        $resultadosApi = $this->cotarApisDisponiveis($idades, $categoria, $operadoraSlug);
        if (!empty($resultadosApi)) {
            return [
                'fonte' => 'api_operadora',
                'fallback_usado' => false,
                'resultados' => $resultadosApi,
            ];
        }

        return [
            'fonte' => 'tabela_local',
            'fallback_usado' => true,
            'resultados' => $this->localProvider->cotar($idades, $categoria, $operadoraSlug),
        ];
    }

    private function cotarApisDisponiveis(array $idades, ?string $categoria, ?string $operadoraSlug): array
    {
        $providers = [
            'amil'        => new AmilProvider(),
            'unimed'      => new UnimedProvider(),
            'cemeru'      => new CemeruProvider(),
            'klini-saude' => new KliniSaudeProvider(),
            'samoc'       => new SamocProvider(),
        ];

        $resultados = [];
        foreach ($providers as $slug => $provider) {
            if ($operadoraSlug && $operadoraSlug !== $slug) {
                continue;
            }

            $resultados = array_merge($resultados, $provider->cotar($idades, $categoria));
        }

        return $resultados;
    }
}
