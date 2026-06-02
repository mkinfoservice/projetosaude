/**
 * app.js — JavaScript Principal
 * Concessionária Inteligente Bem
 *
 * Vanilla JS modular, sem dependências externas.
 * Compatível com todos os navegadores modernos.
 */

'use strict';

/* =========================================
   NAMESPACE PRINCIPAL
   ========================================= */
const CIB = {

    /* --- CSRF Token --- */
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content ?? '',

    /* =========================================
       MODAIS
       ========================================= */
    openModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('active');
        el.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        // Foca primeiro elemento focável
        const focusable = el.querySelector('button, input, select, textarea, a[href]');
        focusable?.focus();
    },

    closeModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('active');
        el.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    },

    closeAllModals() {
        document.querySelectorAll('.modal.active').forEach(m => {
            m.classList.remove('active');
            m.setAttribute('aria-hidden', 'true');
        });
        document.body.style.overflow = '';
    },

    /* =========================================
       MENU MOBILE
       ========================================= */
    toggleMobileMenu() {
        const menu   = document.getElementById('nav-mobile');
        const toggle = document.querySelector('.nav-toggle');
        const isOpen = menu?.classList.contains('open');

        menu?.classList.toggle('open', !isOpen);
        toggle?.setAttribute('aria-expanded', String(!isOpen));
        if (menu) menu.setAttribute('aria-hidden', String(isOpen));
        document.body.classList.toggle('mobile-menu-open', !isOpen);
    },

    closeMobileMenu() {
        const menu   = document.getElementById('nav-mobile');
        const toggle = document.querySelector('.nav-toggle');

        menu?.classList.remove('open');
        menu?.setAttribute('aria-hidden', 'true');
        toggle?.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('mobile-menu-open');
    },

    /* =========================================
       NAVBAR — efeito scroll
       ========================================= */
    initNavbar() {
        const navbar = document.getElementById('navbar');
        if (!navbar) return;

        const onScroll = () => {
            navbar.classList.toggle('scrolled', window.scrollY > 20);
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    },

    /* =========================================
       CEP AUTO-PREENCHIMENTO
       ========================================= */
    async fillCEP(cep, prefix) {
        const clean = cep.replace(/\D/g, '');
        if (clean.length !== 8) return;

        try {
            const res  = await fetch(`https://viacep.com.br/ws/${clean}/json/`);
            const data = await res.json();
            if (data.erro) return;

            const set = (suffix, value) => {
                const el = document.querySelector(`[name="${prefix}-${suffix}"], #${prefix}-${suffix}`);
                if (el && value) el.value = value;
            };

            set('endereco', data.logradouro);
            set('bairro',   data.bairro);
            set('cidade',   `${data.localidade} / ${data.uf}`);
            set('uf',       data.uf);
        } catch (err) {
            console.warn('ViaCEP:', err);
        }
    },

    /* =========================================
       MÁSCARAS DE INPUT
       ========================================= */
    maskCNPJ(value) {
        let v = value.replace(/\D/g, '').slice(0, 14);
        v = v.replace(/^(\d{2})(\d)/, '$1.$2');
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
        v = v.replace(/(\d{4})(\d)/, '$1-$2');
        return v;
    },

    maskCPF(value) {
        let v = value.replace(/\D/g, '').slice(0, 11);
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
        return v;
    },

    maskPhone(value) {
        let v = value.replace(/\D/g, '').slice(0, 11);
        if (v.length <= 10) {
            v = v.replace(/^(\d{2})(\d)/, '($1) $2');
            v = v.replace(/(\d{4})(\d{4})$/, '$1-$2');
        } else {
            v = v.replace(/^(\d{2})(\d)/, '($1) $2');
            v = v.replace(/(\d{5})(\d{4})$/, '$1-$2');
        }
        return v;
    },

    maskCEP(value) {
        let v = value.replace(/\D/g, '').slice(0, 8);
        v = v.replace(/^(\d{5})(\d)/, '$1-$2');
        return v;
    },

    initMasks() {
        document.addEventListener('input', (e) => {
            const t = e.target;
            if (t.matches('[data-mask="cnpj"]'))  t.value = CIB.maskCNPJ(t.value);
            if (t.matches('[data-mask="cpf"]'))   t.value = CIB.maskCPF(t.value);
            if (t.matches('[data-mask="phone"]')) t.value = CIB.maskPhone(t.value);
            if (t.matches('[data-mask="cep"]'))   t.value = CIB.maskCEP(t.value);
        });

        document.addEventListener('blur', async (e) => {
            const t = e.target;
            if (t.matches('[data-mask="cep"]') && t.dataset.fillPrefix) {
                await CIB.fillCEP(t.value, t.dataset.fillPrefix);
            }
        }, true);
    },

    /* =========================================
       AJAX HELPER
       ========================================= */
    async post(action, data = {}, files = null) {
        const fd = new FormData();
        fd.append('action',     action);
        fd.append('csrf_token', CIB.csrfToken);
        Object.entries(data).forEach(([k, v]) => fd.append(k, v));
        if (files) {
            Object.entries(files).forEach(([k, v]) => fd.append(k, v));
        }

        const res = await fetch('/api/' + action.split('_')[0], {
            method:  'POST',
            body:    fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
    },

    /* =========================================
       TOAST NOTIFICATIONS
       ========================================= */
    toast(message, type = 'success', duration = 4000) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = `
                position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
                z-index: 9999; display: flex; flex-direction: column; gap: 8px;
                pointer-events: none; min-width: 280px; max-width: 420px;
            `;
            document.body.appendChild(container);
        }

        const colors = {
            success: { bg: '#00C48C', text: '#fff' },
            error:   { bg: '#E53E3E', text: '#fff' },
            warning: { bg: '#F5A623', text: '#fff' },
            info:    { bg: '#1565C0', text: '#fff' },
        };
        const c = colors[type] || colors.info;

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: ${c.bg}; color: ${c.text};
            padding: 0.875rem 1.25rem; border-radius: 12px;
            font-size: 0.9rem; font-weight: 500;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            pointer-events: auto; cursor: pointer;
            animation: toastIn 0.3s ease;
            font-family: var(--font-sans, system-ui, sans-serif);
        `;
        toast.textContent = message;
        toast.addEventListener('click', () => toast.remove());
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(8px)';
            toast.style.transition = '0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    /* =========================================
       COTAÇÃO — Chatbot
       ========================================= */
    quote: {
        step:    0,
        answers: {},
        steps: [
            { key: 'quantidade',        label: 'Para quantas pessoas?',        type: 'number',   placeholder: 'Ex: 3' },
            { key: 'idades',            label: 'Quais são as idades?',          type: 'text',     placeholder: 'Ex: 30, 45, 12' },
            { key: 'profissao',         label: 'Qual é a profissão principal?', type: 'text',     placeholder: 'Ex: Autônomo' },
            { key: 'telefone_vendedor', label: 'Telefone do vendedor:',        type: 'tel',      placeholder: '(21) 99999-9999', isLast: true },
        ],

        init() {
            this.step    = 0;
            this.answers = {};
            this.render();
        },

        render() {
            const step       = this.steps[this.step];
            const messages   = document.getElementById('chat-messages');
            const input      = document.getElementById('chat-input');
            const btnNext    = document.getElementById('chat-next');
            const btnBack    = document.getElementById('chat-back');
            const btnIdentify= document.getElementById('chat-identify');
            const stepsEl    = document.querySelectorAll('.chatbot-step');

            if (!messages || !input) return;

            const msg = document.createElement('div');
            msg.className = 'message bot';
            msg.textContent = step.label;
            messages.appendChild(msg);
            messages.scrollTop = messages.scrollHeight;

            input.type        = step.type;
            input.placeholder = step.placeholder;
            input.value       = '';

            stepsEl.forEach((el, i) => {
                el.className = 'chatbot-step' + (i < this.step ? ' done' : i === this.step ? ' active' : '');
            });

            if (btnBack) btnBack.style.display = this.step > 0 ? 'inline-flex' : 'none';

            if (step.isLast) {
                if (btnNext)     btnNext.style.display     = 'none';
                if (btnIdentify) btnIdentify.style.display = 'inline-flex';
            } else {
                if (btnNext)     btnNext.style.display     = 'inline-flex';
                if (btnIdentify) btnIdentify.style.display = 'none';
            }
        },

        next() {
            const input = document.getElementById('chat-input');
            const value = input?.value.trim();
            if (!value) { CIB.toast('Por favor, preencha o campo.', 'warning'); return; }

            const step = this.steps[this.step];
            this.answers[step.key] = value;

            const messages = document.getElementById('chat-messages');
            const userMsg  = document.createElement('div');
            userMsg.className = 'message user';
            userMsg.textContent = value;
            messages?.appendChild(userMsg);
            messages && (messages.scrollTop = messages.scrollHeight);

            if (this.step < this.steps.length - 1) {
                this.step++;
                setTimeout(() => this.render(), 300);
            }
        },

        back() {
            if (this.step > 0) {
                this.step--;
                this.render();
            }
        },

        async identify() {
            const input   = document.getElementById('chat-input');
            const phone   = input?.value.trim();
            if (!phone) { CIB.toast('Digite o telefone do vendedor.', 'warning'); return; }

            this.answers.telefone_vendedor = phone;

            const messages = document.getElementById('chat-messages');
            const userMsg  = document.createElement('div');
            userMsg.className = 'message user';
            userMsg.textContent = phone;
            messages?.appendChild(userMsg);

            const thinkMsg = document.createElement('div');
            thinkMsg.className = 'message bot';
            thinkMsg.textContent = '🔍 Identificando vendedor...';
            messages?.appendChild(thinkMsg);
            messages && (messages.scrollTop = messages.scrollHeight);

            try {
                const fd = new FormData();
                fd.append('action',     'identify_vendedor');
                fd.append('telefone',   phone.replace(/\D/g, ''));
                fd.append('csrf_token', CIB.csrfToken);

                const res    = await fetch('/api/vendedor', { method: 'POST', body: fd });
                const result = await res.json();

                thinkMsg.textContent = result.success
                    ? `✅ Vendedor: ${result.data?.nome ?? 'identificado'}. Vamos ver as opções!`
                    : '❌ Vendedor não encontrado. Proposta será atribuída ao Admin.';

                if (result.success) {
                    this.answers.vendedor_id   = result.data?.id;
                    this.answers.vendedor_nome = result.data?.nome;
                }

                setTimeout(() => this.fetchCotacao(), 600);
            } catch {
                thinkMsg.textContent = '❌ Erro de conexão. Tente novamente.';
            }
        },

        showPricetable() {
            CIB.openModal('modal-cotacao');
        },

        async fetchCotacao(override = {}) {
            const idades = override.idades || this.answers.idades || '';
            if (!idades) {
                CIB.toast('Informe as idades para calcular a cotacao.', 'warning');
                return;
            }

            const params = new URLSearchParams({
                idades,
                categoria: override.categoria || this.answers.categoria || 'sem',
                operadora: override.operadora || ''
            });

            const resultsEl = document.getElementById('quote-results');
            const introEl = document.getElementById('quote-modal-intro');
            CIB.openModal('modal-cotacao');

            if (introEl) introEl.classList.add('hidden');
            if (resultsEl) {
                resultsEl.classList.remove('hidden');
                resultsEl.innerHTML = '<div class="quote-loading">Calculando melhores opcoes...</div>';
            }

            try {
                const res = await fetch('/api/cotacao?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const payload = await res.json();

                if (!payload.success) {
                    throw new Error(payload.error || 'Nao foi possivel cotar.');
                }

                this.renderCotacao(payload.data);
            } catch (err) {
                if (resultsEl) {
                    resultsEl.innerHTML = `
                        <div class="quote-empty">
                            <strong>Nao foi possivel calcular agora.</strong>
                            <span>${err.message || 'Tente novamente em instantes.'}</span>
                        </div>
                    `;
                }
            }
        },

        renderCotacao(data) {
            const resultsEl = document.getElementById('quote-results');
            if (!resultsEl) return;

            const resultados = data.resultados || [];
            if (!resultados.length) {
                resultsEl.innerHTML = `
                    <div class="quote-empty">
                        <strong>Nenhum plano encontrado.</strong>
                        <span>Altere as idades, categoria ou operadora e tente novamente.</span>
                    </div>
                `;
                return;
            }

            const cards = resultados.slice(0, 8).map((item) => {
                const valor = Number(item.valor_total || 0).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
                const vidas = (item.vidas || []).map((vida) => `${vida.idade} anos: ${Number(vida.valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}`).join('<br>');

                return `
                    <article class="quote-result-card">
                        <div>
                            <span class="quote-provider">${item.operadora_nome}</span>
                            <h4>${item.plano_nome}</h4>
                            <p>${item.categoria_label} · ${item.cobertura || 'Cobertura sob consulta'}</p>
                        </div>
                        <div class="quote-price">${valor}</div>
                        <details>
                            <summary>Ver faixas calculadas</summary>
                            <div>${vidas}</div>
                        </details>
                    </article>
                `;
            }).join('');

            resultsEl.innerHTML = `
                <div class="quote-results-header">
                    <div>
                        <strong>Comparacao encontrada</strong>
                        <span>${data.idades.length} vida(s) · ${data.categoria_label} · fonte: ${data.fonte}</span>
                    </div>
                    <button class="btn btn-outline btn-sm" onclick="CIB.closeAllModals(); document.getElementById('section-cotacao')?.scrollIntoView({behavior:'smooth'});">Ver beneficios e redes</button>
                </div>
                <div class="quote-result-list">${cards}</div>
            `;
        }
    },

    /* =========================================
       INIT
       ========================================= */
    init() {
        this.initNavbar();
        this.initMasks();

        // Fechar modais ao clicar no overlay
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                CIB.closeAllModals();
            }
        });

        // Fechar com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') CIB.closeAllModals();
        });

        // Botões com data-modal
        document.querySelectorAll('[data-modal]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                CIB.openModal(btn.dataset.modal);
            });
        });

        // Botões fechar modal
        document.querySelectorAll('.modal-close, [data-modal-close]').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.modal');
                if (modal) CIB.closeModal(modal.id);
            });
        });

        document.querySelectorAll('.nav-mobile a, .nav-mobile button').forEach(item => {
            item.addEventListener('click', () => CIB.closeMobileMenu());
        });

        // Chatbot
        const chatNext     = document.getElementById('chat-next');
        const chatBack     = document.getElementById('chat-back');
        const chatIdentify = document.getElementById('chat-identify');
        const chatInput    = document.getElementById('chat-input');

        if (chatNext)     chatNext.addEventListener('click', () => CIB.quote.next());
        if (chatBack)     chatBack.addEventListener('click', () => CIB.quote.back());
        if (chatIdentify) chatIdentify.addEventListener('click', () => CIB.quote.identify());
        if (chatInput)    chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                chatIdentify?.style.display !== 'none' ? CIB.quote.identify() : CIB.quote.next();
            }
        });

        if (document.getElementById('chat-messages')) {
            CIB.quote.init();
        }

        // Adiciona estilo do toast animation
        const style = document.createElement('style');
        style.textContent = '@keyframes toastIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }';
        document.head.appendChild(style);
    }
};

// Inicializa quando DOM pronto
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}

document.addEventListener('DOMContentLoaded', () => {
    CIB.init();
    if (!window.location.hash && window.location.pathname === '/') {
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    }
});

window.addEventListener('pageshow', () => {
    if (!window.location.hash && window.location.pathname === '/') {
        window.scrollTo(0, 0);
    }
});
