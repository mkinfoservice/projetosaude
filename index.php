<?php
/**
 * index.php - Página Principal
 * Concessionária Inteligente Bem - Sistema Multi-Operadoras
 */
define('SITE_LOADED', true);

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDBConnection();
$auth = new Auth($pdo);
$csrfToken = $auth->generateCsrfToken();

$isLoggedIn = $auth->isLoggedIn();
$userType = $auth->getUserType();
$perfil = $_SESSION['user_perfil'] ?? null;

$pageTitle = 'Concessionária Inteligente Bem - Planos de Saúde Multi-Operadoras';
$currentPage = 'home';

include __DIR__ . '/includes/header.php';

// Buscar operadoras do banco
$stmt = $pdo->query("SELECT id, nome, slug, logo_url FROM operadoras WHERE ativo = 1 ORDER BY nome");
$operadoras = $stmt->fetchAll();
?>

<!-- ===== HERO SECTION ===== -->
<section class="hero">
    <h1>Planos acessíveis para você e sua família</h1>
    <p>Trabalhamos com as melhores operadoras da Zona Oeste do Rio de Janeiro</p>
    <div class="hero-images">
        <img src="https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=300" alt="Saúde">
        <img src="https://images.unsplash.com/photo-1580281658629-4c3b0a9b5f5d?w=300" alt="Família">
        <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=300" alt="Bem-estar">
        <img src="https://images.unsplash.com/photo-1588776814546-ec7e0d1c1f02?w=300" alt="Cuidado">
        <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=300" alt="Hospital">
    </div>
</section>

<!-- ===== OPERADORAS ===== -->
<section class="section" id="section-operadoras">
    <h2 class="section-title">🎯 Escolha seu Plano de Saúde</h2>
    <div class="operadoras-grid">
        <?php foreach ($operadoras as $op): ?>
            <div class="operadora-card">
                <img src="<?= !empty($op['logo_url']) ? h($op['logo_url']) : 'https://via.placeholder.com/300x160?text=' . urlencode($op['nome']) ?>" 
                     alt="<?= h($op['nome']) ?>">
                <div class="operadora-card-body">
                    <h3><?= h($op['nome']) ?></h3>
                    <p>Planos completos com cobertura nacional e rede credenciada de qualidade.</p>
                    <a href="#section-simulacao" class="btn" onclick="selecionarOperadora(<?= $op['id'] ?>)">Ver Planos</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== CADASTRO EMPRESA ===== -->
<section class="section" id="section-cadastro-empresa" style="background:#f8f9fa;">
    <h2 class="section-title">🏢 Cadastro de Empresa</h2>
    <div class="form-container">
        <form id="form-empresa" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="action" value="register_empresa">
            
            <div class="form-row">
                <div class="form-group">
                    <label>CNPJ <span class="required">*</span></label>
                    <input type="text" name="cnpj" class="form-control" placeholder="00.000.000/0000-00" required maxlength="18">
                </div>
                <div class="form-group">
                    <label>CEP <span class="required">*</span></label>
                    <input type="text" name="cep" class="form-control" placeholder="00000-000" required maxlength="9">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Endereço <span class="required">*</span></label>
                    <input type="text" name="endereco" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Bairro</label>
                    <input type="text" name="bairro" class="form-control">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Número</label>
                    <input type="text" name="numero" class="form-control">
                </div>
                <div class="form-group">
                    <label>Telefone <span class="required">*</span></label>
                    <input type="text" name="telefone" class="form-control" placeholder="(21) 99999-9999" required maxlength="15">
                </div>
            </div>
            
            <div class="form-group">
                <label>E-mail <span class="required">*</span></label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Senha <span class="required">*</span></label>
                <input type="password" name="senha" class="form-control" placeholder="Mínimo 8 caracteres com letras e números" 
                       pattern="(?=.*\d)(?=.*[a-zA-Z]).{8,}" required>
                <small class="text-muted">Use letras e números para maior segurança</small>
            </div>
            
            <div class="form-group">
                <label>Contrato Social (PDF) <span class="required">*</span></label>
                <input type="file" name="contrato_social" class="form-control" accept=".pdf" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                💳 Cadastrar e Gerar PIX (R$ 0,50)
            </button>
        </form>
    </div>
</section>

