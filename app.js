/**
 * assets/js/app.js
 * Concessionária Inteligente Bem - Lógica Frontend Principal
 */

// ===== CONFIGURAÇÃO GLOBAL =====
const AppConfig = {
    apiUrl: '/api/ajax-handler.php',
    uploadUrl: '/api/ajax-handler.php?action=upload',
    siteUrl: window.location.origin,
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
    debug: false
};

// ===== UTILITÁRIOS =====
const Utils = {
    // Formatador de CPF/CNPJ
    formatDocument(value, type = 'cpf') {
        value = value.replace(/\D/g, '');
        if (type === 'cpf') {
            return value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        }
        return value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    },
    
    // Formatador de CEP
    formatCEP(value) {
        value = value.replace(/\D/g, '');
        return value.replace(/(\d{5})(\d{3})/, '$1-$2');
    },
    
    // Formatador de Telefone
    formatPhone(value) {
        value = value.replace(/\D/g, '');
        if (value.length <= 10) {
            return value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
        return value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    },
    
    // Formatador de Moeda
    formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    },
    
    // Validador de Email
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },
    
    // Show/Hide Loading
    showLoading(message = 'Carregando...') {
        let overlay = document.getElementById('loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `<div class="spinner"></div><p>${message}</p>`;
            document.body.appendChild(overlay);
        }
        overlay.querySelector('p').textContent = message;
        overlay.classList.add('active');
    },
    
    hideLoading() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) overlay.classList.remove('active');
    },
    
    // Alertas
    showAlert(message, type = 'info', duration = 5000) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `<strong>${type === 'error' ? 'Erro' : type === 'success' ? 'Sucesso' : 'Atenção'}:</strong> ${message}`;
        
        const container = document.querySelector('.alert-container') || document.body;
        container.insertBefore(alert, container.firstChild);
        
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(() => alert.remove(), 300);
        }, duration);
    },
    
    // Fetch com tratamento de erros
    async apiRequest(endpoint, options = {}) {
        const config = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': AppConfig.csrfToken,
                ...options.headers
            },
            ...options
        };
        
        try {
            const response = await fetch(AppConfig.apiUrl + endpoint, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Erro na requisição');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            Utils.showAlert(error.message, 'error');
            throw error;
        }
    }
};

// ===== MODALS =====
const ModalManager = {
    modals: {},
    
    init() {
        // Registrar todos os modais
        document.querySelectorAll('.modal').forEach(modal => {
            const id = modal.id;
            this.modals[id] = modal;
            
            // Fechar ao clicar no X
            modal.querySelector('.modal-close')?.addEventListener('click', () => this.hide(id));
            
            // Fechar ao clicar fora
            modal.addEventListener('click', (e) => {
                if (e.target === modal) this.hide(id);
            });
        });
        
        // Fechar com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                Object.keys(this.modals).forEach(id => this.hide(id));
            }
        });
    },
    
    show(modalId) {
        const modal = this.modals[modalId];
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            return true;
        }
        return false;
    },
    
    hide(modalId) {
        const modal = this.modals[modalId];
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            return true;
        }
        return false;
    },
    
    toggle(modalId) {
        const modal = this.modals[modalId];
        if (modal?.classList.contains('active')) {
            return this.hide(modalId);
        }
        return this.show(modalId);
    }
};

