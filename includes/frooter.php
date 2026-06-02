<?php
/**
 * includes/footer.php
 * Rodapé, scripts globais e modais reutilizáveis (Login, PIX, Simulação)
 */
?>
    </main>

    <footer class="footer">
        <div class="container text-center">
            <p>💬 © <?= date('Y') ?> Concessionária Inteligente Bem | Contato: (21) 96882-7864</p>
            <p class="small text-muted">Sistema seguro, LGPD compliant e operadoras regulamentadas pela ANS.</p>
        </div>
    </footer>

    <!-- ===== MODAL LOGIN ===== -->
    <div id="modal-login" class="modal">
        <div class="modal-content">
            <button class="modal-close">&times;</button>
            <h3>🔐 Acesso Administrativo</h3>
            <form id="form-login" class="mt-2">
                <div class="form-group">
                    <label>Perfil de Acesso *</label>
                    <select name="type" class="form-control" required>
                        <option value="">Selecione...</option>
                        <option value="supervisor">Supervisor Operacional</option>
                        <option value="gerente">Gerente de Negócios</option>
                        <option value="vendedor">Vendedor / Corretor</option>
                        <option value="empresa">Empresa</option>
                    </select>
                </div>
                <div class="form-group">
                    <label id="login-identifier-label">Telefone / ID *</label>
                    <input type="text" name="identifier" class="form-control" placeholder="Digite seu telefone ou e-mail" required>
                </div>
                <div class="form-group">
                    <label>Senha *</label>
                    <input type="password" name="password" class="form-control" placeholder="Sua senha" required>
                </div>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="login">
                <button type="submit" class="btn btn-primary">Entrar no Sistema</button>
                <p class="mt-2 text-center small">
                    Primeiro acesso? <a href="#modal-register-vendedor" data-modal="modal-register-vendedor">Cadastre-se como Vendedor</a>
                </p>
            </form>
        </div>
    </div>

    <!-- ===== MODAL SIMULAÇÃO (Chatbot + Tabela) ===== -->
    <div id="modal-simulacao" class="modal">
        <div class="modal-content" style="max-width: 650px;">
            <button class="modal-close">&times;</button>
            <h3>📊 Simulação de Cotação Inteligente</h3>
            <div class="chatbot-steps mt-2">
                <div class="step active"></div><div class="step"></div><div class="step"></div><div class="step"></div>
            </div>
            <div id="chat-messages" class="chatbot-messages mt-2">
                <div class="message bot">Olá! Sou o assistente virtual. Para quantas pessoas será o plano?</div>
            </div>
            <div class="chatbot-input mt-2">
                <input type="text" id="chat-input" placeholder="Digite sua resposta...">
                <button id="chat-back" class="btn btn-outline" style="display:none;">← Voltar</button>
                <button id="chat-next" class="btn btn-primary">Próximo ➜</button>
                <button id="chat-identify" class="btn btn-secondary" style="display:none;">Identificar ➜</button>
            </div>
            <div id="price-table-container" class="mt-3 hidden">
                <h4>📋 Tabela de Preços</h4>
                <div class="category-toggle">
                    <button class="btn btn-outline active" data-category="COM_COPARTICIPACAO">Com Coparticipação</button>
                    <button class="btn btn-outline" data-category="SEM_COPARTICIPACAO">Sem Coparticipação</button>
                </div>
                <div class="table-container mt-2">
                    <table id="price-table-body">
                        <thead><tr><th>Operadora</th><th>Plano</th><th>0-18</th><th>19-28</th><th>29-43</th><th>44-58</th><th>59+</th><th></th></tr></thead>
                        <tbody><tr><td colspan="8" class="text-center">Selecione uma empresa para ver os preços</td></tr></tbody>
                    </table>
                </div>
                <div class="mt-2 text-center">
                    <button class="btn btn-primary" onclick="ModalManager.hide('modal-simulacao'); document.getElementById('section-cadastro').scrollIntoView({behavior:'smooth'})">Continuar para Cadastro ➜</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SCRIPTS GLOBAIS ===== -->
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/pix-handler.js"></script>
    <script>
    // Ajuste dinâmico do campo de login conforme perfil
    document.querySelector('[name="type"]')?.addEventListener('change', function() {
        const label = document.getElementById('login-identifier-label');
        if (this.value === 'empresa') {
            label.textContent = 'E-mail *';
            document.querySelector('[name="identifier"]').placeholder = 'seu@email.com';
        } else {
            label.textContent = 'Telefone / ID *';
            document.querySelector('[name="identifier"]').placeholder = '(21) 99999-9999';
        }
    });
    </script>
</body>
</html>