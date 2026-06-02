<?php
/**
 * includes/functions.php
 * Funções utilitárias para frontend, formatação e UI
 * ⚠️ Incluir APÓS config.php e Auth.php
 */
if (!defined('SITE_LOADED')) {
    exit('Acesso direto não permitido.');
}

/**
 * Escapa string para HTML (atalho seguro)
 */
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formata moeda BRL
 */
function formatMoney($value, $showSymbol = true) {
    $val = number_format((float)$value, 2, ',', '.');
    return $showSymbol ? "R$ {$val}" : $val;
}

/**
 * Formata data para padrão BR
 */
function formatDateBR($date, $includeTime = false) {
    if (empty($date)) return '-';
    try {
        $dt = new DateTime($date);
        return $includeTime ? $dt->format('d/m/Y H:i') : $dt->format('d/m/Y');
    } catch (Exception $e) {
        return '-';
    }
}

/**
 * Gera badge HTML de status
 */
function statusBadge($status, $prefix = 'status-') {
    $map = [
        'PENDENTE_PAGAMENTO' => 'pendente',
        'ATIVO'              => 'ativo',
        'SUSPENSO'           => 'suspenso',
        'CANCELADO'          => 'suspenso',
        'APROVADA'           => 'aprovada',
        'ENVIADA'            => 'info',
        'EM_ANALISE'         => 'warning',
        'RECUSADA'           => 'danger',
        'RASCUNHO'           => 'draft'
    ];
    $class = $map[strtoupper($status)] ?? 'default';
    $label = ucwords(strtolower(str_replace('_', ' ', $status)));
    return "<span class='status-badge {$prefix}{$class}'>{$label}</span>";
}

/**
 * Calcula faixa etária baseada na data de nascimento
 */
function getIdadeFaixa($nascimento) {
    if (empty($nascimento)) return null;
    try {
        $dt = new DateTime($nascimento);
        $idade = $dt->diff(new DateTime())->y;
        if ($idade <= 18) return '0-18';
        if ($idade <= 28) return '19-28';
        if ($idade <= 43) return '29-43';
        if ($idade <= 58) return '44-58';
        return '59+';
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Renderiza paginação simples
 */
function renderPagination($currentPage, $totalPages, $baseUrl = '') {
    if ($totalPages <= 1) return '';
    $html = '<div class="pagination mt-3 text-center">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i === $currentPage) ? 'btn-primary' : 'btn-outline';
        $html .= "<a href='{$baseUrl}?page={$i}' class='btn {$active}' style='padding:0.35rem 0.7rem; margin:0 0.25rem;'>{$i}</a>";
    }
    $html .= '</div>';
    return $html;
}

/**
 * Retorna URL ativa para navegação
 */
function isActivePage($page, $current) {
    return ($page === $current) ? 'active' : '';
}

/**
 * Gera QR Code fallback (quando API Asaas indisponível)
 */
function fallbackQRCode($amount, $reference) {
    $data = "PIX_CONCESSIONARIA_BEM_" . urlencode($reference) . "_R" . number_format($amount, 2, '', '');
    return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($data);
}

/**
 * Valida formato de telefone (apenas números)
 */
function isPhoneValid($phone) {
    return preg_match('/^\d{10,11}$/', preg_replace('/\D/', '', $phone));
}
?>