// ===== CHATBOT =====
const ChatBot = {
    currentStep: 0,
    steps: [
        { question: 'Para quantas pessoas será o plano?', type: 'number', key: 'quantidade' },
        { question: 'Quais são as faixas etárias?', type: 'text', key: 'idades', placeholder: 'Ex: 30, 32, 5, 68' },
        { question: 'Qual é a profissão principal?', type: 'text', key: 'profissao' },
        { question: 'Digite o telefone do vendedor:', type: 'tel', key: 'telefone_vendedor', placeholder: '(21) 99999-9999' }
    ],
    answers: {},
    
    init() {
        const input = document.getElementById('chat-input');
        const sendBtn = document.getElementById('chat-send');
        const backBtn = document.getElementById('chat-back');
        const nextBtn = document.getElementById('chat-next');
        const identifyBtn = document.getElementById('chat-identify');
        const messages = document.getElementById('chat-messages');
        
        if (!input) return;
        
        // Enviar mensagem
        const sendMessage = () => {
            const value = input.value.trim();
            if (!value) return;
            
            const step = this.steps[this.currentStep];
            this.answers[step.key] = value;
            
            // Adicionar mensagem do usuário
            this.addMessage(value, 'user');
            input.value = '';
            
            // Próximo passo ou finalizar
            if (this.currentStep < this.steps.length - 1) {
                this.currentStep++;
                this.showStep();
            } else {
                this.finish();
            }
        };
        
        sendBtn?.addEventListener('click', sendMessage);
        input?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
        
        // Voltar
        backBtn?.addEventListener('click', () => {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.showStep();
            }
        });
        
        // Próximo manual
        nextBtn?.addEventListener('click', () => {
            if (this.currentStep < this.steps.length - 1) {
                this.currentStep++;
                this.showStep();
            }
        });
        
        // Identificar vendedor
        identifyBtn?.addEventListener('click', async () => {
            const telefone = this.answers.telefone_vendedor;
            if (!telefone) {
                Utils.showAlert('Digite o telefone do vendedor', 'warning');
                return;
            }
            
            Utils.showLoading('Identificando vendedor...');
            try {
                const result = await Utils.apiRequest('', {
                    body: JSON.stringify({
                        action: 'identify_vendedor',
                        telefone: telefone.replace(/\D/g, '')
                    })
                });
                
                if (result.success) {
                    Utils.showAlert('Vendedor identificado!', 'success');
                    this.finish();
                }
            } catch (e) {
                Utils.showAlert('Vendedor não encontrado. Verifique o telefone.', 'error');
            } finally {
                Utils.hideLoading();
            }
        });
        
        // Iniciar
        this.showStep();
    },
    
    addMessage(text, sender = 'bot') {
        const messages = document.getElementById('chat-messages');
        if (!messages) return;
        
        const msg = document.createElement('div');
        msg.className = `message ${sender}`;
        msg.textContent = text;
        messages.appendChild(msg);
        messages.scrollTop = messages.scrollHeight;
    },
    
    showStep() {
        const step = this.steps[this.currentStep];
        const input = document.getElementById('chat-input');
        const inputContainer = document.getElementById('chat-input-container');
        
        if (!input || !inputContainer) return;
        
        // Atualizar indicador de passos
        document.querySelectorAll('.chatbot-steps .step').forEach((el, i) => {
            el.classList.toggle('active', i === this.currentStep);
        });
        
        // Mensagem do bot
        this.addMessage(step.question, 'bot');
        
        // Configurar input
        input.type = step.type || 'text';
        input.placeholder = step.placeholder || '';
        input.value = this.answers[step.key] || '';
        input.focus();
        
        // Mostrar/esconder botões
        document.getElementById('chat-back').style.display = this.currentStep > 0 ? 'inline-flex' : 'none';
        
        if (step.key === 'telefone_vendedor') {
            document.getElementById('chat-next').style.display = 'none';
            document.getElementById('chat-identify').style.display = 'inline-flex';
        } else {
            document.getElementById('chat-next').style.display = 'inline-flex';
            document.getElementById('chat-identify').style.display = 'none';
        }
    },
    
    finish() {
        this.addMessage('🎯 Ótimo! Agora vamos ver as opções de planos para você.', 'bot');
        
        // Mostrar tabela de preços
        setTimeout(() => {
            PriceTable.init(this.answers);
            ModalManager.show('modal-price-table');
        }, 1000);
    }
};

