<?php
/**
 * public/index.php — Front Controller
 * Concessionária Inteligente Bem
 *
 * Todas as requisições HTTP passam por aqui.
 * Não há framework: roteamento simples, leve, compatível com Locaweb.
 */

declare(strict_types=1);

// Marca que foi carregado pelo front controller
define('APP_LOADED', true);

// Raiz da aplicação (pasta nova-versao/)
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');

// Carrega configurações
require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/config/app.php';

// ======================================
// ROTEAMENTO SIMPLES
// ======================================
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

// Remove prefixo de subpasta se houver (deploy em subdiretório)
// Ex: se o site está em /nova-versao/public, remove esse prefixo
$scriptDir = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($scriptDir && str_starts_with($uri, $scriptDir)) {
    $uri = trim(substr($uri, strlen($scriptDir)), '/');
}

// Tabela de rotas: URI => [controller, método]
$routes = [
    ''                               => ['HomeController',    'index'],
    'login'                          => ['AuthController',    'login'],
    'logout'                         => ['AuthController',    'logout'],

    // Empresa pública
    'empresa/cadastro'               => ['EmpresaController', 'cadastro'],
    'empresa/aguardando-pagamento'   => ['EmpresaController', 'aguardandoPagamento'],

    // Painéis (requerem login)
    'admin/empresa'                  => ['EmpresaController',  'dashboard'],
    'admin/empresa/vendedores'       => ['VendedorController',  'lista'],
    'admin/empresa/vendedores/novo'  => ['VendedorController',  'novo'],
    'admin/empresa/propostas'        => ['EmpresaController',  'propostas'],
    'admin/empresa/pagamentos'       => ['EmpresaController',  'pagamentos'],
    'admin/vendedor'                 => ['VendedorController',  'dashboard'],
    'admin/vendedor/propostas'       => ['PropostaController',  'lista'],
    'admin/vendedor/nova-proposta'   => ['PropostaController',  'nova'],
    'admin/vendedor/comissoes'       => ['VendedorController',  'comissoes'],
    'admin/supervisor'               => ['AdminController',     'supervisor'],
    'admin/gerente'                  => ['AdminController',     'gerente'],
    'admin/gerente/tabelas'          => ['AdminController',     'tabelas'],

    // API endpoints (retornam JSON)
    'api/operadoras'                 => ['ApiController',     'operadoras'],
    'api/planos'                     => ['ApiController',     'planos'],
    'api/cotacao'                    => ['ApiController',     'cotacao'],
    'api/cep'                        => ['ApiController',     'cep'],
    'api/auth'                       => ['ApiController',     'auth'],
    'api/empresa'                    => ['ApiController',     'empresa'],
    'api/empresa/payment-status'     => ['EmpresaController', 'checkPaymentStatus'],
    'api/vendedor'                   => ['ApiController',     'vendedor'],
    'api/proposta'                   => ['ApiController',     'proposta'],
    'api/payment'                    => ['ApiController',     'payment'],
    'api/csrf'                       => ['ApiController',     'csrf'],
    'api/webhook/asaas'              => ['EmpresaController', 'webhookAsaas'],
];

// Resolve rota
$route = $routes[$uri] ?? null;

// Se não encontrou rota, tenta 404 ou home
if ($route === null) {
    // Tenta match parcial para páginas dinâmicas futuras
    // Ex: /proposta/123 → PropostaController::show(123)
    $parts = explode('/', $uri, 3);

    if (count($parts) >= 2 && $parts[0] === 'proposta') {
        $route = ['PropostaController', 'show'];
        $_GET['id'] = $parts[1] ?? null;
    } else {
        // 404
        http_response_code(404);
        $route = ['HomeController', 'notFound'];
    }
}

[$controllerName, $method] = $route;

// Carrega controller
$controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(500);
    echo APP_DEBUG ? "Controller não encontrado: $controllerName" : "Erro interno.";
    exit;
}

require_once $controllerFile;

// Instancia e chama método
$controller = new $controllerName();
$controller->$method();
