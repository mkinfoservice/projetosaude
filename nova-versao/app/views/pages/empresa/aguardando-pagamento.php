<?php
/**
 * views/pages/empresa/aguardando-pagamento.php
 * Exibe o QR Code / código PIX e aguarda confirmação de pagamento
 */

$pixTransaction = $pixData['pixTransaction'] ?? [];
$valor          = number_format($pixData['value'] ?? 97.00, 2, ',', '.');
$pixCode        = $pixTransaction['payload'] ?? null;
$qrCodeImg      = $pixTransaction['qrCode']  ?? null;
$isSimulado     = !empty($pixData['simulado']);
$erroAsaas      = $pixData['erro'] ?? null;
$expiracao      = $pixTransaction['expirationDate'] ?? null;
?>
<section class="auth-section">
<div class="container" style="max-width:600px; padding:3rem 1.5rem;">
<div class="auth-card">

    <!-- Header -->
    <div class="auth-header">
        <div class="auth-icon" id="status-icon">
            <svg viewBox="0 0 40 40" fill="none" width="44" height="44">
                <rect width="40" height="40" rx="10" fill="#00C48C"/>
                <path d="M20 10v10l6 4" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="20" cy="20" r="9" stroke="white" stroke-width="2"/>
            </svg>
        </div>
        <h1 class="auth-title" id="status-title">Aguardando Pagamento</h1>
        <p class="auth-subtitle">
            Olá, <strong><?= h($razaoSocial) ?></strong>! Realize o pagamento abaixo para ativar sua conta.
        </p>
    </div>

    <?php if ($erroAsaas): ?>
    <div class="alert alert-warning" style="margin-bottom:1.5rem;">
        <strong>Atenção:</strong> <?= h($erroAsaas) ?>
    </div>
    <?php endif; ?>

    <?php if ($isSimulado && !$erroAsaas): ?>
    <div class="alert alert-info" style="margin-bottom:1.5rem;">
        <strong>Modo demonstração:</strong> API Asaas não configurada. O PIX abaixo é simulado.
        Entre em contato via WhatsApp para ativar manualmente.
    </div>
    <?php endif; ?>

    <!-- Valor -->
    <div style="text-align:center;margin-bottom:1.5rem;">
        <p style="font-size:.9rem;color:var(--gray-500);margin-bottom:.25rem">Taxa de adesão</p>
        <p style="font-size:2.5rem;font-weight:700;color:var(--navy)">R$ <?= $valor ?></p>
        <?php if ($expiracao): ?>
        <p style="font-size:.85rem;color:var(--gray-400)">
            Válido até <?= date('d/m/Y H:i', strtotime($expiracao)) ?>
        </p>
        <?php endif; ?>
    </div>

    <?php if ($qrCodeImg): ?>
    <!-- QR Code -->
    <div style="text-align:center;margin-bottom:1.5rem;">
        <img src="data:image/png;base64,<?= h($qrCodeImg) ?>"
             alt="QR Code PIX" width="220" height="220"
             style="border:4px solid var(--green);border-radius:12px;padding:4px;">
    </div>
    <?php endif; ?>

    <?php if ($pixCode): ?>
    <!-- Código copia e cola -->
    <div style="margin-bottom:1.5rem;">
        <p style="font-size:.85rem;font-weight:600;color:var(--gray-600);margin-bottom:.5rem;">
            PIX Copia e Cola:
        </p>
        <div style="position:relative;">
            <textarea id="pix-code" rows="3" readonly
                      style="width:100%;font-size:.75rem;font-family:monospace;padding:.75rem;
                             border:1px solid var(--gray-200);border-radius:8px;resize:none;
                             background:var(--gray-50);word-break:break-all;"
            ><?= h($pixCode) ?></textarea>
            <button onclick="copiarPix()" class="btn btn-outline"
                    style="margin-top:.5rem;width:100%;" id="btn-copiar">
                Copiar Código PIX
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Status do pagamento -->
    <div id="payment-status" style="text-align:center;padding:1rem;
         background:var(--gray-50);border-radius:10px;margin-bottom:1.5rem;">
        <div id="status-spinner" style="display:inline-block;width:20px;height:20px;
             border:3px solid var(--gray-200);border-top-color:var(--green);
             border-radius:50%;animation:spin 1s linear infinite;margin-right:.5rem;vertical-align:middle;"></div>
        <span id="status-text" style="color:var(--gray-600);font-size:.9rem;">
            Aguardando confirmação do pagamento...
        </span>
    </div>

    <!-- Instruções -->
    <div class="alert alert-info" style="margin-bottom:1.5rem;font-size:.875rem;">
        <ol style="margin:.5rem 0 0 1.2rem;line-height:1.8;">
            <li>Abra o app do seu banco e escolha <strong>Pagar via PIX</strong>.</li>
            <li>Escaneie o QR Code ou use o código <em>Copia e Cola</em>.</li>
            <li>Após a confirmação, sua conta será ativada <strong>automaticamente</strong>.</li>
        </ol>
    </div>

    <p style="text-align:center;font-size:.85rem;color:var(--gray-500);">
        Dúvidas? Fale conosco:
        <a href="https://wa.me/5521968827864" target="_blank" style="color:var(--green);font-weight:600">
            WhatsApp
        </a>
    </p>

</div>
</div>
</section>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
(function() {
    let tentativas = 0;
    const maxTentativas = 120; // 10 minutos

    function checkStatus() {
        if (tentativas >= maxTentativas) {
            document.getElementById('status-text').textContent =
                'Tempo esgotado. Se você pagou, aguarde alguns minutos e acesse /login.';
            document.getElementById('status-spinner').style.display = 'none';
            return;
        }
        tentativas++;

        fetch('/api/empresa/payment-status', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data && data.data.status === 'ATIVO') {
                document.getElementById('status-spinner').style.display = 'none';
                document.getElementById('status-text').innerHTML =
                    '<strong style="color:var(--green)">✓ Pagamento confirmado! Redirecionando...</strong>';
                setTimeout(() => { window.location.href = data.data.redirect || '/login?ativado=1'; }, 1500);
            } else {
                setTimeout(checkStatus, 5000);
            }
        })
        .catch(() => { setTimeout(checkStatus, 10000); });
    }

    // Inicia polling após 5s
    setTimeout(checkStatus, 5000);
})();

function copiarPix() {
    const code = document.getElementById('pix-code');
    code.select();
    document.execCommand('copy');

    const btn = document.getElementById('btn-copiar');
    btn.textContent = '✓ Copiado!';
    btn.style.color = 'var(--green)';
    setTimeout(() => {
        btn.textContent = 'Copiar Código PIX';
        btn.style.color = '';
    }, 2000);
}
</script>