// ===== TABELA DE PREÇOS =====
const PriceTable = {
    userData: null,
    currentCategory: 'COM_COPARTICIPACAO',
    
    init(userData) {
        this.userData = userData;
        this.render();
        this.bindEvents();
    },
    
    render() {
        const container = document.getElementById('price-table-body');
        if (!container) return;
        
        const planos = this.getPlanos();
        container.innerHTML = planos.map(plano => `
            <tr>
                <td><strong>${plano.operadora}</strong><br><small>${plano.nome}</small></td>
                <td class="price">${Utils.formatCurrency(plano.faixa_0_18)}</td>
                <td class="price">${Utils.formatCurrency(plano.faixa_19_28)}</td>
                <td class="price">${Utils.formatCurrency(plano.faixa_29_43)}</td>
                <td class="price">${Utils.formatCurrency(plano.faixa_44_58)}</td>
                <td class="price">${Utils.formatCurrency(plano.faixa_59_plus)}</td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="PriceTable.selectPlano('${plano.id}')">
                        Escolher
                    </button>
                </td>
            </tr>
        `).join('');
    },
    
    getPlanos() {
        // Dados mockados - em produção viriam da API
        const planos = [
            { id: 'amil_completo_com', operadora: '🏥 AMIL', nome: 'Completo', categoria: 'COM_COPARTICIPACAO', faixa_0_18: 280, faixa_19_28: 320, faixa_29_43: 380, faixa_44_58: 450, faixa_59_plus: 580 },
            { id: 'unimed_completo_com', operadora: '⚕️ UNIMED', nome: 'Completo', categoria: 'COM_COPARTICIPACAO', faixa_0_18: 290, faixa_19_28: 330, faixa_29_43: 390, faixa_44_58: 460, faixa_59_plus: 590 },
            { id: 'klini_flex_com', operadora: '📈 KLINI', nome: 'Flex', categoria: 'COM_COPARTICIPACAO', faixa_0_18: 260, faixa_19_28: 300, faixa_29_43: 360, faixa_44_58: 430, faixa_59_plus: 550 },
            { id: 'cemeru_tradicional_com', operadora: '🛡️ CEMERU', nome: 'Tradicional', categoria: 'COM_COPARTICIPACAO', faixa_0_18: 270, faixa_19_28: 310, faixa_29_43: 370, faixa_44_58: 440, faixa_59_plus: 560 },
            { id: 'samoc_regional_com', operadora: '🌍 SAMOC', nome: 'Regional', categoria: 'COM_COPARTICIPACAO', faixa_0_18: 250, faixa_19_28: 290, faixa_29_43: 350, faixa_44_58: 420, faixa_59_plus: 540 },
            { id: 'oplan_flex_com', operadora: '💡 OPLAN', nome: 'Flex', categoria: 'COM_COPARTICIPACAO', faixa_0_18: 240, faixa_19_28: 280, faixa_29_43: 340, faixa_44_58: 410, faixa_59_plus: 530 },
            { id: 'amil_completo_sem', operadora: '🏥 AMIL', nome: 'Completo', categoria: 'SEM_COPARTICIPACAO', faixa_0_18: 220, faixa_19_28: 250, faixa_29_43: 300, faixa_44_58: 360, faixa_59_plus: 460 },
            { id: 'unimed_completo_sem', operadora: '⚕️ UNIMED', nome: 'Completo', categoria: 'SEM_COPARTICIPACAO', faixa_0_18: 230, faixa_19_28: 260, faixa_29_43: 310, faixa_44_58: 370, faixa_59_plus: 470 },
            { id: 'klini_flex_sem', operadora: '📈 KLINI', nome: 'Flex', categoria: 'SEM_COPARTICIPACAO', faixa_0_18: 200, faixa_19_28: 230, faixa_29_43: 280, faixa_44_58: 340, faixa_59_plus: 440 },
            { id: 'cemeru_tradicional_sem', operadora: '🛡️ CEMERU', nome: 'Tradicional', categoria: 'SEM_COPARTICIPACAO', faixa_0_18: 210, faixa_19_28: 240, faixa_29_43: 290, faixa_44_58: 350, faixa_59_plus: 450 },
            { id: 'samoc_regional_sem', operadora: '🌍 SAMOC', nome: 'Regional', categoria: 'SEM_COPARTICIPACAO', faixa_0_18: 190, faixa_19_28: 220, faixa_29_43: 270, faixa_44_58: 330, faixa_59_plus: 430 },
            { id: 'oplan_flex_sem', operadora: '💡 OPLAN', nome: 'Flex', categoria: 'SEM_COPARTICIPACAO', faixa_0_18: 180, faixa_19_28: 210, faixa_29_43: 260, faixa_44_58: 320, faixa_59_plus: 420 }
        ];
        
        return planos.filter(p => p.categoria === this.currentCategory);
    },
    
    bindEvents() {
        document.querySelectorAll('.category-toggle .btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.category-toggle .btn').forEach(b => b.classList.remove('active'));
                e.currentTarget.classList.add('active');
                this.currentCategory = e.currentTarget.dataset.category;
                this.render();
            });
        });
    },
    
    selectPlano(planoId) {
        ModalManager.hide('modal-price-table');
        
        // Preencher formulário de proposta
        const form = document.getElementById('form-proposta');
        if (form) {
            form.querySelector('[name="plano_id"]')?.setAttribute('value', planoId);
            
            // Preencher dados do chatbot se existirem
            if (this.userData) {
                form.querySelector('[name="titular_nome"]')?.focus();
            }
        }
        
        // Scroll para o formulário
        document.getElementById('section-cadastro')?.scrollIntoView({ behavior: 'smooth' });
    }
};

