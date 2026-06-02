/**
 * assets/js/pix-handler.js
 * Handler específico para pagamentos PIX com Asaas
 */

const PixHandler = {
    pollingInterval: null,
    maxAttempts: 20, // 5 minutos (15s x 20)
    currentAttempts: 0,
    
    /**
     * Exibir modal de pagamento PIX
     */
    showPaymentModal(qrCode, copiaCola, paymentId, amount, entity) {
        const modalId = entity === 'empresa' ? 'modal-pix-empresa' : 'modal-pix-vendedor';
        const modal = document.getElementById(modalId);
        
        if (!modal) {
            console.error(`Modal ${modalId} não encontrado`);
            return;
        }
        
        // Atualizar conteúdo
        modal.querySelector('.pix-amount').textContent = Utils.formatCurrency(amount);
        modal.querySelector('#pix-qr-image').src = qrCode;
        modal.querySelector('#pix-copia-cola').value = copiaCola;
        
        // Botão copiar
        const copyBtn = modal.querySelector('.btn-copy-pix');
        copyBtn?.removeEventListener('click', this.copyToClipboard);
        copyBtn?.addEventListener('click', () => this.copyToClipboard(copiaCola));
        
        // Botão confirmar
        const confirmBtn = modal.querySelector('.btn-confirm-payment');
        confirmBtn?.removeEventListener('click', this.manualConfirm);
        confirmBtn?.addEventListener('click', () => this.manualConfirm(paymentId, entity));
        
        // Mostrar modal
        ModalManager.show(modalId);
        
        // Iniciar polling automático
        this.startPolling(paymentId, () => {
            this.onPaymentConfirmed(entity);
        });
    },
    
    /**
     * Copiar código PIX para área de transferência
     */
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            Utils.showAlert('✅ Código PIX copiado!', 'success', 3000);
            
            // Feedback visual no botão
            const btn = document.querySelector('.btn-copy-pix');
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '✅ Copiado!';
                setTimeout(() => btn.innerHTML = originalText, 2000);
            }
        } catch (err) {
            // Fallback para navegadores antigos
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            Utils.showAlert('✅ Código copiado!', 'success', 3000);
        }
    },
    
    /**
     * Confirmação manual de pagamento (fallback)
     */
    async manualConfirm(paymentId, entity) {
        if (!confirm('Você já realizou o pagamento via PIX?\n\nClique em OK apenas se o pagamento já foi efetuado.')) {
            return;
        }
        
        Utils.showLoading('Verificando pagamento...');
        
        try {
            const result = await Utils.apiRequest('', {
                body: JSON.stringify({
                    action: 'confirm_payment',
                    payment_id: paymentId,
                    entity_type: entity === 'empresa' ? 'EMPRESA' : 'VENDEDOR'
                })
            });
            
            if (result.success) {
                Utils.showAlert('✅ Pagamento confirmado! Ativando sua conta...', 'success');
                this.onPaymentConfirmed(entity);
            } else {
                Utils.showAlert('⏳ Pagamento ainda não identificado. Aguarde alguns minutos ou entre em contato.', 'warning', 8000);
            }
        } catch (err) {
            Utils.showAlert('Erro ao verificar pagamento. Tente novamente.', 'error');
        } finally {
            Utils.hideLoading();
        }
    },
    
    /**
     * Polling automático para verificar status do pagamento
     */
    startPolling(paymentId, callback) {
        // Limpar polling anterior se existir
        this.stopPolling();
        
        this.currentAttempts = 0;
        
        const check = async () => {
            if (this.currentAttempts >= this.maxAttempts) {
                this.stopPolling();
                Utils.showAlert('⏳ Pagamento não confirmado automaticamente. Use "Confirmar Pagamento" após realizar o PIX.', 'warning', 10000);
                return;
            }
            
            this.currentAttempts++;
            
            try {
                const result = await Utils.apiRequest('', {
                    body: JSON.stringify({
                        action: 'check_payment_status',
                        payment_id: paymentId
                    })
                });
                
                if (result.success && result.status === 'CONFIRMED') {
                    this.stopPolling();
                    callback?.();
                }
            } catch (err) {
                console.warn('Erro ao verificar status:', err);
            }
        };
        
        // Verificar imediatamente e depois a cada 15 segundos
        check();
        this.pollingInterval = setInterval(check, 15000);
    },
    
    /**
     * Parar polling
     */
    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    },
    
    /**
     * Callback quando pagamento é confirmado
     */
    onPaymentConfirmed(entity) {
        this.stopPolling();
        ModalManager.hide(entity === 'empresa' ? 'modal-pix-empresa' : 'modal-pix-vendedor');
        
        // Redirecionar conforme entidade
        const redirect = entity === 'empresa' ? '/admin/empresa.php' : '/admin/vendedor.php';
        
        // Mostrar mensagem de sucesso
        Utils.showAlert('🎉 Conta ativada com sucesso! Bem-vindo à Concessionária Bem.', 'success', 5000);
        
        // Redirecionar após delay
        setTimeout(() => {
            window.location.href = redirect;
        }, 3000);
    },
    
    /**
     * Gerar fallback QR Code (caso API Asaas falhe)
     */
    generateFallbackQR(amount, reference) {
        const qrData = `PIX_CONCESSIONARIA_BEM_${reference}_R${amount.toFixed(2).replace('.', '')}`;
        return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(qrData)}`;
    },
    
    /**
     * Abrir app do banco com código PIX (mobile)
     */
    openBankApp(copiaCola) {
        // Tentar deep links de bancos populares
        const banks = [
            'nubank://pix/copia-cola/',
            'picpay://pix/',
            'itau://pix/',
            'bb://pix/',
            'santander://pix/'
        ];
        
        let opened = false;
        
        for (const deepLink of banks) {
            try {
                const testLink = deepLink + encodeURIComponent(copiaCola.substring(0, 50));
                const popup = window.open(testLink, '_blank');
                if (popup) {
                    opened = true;
                    break;
                }
            } catch (e) {
                continue;
            }
        }
        
        if (!opened) {
            // Fallback: copiar e instruir usuário
            this.copyToClipboard(copiaCola);
            Utils.showAlert('📋 Código copiado! Cole no app do seu banco.', 'info', 6000);
        }
    }
};

// ===== Event Listeners Globais para PIX =====
document.addEventListener('DOMContentLoaded', () => {
    // Botão de abrir app do banco
    document.querySelectorAll('.btn-open-bank-app').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const copiaCola = document.getElementById('pix-copia-cola')?.value;
            if (copiaCola) {
                PixHandler.openBankApp(copiaCola);
            }
        });
    });
    
    // Fechar modal e parar polling
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', () => {
            if (modal.id.includes('pix')) {
                PixHandler.stopPolling();
            }
        });
    });
});

// Exportar para uso global (se necessário)
window.PixHandler = PixHandler;