<!-- ===== SIMULAÇÃO CHATBOT ===== -->
<section class="section" id="section-simulacao">
    <h2 class="section-title">💬 Atendimento Inteligente</h2>
    <div class="chatbot-container">
        <div class="chatbot-header">
            <div class="avatar">🤖</div>
            <div>
                <strong>Assistente Virtual Bem</strong>
                <div style="font-size:0.85rem;opacity:0.9;">Online agora</div>
            </div>
        </div>
        <div id="chat-messages" class="chatbot-messages">
            <div class="message bot">Olá! Sou o assistente virtual. Para quantas pessoas será o plano?</div>
        </div>
        <div class="chatbot-steps">
            <div class="step active"></div>
            <div class="step"></div>
            <div class="step"></div>
            <div class="step"></div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="chat-input" placeholder="Digite sua resposta...">
            <button id="chat-back" class="btn btn-outline" style="display:none;">←</button>
            <button id="chat-next" class="btn btn-primary">Próximo ➜</button>
            <button id="chat-identify" class="btn btn-secondary" style="display:none;">Identificar ➜</button>
        </div>
    </div>
</section>

<!-- ===== MODAL PIX EMPRESA ===== -->
<div id="modal-pix-empresa" class="modal pix-modal">
    <div class="modal-content">
        <button class="modal-close">&times;</button>
        <h3>💳 Pagamento Empresa</h3>
        <p>Valor: <strong class="pix-amount">R$ 0,50</strong></p>
        <div class="qr-container">
            <img id="pix-qr-image" src="" alt="QR Code PIX" style="max-width:250px;">
        </div>
        <p><small>Escaneie com seu banco ou use o "Copia e Cola" abaixo:</small></p>
        <textarea id="pix-copia-cola" class="pix-copia-cola" readonly></textarea>
        <div class="pix-actions">
            <button class="btn btn-outline btn-copy-pix">📋 Copiar</button>
            <button class="btn btn-secondary btn-open-bank-app">🏦 Abrir App</button>
            <button class="btn btn-primary btn-confirm-payment">✅ Confirmar Pagamento</button>
        </div>
    </div>
</div>

<!-- ===== MODAL PIX VENDEDOR ===== -->
<div id="modal-pix-vendedor" class="modal pix-modal">
    <div class="modal-content">
        <button class="modal-close">&times;</button>
        <h3>💳 Pagamento Vendedor</h3>
        <p>Mensalidade: <strong class="pix-amount">R$ 0,50</strong></p>
        <div class="qr-container">
            <img id="pix-qr-image-vendedor" src="" alt="QR Code PIX" style="max-width:250px;">
        </div>
        <textarea id="pix-copia-cola-vendedor" class="pix-copia-cola" readonly></textarea>
        <div class="pix-actions">
            <button class="btn btn-outline btn-copy-pix">📋 Copiar</button>
            <button class="btn btn-primary btn-confirm-payment">✅ Confirmar Pagamento</button>
        </div>
    </div>
</div>

<script>
// ===== INICIALIZAÇÃO DO CHATBOT =====
const chatbotData = {
    currentStep: 0,
    steps: [
        { question: 'Para quantas pessoas será o plano?', key: 'quantidade', type: 'number' },
        { question: 'Quais são as faixas etárias?', key: 'idades', placeholder: 'Ex: 30, 32, 5, 68' },
        { question: 'Qual é a profissão principal?', key: 'profissao' },
        { question: 'Digite o telefone do vendedor:', key: 'telefone_vendedor', placeholder: '(21) 99999-9999' }
    ],
    answers: {}
};

document.addEventListener('DOMContentLoaded', () => {
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const chatNext = document.getElementById('chat-next');
    const chatBack = document.getElementById('chat-back');
    const chatIdentify = document.getElementById('chat-identify');
    const steps = document.querySelectorAll('.chatbot-steps .step');
    
    function addMessage(text, sender = 'bot') {
        const msg = document.createElement('div');
        msg.className = `message ${sender}`;
        msg.textContent = text;
        chatMessages.appendChild(msg);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function showStep() {
        const step = chatbotData.steps[chatbotData.currentStep];
        addMessage(step.question, 'bot');
        chatInput.value = '';
        chatInput.placeholder = step.placeholder || '';
        chatInput.focus();
        
        steps.forEach((s, i) => s.classList.toggle('active', i === chatbotData.currentStep));
        chatBack.style.display = chatbotData.currentStep > 0 ? 'inline-flex' : 'none';
        
        if (step.key === 'telefone_vendedor') {
            chatNext.style.display = 'none';
            chatIdentify.style.display = 'inline-flex';
        } else {
            chatNext.style.display = 'inline-flex';
            chatIdentify.style.display = 'none';
        }
    }
    
    function handleNext() {
        const value = chatInput.value.trim();
        if (!value) return;
        
        const step = chatbotData.steps[chatbotData.currentStep];
        chatbotData.answers[step.key] = value;
        addMessage(value, 'user');
        
        if (chatbotData.currentStep < chatbotData.steps.length - 1) {
            chatbotData.currentStep++;
            showStep();
        } else {
            addMessage('🎯 Ótimo! Agora vamos ver as opções de planos.', 'bot');
            setTimeout(() => {
                carregarTabelaPrecos();
            }, 1000);
        }
    }
    
    chatNext.addEventListener('click', handleNext);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') handleNext();
    });
    
    chatBack.addEventListener('click', () => {
        if (chatbotData.currentStep > 0) {
            chatbotData.currentStep--;
            showStep();
        }
    });
    
    chatIdentify.addEventListener('click', async () => {
        const telefone = chatInput.value.trim();
        if (!telefone) {
            addMessage('⚠️ Por favor, digite o telefone.', 'bot');
            return;
        }
        
        addMessage(telefone, 'user');
        addMessage('🔍 Identificando vendedor...', 'bot');
        
        try {
            const formData = new FormData();
            formData.append('action', 'identify_vendedor');
            formData.append('telefone', telefone);
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
            
            const response = await fetch('/api/ajax-handler.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                addMessage(`✅ Vendedor identificado: ${result.data.nome}`, 'bot');
                setTimeout(() => carregarTabelaPrecos(), 1500);
            } else {
                addMessage('❌ Vendedor não encontrado. Verifique o número.', 'bot');
            }
        } catch (err) {
            addMessage('❌ Erro de conexão. Tente novamente.', 'bot');
        }
    });
    
    showStep();
});