// ===== FORMULÁRIOS =====
const FormHandler = {
    init() {
        // Máscaras de input
        this.setupMasks();
        
        // Busca de CEP
        this.setupCEP();
        
        // Submissão de forms
        this.setupFormSubmissions();
        
        // Upload preview
        this.setupUploadPreview();
    },
    
    setupMasks() {
        // CPF/CNPJ
        document.querySelectorAll('[name="cpf"], [name="cnpj"]').forEach(input => {
            const type = input.name === 'cnpj' ? 'cnpj' : 'cpf';
            input.addEventListener('input', (e) => {
                e.target.value = Utils.formatDocument(e.target.value, type);
            });
        });
        
        // CEP
        document.querySelectorAll('[name="cep"]').forEach(input => {
            input.addEventListener('input', (e) => {
                e.target.value = Utils.formatCEP(e.target.value);
            });
        });
        
        // Telefone
        document.querySelectorAll('[name="telefone"]').forEach(input => {
            input.addEventListener('input', (e) => {
                e.target.value = Utils.formatPhone(e.target.value);
            });
        });
    },
    
    setupCEP() {
        document.querySelectorAll('[name="cep"]').forEach(input => {
            input.addEventListener('blur', async (e) => {
                const cep = e.target.value.replace(/\D/g, '');
                if (cep.length !== 8) return;
                
                try {
                    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    const data = await response.json();
                    
                    if (!data.erro) {
                        const form = e.target.closest('form');
                        const enderecoInput = form.querySelector('[name="endereco"]');
                        const bairroInput = form.querySelector('[name="bairro"]');
                        const cidadeUfInput = form.querySelector('[name="cidade_uf"]');
                        if (enderecoInput) enderecoInput.value = data.logradouro || '';
                        if (bairroInput) bairroInput.value = data.bairro || '';
                        if (cidadeUfInput) cidadeUfInput.value = `${data.localidade}-${data.uf}` || '';
                    }
                } catch (err) {
                    console.warn('Erro ao buscar CEP:', err);
                }
            });
        });
    },
    
    setupFormSubmissions() {
        // Cadastro de Empresa
        const formEmpresa = document.getElementById('form-empresa');
        formEmpresa?.addEventListener('submit', async (e) => {
            e.preventDefault();
            Utils.showLoading('Processando cadastro...');
            
            try {
                const formData = new FormData(formEmpresa);
                const response = await fetch(AppConfig.apiUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': AppConfig.csrfToken },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (result.next_step === 'show_payment_modal') {
                        // Mostrar modal PIX
                        document.getElementById('pix-qr-image').src = result.pix_qr_code;
                        document.getElementById('pix-copia-cola').value = result.pix_copia_cola;
                        ModalManager.show('modal-pix-empresa');
                        
                        // Iniciar polling de confirmação
                        PixHandler.startPolling(result.payment_id, () => {
                            Utils.showAlert('✅ Pagamento confirmado! Redirecionando...', 'success');
                            setTimeout(() => {
                                window.location.href = '/admin/empresa.php';
                            }, 2000);
                        });
                    }
                } else {
                    Utils.showAlert(result.error || 'Erro ao cadastrar', 'error');
                }
            } catch (err) {
                Utils.showAlert('Erro de conexão. Tente novamente.', 'error');
            } finally {
                Utils.hideLoading();
            }
        });
        
        // Cadastro de Vendedor
        const formVendedor = document.getElementById('form-vendedor');
        formVendedor?.addEventListener('submit', async (e) => {
            e.preventDefault();
            Utils.showLoading('Processando cadastro...');
            // Implementação similar ao form-empresa
            Utils.hideLoading();
        });
        
        // Login
        const formLogin = document.getElementById('form-login');
        formLogin?.addEventListener('submit', async (e) => {
            e.preventDefault();
            Utils.showLoading('Entrando...');
            
            try {
                const formData = new FormData(formLogin);
                formData.append('action', 'login');
                
                const response = await fetch(AppConfig.apiUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-Token': AppConfig.csrfToken }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Utils.showAlert('Login realizado com sucesso!', 'success');
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                } else {
                    Utils.showAlert(result.message || 'Credenciais inválidas', 'error');
                }
            } catch (err) {
                Utils.showAlert('Erro de conexão', 'error');
            } finally {
                Utils.hideLoading();
            }
        });
    },
    
    setupUploadPreview() {
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (!file) return;
                
                // Validar tamanho (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    Utils.showAlert('Arquivo muito grande. Máximo: 10MB', 'error');
                    e.target.value = '';
                    return;
                }
                
                // Preview para imagens
                if (file.type.startsWith('image/')) {
                    const preview = document.createElement('img');
                    preview.src = URL.createObjectURL(file);
                    preview.style.maxWidth = '200px';
                    preview.style.maxHeight = '150px';
                    preview.style.marginTop = '0.5rem';
                    preview.style.borderRadius = '8px';
                    
                    const existing = e.target.parentElement.querySelector('.upload-preview');
                    if (existing) existing.remove();
                    
                    preview.className = 'upload-preview';
                    e.target.parentElement.appendChild(preview);
                }
            });
        });
    }
};

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', () => {
    ModalManager.init();
    FormHandler.init();
    ChatBot.init();
    
    // Abrir modais via links
    document.querySelectorAll('[data-modal]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            ModalManager.show(link.dataset.modal);
        });
    });
    
    // Logout
    document.querySelectorAll('[data-action="logout"]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            if (confirm('Deseja realmente sair?')) {
                await Utils.apiRequest('', { body: JSON.stringify({ action: 'logout' }) });
                window.location.href = '/';
            }
        });
    });
    
    console.log('✅ Sistema Concessionária Bem carregado');
});
