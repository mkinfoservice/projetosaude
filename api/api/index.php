<?php
// api/index.php
// Router principal da API - Cardsaude Bank

require_once 'config.php';
require_once 'utils.php';

// Obter método HTTP e endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? $_GET['q'] ?? '', '/'));
$endpoint = array_shift($request);

// Rotas da API
$routes = [
    'users' => 'users.php',
    'clients' => 'clients.php',
    'proposals' => 'proposals.php',
    'logs' => 'logs.php',
    'expenses' => 'expenses.php',
    'attendances' => 'attendances.php',
    'repasse' => 'repasseRequests.php',
    'commissions' => 'commissions.php',
    'invoices' => 'invoices.php',
    'notifications' => 'notifications.php',
    'clinics' => 'clinics.php',
    'accreditation' => 'accreditationProposals.php',
    'payments' => 'payments.php',
    'clinic-invoices' => 'clinicInvoices.php',
    'procedures' => 'procedures.php',
    'auth' => 'auth.php',
    'seed' => 'seed.php'
];

if (array_key_exists($endpoint, $routes)) {
    require_once $routes[$endpoint];
    exit;
}

// Endpoint não encontrado
jsonResponse(null, 404, 'Endpoint não encontrado: ' . $endpoint);
?>