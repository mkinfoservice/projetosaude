<?php
define('SITE_LOADED', true);
require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/../api/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Painel Vendedor';
$currentPage = 'vendedor';
include __DIR__ . '/../includes/header.php';
?>

<!-- SEU CONTEÚDO AQUI -->

<?php include __DIR__ . '/../includes/footer.php'; ?>