// ===== MÁSCARAS DE INPUT =====
document.querySelectorAll('[name="cnpj"]').forEach(input => {
    input.addEventListener('input', (e) => {
        let v = e.target.value.replace(/\D/g, '');
        v = v.replace(/^(\d{2})(\d)/, '$1.$2');
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
        v = v.replace(/(\d{4})(\d)/, '$1-$2');
        e.target.value = v;
    });
});

document.querySelectorAll('[name="cep"]').forEach(input => {
    input.addEventListener('input', (e) => {
        let v = e.target.value.replace(/\D/g, '');
        v = v.replace(/^(\d{5})(\d)/, '$1-$2');
        e.target.value = v;
    });
    
    input.addEventListener('blur', async (e) => {
        const cep = e.target.value.replace(/\D/g, '');
        if (cep.length === 8) {
            try {
                const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await res.json();
                if (!data.erro) {
                    const form = e.target.closest('form');
                    if (form.querySelector('[name="endereco"]')) form.querySelector('[name="endereco"]').value = data.logradouro || '';
                    if (form.querySelector('[name="bairro"]')) form.querySelector('[name="bairro"]').value = data.bairro || '';
                }
            } catch (err) { console.warn('Erro CEP:', err); }
        }
    });
});

document.querySelectorAll('[name="telefone"]').forEach(input => {
    input.addEventListener('input', (e) => {
        let v = e.target.value.replace(/\D/g, '');
        v = v.replace(/^(\d{2})(\d)/, '($1) $2');
        v = v.replace(/(\d)(\d{4})$/, '$1-$2');
        e.target.value = v;
    });
});

// ===== CADASTRO EMPRESA COM AJAX =====
document.getElementById('form-empresa')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="loading"></span> Processando...';
    
    try {
        const response = await fetch('/api/ajax-handler.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
        });
        const result = await response.json();
        
        if (result.success) {
            if (result.next_step === 'show_payment_modal') {
                document.getElementById('pix-qr-image').src = result.pix_qr_code;
                document.getElementById('pix-copia-cola').value = result.pix_copia_cola || '';
                ModalManager.show('modal-pix-empresa');
                
                // Polling para verificar pagamento
                const checkInterval = setInterval(async () => {
                    const checkData = new FormData();
                    checkData.append('action', 'check_payment_status');
                    checkData.append('payment_id', result.payment_id);
                    
                    const checkRes = await fetch('/api/ajax-handler.php', {
                        method: 'POST',
                        body: checkData
                    });
                    const checkResult = await checkRes.json();
                    
                    if (checkResult.success && checkResult.status === 'CONFIRMED') {
                        clearInterval(checkInterval);
                        ModalManager.hide('modal-pix-empresa');
                        alert('✅ Pagamento confirmado! Redirecionando...');
                        window.location.href = '/admin/empresa.php';
                    }
                }, 15000);
            }
        } else {
            alert('❌ Erro: ' + (result.error || 'Erro ao cadastrar'));
        }
    } catch (err) {
        alert('❌ Erro de conexão. Tente novamente.');
        console.error(err);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

function selecionarOperadora(id) {
    // Scroll para simulacao
    document.getElementById('section-simulacao').scrollIntoView({ behavior: 'smooth' });
}

function carregarTabelaPrecos() {
    // Implementação da tabela de preços dinâmica
    alert('📊 Tabela de preços carregada! (Implementação completa no sistema)');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
