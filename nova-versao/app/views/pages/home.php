<!-- ============================================================
     LANDING PAGE — Concessionária Inteligente Bem
     Design Premium | Healthtech / Fintech Inspired
     ============================================================ -->

<!-- ========== HERO ========== -->
<section class="hero" aria-label="Início">
    <div class="hero-container">
        <!-- Conteúdo -->
        <div class="hero-content">
            <div class="hero-badge">
                <span class="dot"></span>
                Plataforma Oficial Zona Oeste — RJ
            </div>

            <h1>
                Planos de Saúde<br>
                <span class="highlight">que cabem no seu bolso</span>
            </h1>

            <p class="hero-lead">
                Cotação inteligente em minutos. Comparamos as melhores operadoras
                e encontramos o plano ideal para você, sua família ou sua empresa.
            </p>

            <div class="hero-actions">
                <button class="btn btn-primary btn-lg" onclick="CIB.openModal('modal-cotacao')">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Cotar Plano Grátis
                </button>
                <a href="#section-operadoras" class="btn btn-soft-light">
                    Ver Operadoras
                </a>
            </div>

            <!-- Stats -->
            <div class="hero-stats">
                <div>
                    <span class="hero-stat-num">9+</span>
                    <span class="hero-stat-label">Operadoras</span>
                </div>
                <div>
                    <span class="hero-stat-num">5 min</span>
                    <span class="hero-stat-label">Para cotar</span>
                </div>
                <div>
                    <span class="hero-stat-num">100%</span>
                    <span class="hero-stat-label">Gratuito</span>
                </div>
            </div>
        </div>

        <!-- Card de cotação rápida -->
        <div class="hero-card" role="complementary" aria-label="Cotação rápida">
            <div class="hero-card-title">
                Cotação Rápida
                <span>Grátis</span>
            </div>

            <div class="quote-form-group">
                <label class="quote-label" for="hero-quantidade">Quantas pessoas?</label>
                <input type="number" id="hero-quantidade" class="quote-input"
                       placeholder="Ex: 3" min="1" max="99">
            </div>

            <div class="quote-form-group">
                <label class="quote-label" for="hero-idades">Idades dos beneficiários</label>
                <input type="text" id="hero-idades" class="quote-input"
                       placeholder="Ex: 35, 32, 8">
            </div>

            <div class="quote-row">
                <div class="quote-form-group">
                    <label class="quote-label" for="hero-copart">Coparticipação</label>
                    <select id="hero-copart" class="quote-input">
                        <option value="nao">Sem Copart.</option>
                        <option value="sim">Com Copart.</option>
                    </select>
                </div>
                <div class="quote-form-group">
                    <label class="quote-label" for="hero-operadora">Operadora</label>
                    <select id="hero-operadora" class="quote-input">
                        <option value="">Qualquer</option>
                        <?php foreach ($operadoras as $op): ?>
                            <option value="<?= h($op['slug']) ?>"><?= h($op['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button class="btn btn-primary btn-full" style="margin-top: 0.5rem;"
                    onclick="heroQuote()">
                Ver Planos Disponíveis →
            </button>

            <p style="text-align:center; font-size:0.78rem; color:rgba(255,255,255,0.45); margin-top:0.75rem;">
                Sem compromisso. 100% gratuito.
            </p>
        </div>
    </div>

    <!-- Scroll indicator -->
    <a href="#trust-strip" class="scroll-indicator" aria-label="Rolar para baixo">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" width="24" height="24">
            <path d="M6 9l6 6 6-6"/>
        </svg>
    </a>
</section>

<!-- ========== TRUST STRIP ========== -->
<div class="trust-strip" id="trust-strip">
    <div class="trust-strip-inner">
        <span class="trust-label">Trabalhamos com</span>
        <div class="trust-divider"></div>
        <span class="trust-item">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            AMIL
        </span>
        <span class="trust-item">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            UNIMED
        </span>
        <span class="trust-item">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            KLINI SAÚDE
        </span>
        <span class="trust-item">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            CEMERU
        </span>
        <span class="trust-item">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            SAMOC
        </span>
        <span class="trust-item">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            OPLAN SAÚDE
        </span>
        <div class="trust-divider"></div>
        <span class="trust-item">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            9+ Operadoras parceiras
        </span>
        <span class="trust-item">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            100% Gratuito
        </span>
    </div>
</div>

<!-- ========== DIFERENCIAIS ========== -->
<section class="section" id="section-diferenciais" aria-label="Nossos diferenciais">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Por que nos escolher</div>
            <h2 class="section-title">A forma mais inteligente de escolher seu plano</h2>
            <p class="section-lead">
                Tecnologia e atendimento humano juntos para garantir a melhor experiência na contratação do seu plano de saúde.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="26" height="26">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <h3 class="feature-title">Cotação em 5 Minutos</h3>
                <p class="feature-desc">
                    Sem burocracia e sem espera. Preencha um formulário simples e receba as melhores opções de planos para o seu perfil em instantes.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="26" height="26">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="feature-title">Compare Side by Side</h3>
                <p class="feature-desc">
                    Visualize preços e coberturas das principais operadoras lado a lado. Transparência total para você fazer a escolha certa.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="26" height="26">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                </div>
                <h3 class="feature-title">Atendimento Especializado</h3>
                <p class="feature-desc">
                    Corretores certificados prontos para tirar dúvidas e ajudar na escolha do plano. Atendimento humano via WhatsApp ou ligação.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="26" height="26">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="feature-title">Seguro e Confiável</h3>
                <p class="feature-desc">
                    Plataforma segura com dados protegidos. Trabalhamos somente com operadoras regulamentadas pela ANS e de comprovada qualidade.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="26" height="26">
                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                        <path d="M2 10h20"/>
                        <path d="M6 15h.01M10 15h4"/>
                    </svg>
                </div>
                <h3 class="feature-title">100% Gratuito</h3>
                <p class="feature-desc">
                    A cotação é completamente gratuita. Sem taxas, sem pegadinhas. Você só paga o plano escolhido diretamente à operadora.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="26" height="26">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h3 class="feature-title">Acompanhamento Total</h3>
                <p class="feature-desc">
                    Acompanhe sua proposta em tempo real, desde o envio até a ativação do plano. Notificações a cada etapa do processo.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ========== OPERADORAS ========== -->
<section class="section" id="section-operadoras" aria-label="Operadoras parceiras">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Nossas Parceiras</div>
            <h2 class="section-title">Escolha o Plano Ideal</h2>
            <p class="section-lead">
                Trabalhamos com as principais operadoras de saúde da Zona Oeste do Rio de Janeiro.
                Compare e encontre o melhor custo-benefício.
            </p>
        </div>

        <!-- Filtro rápido -->
        <div class="premium-filter">
            <button class="btn btn-primary btn-sm" onclick="filterOperadoras('todos')">Todas</button>
            <button class="btn btn-outline btn-sm" onclick="filterOperadoras('sem')">Sem Coparticipação</button>
            <button class="btn btn-outline btn-sm" onclick="filterOperadoras('com')">Com Coparticipação</button>
        </div>

        <div class="operadoras-grid" id="operadoras-grid">
            <?php foreach ($operadoras as $op): ?>
            <div class="operadora-card" onclick="CIB.openModal('modal-cotacao')"
                 data-operadora="<?= h($op['slug']) ?>"
                 role="button" tabindex="0"
                 aria-label="Ver planos <?= h($op['nome']) ?>"
                 onkeydown="if(event.key==='Enter') CIB.openModal('modal-cotacao')">

                <div class="operadora-card-img-placeholder">
                    <?php
                    $icons = [
                        'amil' => '🏥', 'unimed' => '⚕️', 'klini-saude' => '📈',
                        'cemeru' => '🛡️', 'samoc' => '🌍', 'oplan-saude' => '💡',
                        'abrabdir' => '⚖️', 'abracem' => '💼', 'aba' => '🌟',
                    ];
                    echo $icons[$op['slug']] ?? '🏥';
                    ?>
                </div>

                <div class="operadora-card-body">
                    <h3 class="operadora-card-name"><?= h($op['nome']) ?></h3>
                    <p class="operadora-card-desc">
                        <?= h($op['descricao'] ?? 'Planos completos com rede credenciada de qualidade.') ?>
                    </p>
                    <button class="btn btn-primary btn-sm btn-full">
                        Ver Planos e Preços →
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ========== COMO FUNCIONA ========== -->
<section class="section section-alt" id="section-como-funciona" aria-label="Como funciona">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Simples e Rápido</div>
            <h2 class="section-title">Como Funciona</h2>
            <p class="section-lead">
                Em poucos passos você compara planos, faz a cotação e envia sua proposta —
                tudo online e sem burocracia.
            </p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-icon">
                    <div class="step-number">1</div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="1"/>
                        <path d="M9 12h6M9 16h4"/>
                    </svg>
                </div>
                <h3 class="step-title">Preencha o Formulário</h3>
                <p class="step-desc">
                    Informe quantas pessoas, as idades e sua profissão. Leva menos de 2 minutos.
                </p>
            </div>

            <div class="step-card">
                <div class="step-icon">
                    <div class="step-number">2</div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="step-title">Compare os Planos</h3>
                <p class="step-desc">
                    Veja os preços de todas as operadoras lado a lado e escolha o que melhor se encaixa no seu orçamento.
                </p>
            </div>

            <div class="step-card">
                <div class="step-icon">
                    <div class="step-number">3</div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="step-title">Envie sua Proposta</h3>
                <p class="step-desc">
                    Preencha a ficha cadastral, envie os documentos e acompanhe o status da sua proposta em tempo real.
                </p>
            </div>

            <div class="step-card">
                <div class="step-icon">
                    <div class="step-number">4</div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
                        <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="step-title">Plano Ativo</h3>
                <p class="step-desc">
                    Após aprovação, seu plano é ativado e você recebe a carteirinha digital. Simples assim!
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ========== TABELA DE PREÇOS ========== -->
<section class="section" id="section-precos" aria-label="Tabela de preços">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Transparência Total</div>
            <h2 class="section-title">Tabela de Preços de Referência</h2>
            <p class="section-lead">
                Preços por faixa etária. Valores são de referência — solicite uma cotação
                personalizada para valores exatos conforme sua situação.
            </p>
        </div>

        <!-- Toggle coparticipação -->
        <div class="premium-actions" style="margin-bottom:2rem;">
            <div class="segmented-control">
                <button id="btn-sem-copart" class="btn btn-primary btn-sm" onclick="showPriceTable('sem')">
                    Sem Coparticipação
                </button>
                <button id="btn-com-copart" class="btn btn-ghost btn-sm" onclick="showPriceTable('com')">
                    Com Coparticipação
                </button>
            </div>
        </div>

        <!-- Tabela sem copart -->
        <div id="table-sem-copart" class="price-table-wrapper">
            <table class="price-table" aria-label="Tabela sem coparticipação">
                <thead>
                    <tr>
                        <th>Operadora</th>
                        <th>Plano</th>
                        <th>0–18 anos</th>
                        <th>19–28 anos</th>
                        <th>29–43 anos</th>
                        <th>44–58 anos</th>
                        <th>59+ anos</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>AMIL</strong></td>
                        <td>Completo</td>
                        <td class="price-badge">R$ 280</td><td class="price-badge">R$ 320</td>
                        <td class="price-badge">R$ 380</td><td class="price-badge">R$ 450</td>
                        <td class="price-badge">R$ 580</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                    <tr>
                        <td><strong>UNIMED</strong></td>
                        <td>Completo</td>
                        <td class="price-badge">R$ 290</td><td class="price-badge">R$ 330</td>
                        <td class="price-badge">R$ 390</td><td class="price-badge">R$ 460</td>
                        <td class="price-badge">R$ 590</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                    <tr>
                        <td><strong>KLINI</strong></td>
                        <td>Flex</td>
                        <td class="price-badge">R$ 260</td><td class="price-badge">R$ 300</td>
                        <td class="price-badge">R$ 360</td><td class="price-badge">R$ 430</td>
                        <td class="price-badge">R$ 550</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                    <tr>
                        <td><strong>CEMERU</strong></td>
                        <td>Tradicional</td>
                        <td class="price-badge">R$ 270</td><td class="price-badge">R$ 310</td>
                        <td class="price-badge">R$ 370</td><td class="price-badge">R$ 440</td>
                        <td class="price-badge">R$ 560</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                    <tr>
                        <td><strong>SAMOC</strong></td>
                        <td>Regional</td>
                        <td class="price-badge">R$ 250</td><td class="price-badge">R$ 290</td>
                        <td class="price-badge">R$ 350</td><td class="price-badge">R$ 420</td>
                        <td class="price-badge">R$ 540</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                    <tr>
                        <td><strong>OPLAN</strong></td>
                        <td>Flex</td>
                        <td class="price-badge">R$ 240</td><td class="price-badge">R$ 280</td>
                        <td class="price-badge">R$ 340</td><td class="price-badge">R$ 410</td>
                        <td class="price-badge">R$ 530</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tabela com copart -->
        <div id="table-com-copart" class="price-table-wrapper hidden">
            <table class="price-table" aria-label="Tabela com coparticipação">
                <thead>
                    <tr>
                        <th>Operadora</th>
                        <th>Plano</th>
                        <th>0–18 anos</th>
                        <th>19–28 anos</th>
                        <th>29–43 anos</th>
                        <th>44–58 anos</th>
                        <th>59+ anos</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>AMIL</strong></td>
                        <td>Completo</td>
                        <td class="price-badge">R$ 220</td><td class="price-badge">R$ 250</td>
                        <td class="price-badge">R$ 300</td><td class="price-badge">R$ 360</td>
                        <td class="price-badge">R$ 460</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                    <tr>
                        <td><strong>UNIMED</strong></td>
                        <td>Completo</td>
                        <td class="price-badge">R$ 230</td><td class="price-badge">R$ 260</td>
                        <td class="price-badge">R$ 310</td><td class="price-badge">R$ 370</td>
                        <td class="price-badge">R$ 470</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                    <tr>
                        <td><strong>KLINI</strong></td>
                        <td>Flex</td>
                        <td class="price-badge">R$ 200</td><td class="price-badge">R$ 230</td>
                        <td class="price-badge">R$ 280</td><td class="price-badge">R$ 340</td>
                        <td class="price-badge">R$ 440</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                    <tr>
                        <td><strong>CEMERU</strong></td>
                        <td>Tradicional</td>
                        <td class="price-badge">R$ 210</td><td class="price-badge">R$ 240</td>
                        <td class="price-badge">R$ 290</td><td class="price-badge">R$ 350</td>
                        <td class="price-badge">R$ 450</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                    <tr>
                        <td><strong>SAMOC</strong></td>
                        <td>Regional</td>
                        <td class="price-badge">R$ 190</td><td class="price-badge">R$ 220</td>
                        <td class="price-badge">R$ 270</td><td class="price-badge">R$ 330</td>
                        <td class="price-badge">R$ 430</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                    <tr>
                        <td><strong>OPLAN</strong></td>
                        <td>Flex</td>
                        <td class="price-badge">R$ 180</td><td class="price-badge">R$ 210</td>
                        <td class="price-badge">R$ 260</td><td class="price-badge">R$ 320</td>
                        <td class="price-badge">R$ 420</td>
                        <td><button class="btn btn-primary btn-sm" onclick="CIB.openModal('modal-cotacao')">Cotar</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="muted-note">
            * Valores de referência. Preços podem variar conforme vigência da tabela. Solicite cotação para valores exatos.
        </p>
    </div>
</section>

<!-- ========== BENEFICIOS E REDE CREDENCIADA ========== -->
<section class="section section-alt benefits-network-section" id="section-cotacao" aria-label="Consulta de beneficios e redes credenciadas">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Consulta publica</div>
            <h2 class="section-title">Beneficios e redes credenciadas por plano</h2>
            <p class="section-lead">
                Consulte uma previa dos beneficios, hospitais, clinicas e laboratorios disponiveis
                antes de falar com um consultor.
            </p>
        </div>

        <div class="benefits-network-shell">
            <div class="benefits-network-hero">
                <div>
                    <span class="benefits-kicker">Explorador de rede</span>
                    <h3>Veja se o plano conversa com a rotina do cliente.</h3>
                    <p>
                        Compare beneficios, perfil de cobertura e unidades de referencia em uma
                        experiencia simples para apoiar a decisao antes da cotacao final.
                    </p>
                </div>
                <div class="benefits-live-card">
                    <span>Consulta ativa</span>
                    <strong id="benefit-live-plan">AMIL Select Oeste</strong>
                    <small>Dados demonstrativos para validacao comercial</small>
                </div>
            </div>

            <div class="benefits-network-controls">
                <div class="benefit-field">
                    <label for="benefit-plan-select">Plano para consulta</label>
                    <select id="benefit-plan-select" class="form-control" onchange="renderBenefitNetwork()">
                        <option value="amil-select">AMIL Select Oeste - sem coparticipacao</option>
                        <option value="cemeru-tradicional">CEMERU Tradicional - com coparticipacao</option>
                        <option value="unimed-plus">Unimed Plus Regional - sem coparticipacao</option>
                        <option value="klini-essencial">Klini Essencial - com coparticipacao</option>
                    </select>
                </div>

                <div class="benefit-field">
                    <label for="benefit-location-input">Bairro, cidade ou CEP</label>
                    <input id="benefit-location-input" class="form-control" type="text"
                           placeholder="Ex: Campo Grande, Bangu, 23000-000"
                           oninput="renderBenefitNetwork()">
                </div>
            </div>

            <div class="benefits-network-grid">
                <article id="benefit-plan-card" class="benefit-plan-card" aria-live="polite"></article>
                <aside class="network-panel">
                    <div class="network-panel-head">
                        <div>
                            <span class="eyebrow">Rede credenciada</span>
                            <h3>Unidades de referencia</h3>
                        </div>
                        <span class="network-count" id="network-count">0 redes</span>
                    </div>
                    <div id="network-results" class="network-results" aria-live="polite"></div>
                    <p class="benefit-disclaimer">
                        Dados demonstrativos para testes. A rede e os beneficios finais devem ser confirmados
                        pelo consultor antes da proposta.
                    </p>
                </aside>
            </div>
        </div>
    </div>
</section>

<!-- ========== DEPOIMENTOS ========== -->
<section class="section" id="section-depoimentos" aria-label="Depoimentos de clientes">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">Quem já escolheu</div>
            <h2 class="section-title">O que nossos clientes dizem</h2>
            <p class="section-lead">
                Mais de 500 famílias e empresas da Zona Oeste do Rio já contrataram seus planos de saúde com a gente.
            </p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="testimonial-text">"Consegui um plano completo para minha família em menos de 10 minutos. O atendimento foi excelente e os preços muito melhores do que eu esperava!"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">MR</div>
                    <div>
                        <div class="testimonial-name">Marcos Ribeiro</div>
                        <div class="testimonial-role">Autônomo — Campo Grande, RJ</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="testimonial-text">"Gerencio 8 funcionários e precisava de um plano empresarial. A plataforma facilitou tudo: cotação, proposta e acompanhamento em um só lugar."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">CS</div>
                    <div>
                        <div class="testimonial-name">Carla Santos</div>
                        <div class="testimonial-role">Empresária — Bangu, RJ</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="testimonial-text">"Corretor excelente, me explicou tudo com paciência. Encontrei um plano com coparticipação que reduziu meu custo mensal em quase 30%."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">JO</div>
                    <div>
                        <div class="testimonial-name">Jorge Oliveira</div>
                        <div class="testimonial-role">Professor — Santa Cruz, RJ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== CTA FINAL ========== -->
<section class="section section-dark" id="section-contato" aria-label="Contato e CTA">
    <div class="container text-center">
        <div class="section-badge">Fale Conosco</div>
        <h2 class="section-title" style="color:white; max-width:600px; margin:1rem auto;">
            Pronto para cuidar da sua saúde?
        </h2>
        <p class="section-lead" style="max-width:480px; margin:0 auto 2.5rem;">
            Nossa equipe está pronta para ajudar você a escolher o melhor plano.
            Atendimento por WhatsApp, telefone ou pelo nosso Rede Credenciada.
        </p>

        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; margin-bottom:3rem;">
            <button class="btn btn-primary btn-lg" onclick="CIB.openModal('modal-cotacao')">
                Cotar Agora — É Grátis
            </button>
            <a href="https://wa.me/5521968827864?text=Olá!%20Quero%20cotar%20um%20plano%20de%20saúde."
               target="_blank" rel="noopener"
               class="btn btn-lg" style="background:#25D366; color:white; border-color:#25D366;">
                <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884z"/>
                </svg>
                WhatsApp
            </a>
        </div>

        <div class="contact-grid">
            <div style="padding:1.5rem; background:rgba(255,255,255,0.06); border-radius:var(--radius-lg); border:1px solid rgba(255,255,255,0.1);">
                <div style="font-size:1.75rem; margin-bottom:0.5rem;">📞</div>
                <div style="font-weight:700; color:white; margin-bottom:0.25rem;">Telefone</div>
                <div style="color:var(--green); font-size:0.95rem;">(21) 96882-7864</div>
            </div>
            <div style="padding:1.5rem; background:rgba(255,255,255,0.06); border-radius:var(--radius-lg); border:1px solid rgba(255,255,255,0.1);">
                <div style="font-size:1.75rem; margin-bottom:0.5rem;">💬</div>
                <div style="font-weight:700; color:white; margin-bottom:0.25rem;">WhatsApp</div>
                <div style="color:var(--green); font-size:0.95rem;">Atendimento imediato</div>
            </div>
            <div style="padding:1.5rem; background:rgba(255,255,255,0.06); border-radius:var(--radius-lg); border:1px solid rgba(255,255,255,0.1);">
                <div style="font-size:1.75rem; margin-bottom:0.5rem;">🤖</div>
                <div style="font-weight:700; color:white; margin-bottom:0.25rem;">Rede Credenciada</div>
                <div style="color:var(--green); font-size:0.95rem;">Consulta inicial</div>
            </div>
        </div>
    </div>
</section>

<!-- ========== MODAL COTAÇÃO COMPLETA ========== -->
<div id="modal-cotacao" class="modal" role="dialog" aria-modal="true" aria-label="Simulação de cotação" aria-hidden="true">
    <div class="modal-content" style="max-width:700px;">
        <div class="modal-header">
            <div>
                <div class="modal-title">📊 Simulação de Cotação</div>
                <div class="modal-subtitle">Compare planos e encontre o ideal para você</div>
            </div>
            <button class="modal-close" aria-label="Fechar">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Resultados calculados pela API de cotacao -->
            <div id="quote-results" class="quote-results hidden" aria-live="polite"></div>
            <p id="quote-modal-intro" style="text-align:center; color:var(--gray-500); padding:2rem 0;">
                Informe as idades no card inicial para simular valores, ou consulte os
                <strong>beneficios e redes credenciadas</strong> antes de falar com um consultor.
            </p>
            <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
                <button class="btn btn-primary" onclick="CIB.closeAllModals(); document.getElementById('section-cotacao').scrollIntoView({behavior:'smooth'});">
                    Consultar Beneficios e Redes
                </button>
                <a href="https://wa.me/5521968827864?text=Olá!%20Quero%20cotar%20um%20plano%20de%20saúde."
                   target="_blank" rel="noopener" class="btn btn-outline">
                    Falar no WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ========== SCRIPTS DA HOME ========== -->
<script>
function showPriceTable(type) {
    const semEl  = document.getElementById('table-sem-copart');
    const comEl  = document.getElementById('table-com-copart');
    const btnSem = document.getElementById('btn-sem-copart');
    const btnCom = document.getElementById('btn-com-copart');

    if (type === 'com') {
        semEl?.classList.add('hidden');
        comEl?.classList.remove('hidden');
        btnSem?.classList.replace('btn-primary', 'btn-ghost');
        btnCom?.classList.replace('btn-ghost', 'btn-primary');
    } else {
        comEl?.classList.add('hidden');
        semEl?.classList.remove('hidden');
        btnCom?.classList.replace('btn-primary', 'btn-ghost');
        btnSem?.classList.replace('btn-ghost', 'btn-primary');
    }
}

function heroQuote() {
    const idades = document.getElementById('hero-idades')?.value;

    if (idades) {
        CIB.quote.fetchCotacao({
            idades,
            categoria: document.getElementById('hero-copart')?.value || 'nao',
            operadora: document.getElementById('hero-operadora')?.value || ''
        });
        return;
    }

    const benefitsSection = document.getElementById('section-cotacao');
    benefitsSection?.scrollIntoView({ behavior: 'smooth' });
}

const benefitPlans = {
    'amil-select': {
        operadora: 'AMIL',
        plano: 'AMIL Select Oeste',
        price: 'a partir de R$ 289,00',
        profile: 'Melhor equilibrio entre rede ampla e custo mensal.',
        badge: 'Mais consultado',
        score: '4.8',
        tags: ['Sem coparticipacao', 'Zona Oeste RJ', 'Telemedicina 24h'],
        benefits: ['Consultas eletivas', 'Urgencia e emergencia', 'Exames laboratoriais', 'Desconto em farmacia', 'Carteirinha digital'],
        network: [
            ['Hospital Oeste D Or', 'Hospital', 'Campo Grande'],
            ['Clinica Bem Viver', 'Clinica', 'Bangu'],
            ['LabSaude Diagnosticos', 'Laboratorio', 'Santa Cruz']
        ]
    },
    'cemeru-tradicional': {
        operadora: 'CEMERU',
        plano: 'CEMERU Tradicional',
        price: 'a partir de R$ 210,00',
        profile: 'Opcao regional competitiva para atendimento na Zona Oeste.',
        badge: 'Custo-beneficio',
        score: '4.6',
        tags: ['Com coparticipacao', 'Rede regional', 'Plano familiar'],
        benefits: ['Consultas com custo parcial', 'Pronto atendimento', 'Exames simples', 'Rede local forte', 'Suporte para autorizacoes'],
        network: [
            ['Hospital Cemeru', 'Hospital', 'Campo Grande'],
            ['Centro Medico Realengo', 'Clinica', 'Realengo'],
            ['Diagnostica Oeste', 'Laboratorio', 'Guaratiba']
        ]
    },
    'unimed-plus': {
        operadora: 'Unimed',
        plano: 'Unimed Plus Regional',
        price: 'a partir de R$ 340,00',
        profile: 'Rede conhecida e boa cobertura para rotina medica.',
        badge: 'Rede reconhecida',
        score: '4.7',
        tags: ['Sem coparticipacao', 'Regional RJ', 'Preventivo'],
        benefits: ['Rede de especialistas', 'Programa de prevencao', 'Exames de imagem', 'Atendimento pediatrico', 'Canal digital'],
        network: [
            ['Unimed Centro Clinico', 'Clinica', 'Barra da Tijuca'],
            ['Hospital Regional Saude', 'Hospital', 'Jacarepagua'],
            ['Imagem Rio', 'Diagnostico', 'Recreio']
        ]
    },
    'klini-essencial': {
        operadora: 'Klini Saude',
        plano: 'Klini Essencial',
        price: 'a partir de R$ 195,00',
        profile: 'Entrada acessivel para clientes que priorizam mensalidade menor.',
        badge: 'Mais economico',
        score: '4.5',
        tags: ['Com coparticipacao', 'Mensalidade baixa', 'Consultas guiadas'],
        benefits: ['Consultas presenciais', 'Teleorientacao', 'Exames basicos', 'Rede ambulatorial', 'Acompanhamento digital'],
        network: [
            ['Klini Campo Grande', 'Clinica', 'Campo Grande'],
            ['Clinica Popular Oeste', 'Clinica', 'Bangu'],
            ['Lab Mais Saude', 'Laboratorio', 'Paciencia']
        ]
    }
};

function escapeBenefitText(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
}

function renderBenefitNetwork() {
    const selected = document.getElementById('benefit-plan-select')?.value || 'amil-select';
    const location = document.getElementById('benefit-location-input')?.value.trim();
    const data = benefitPlans[selected] || benefitPlans['amil-select'];
    const card = document.getElementById('benefit-plan-card');
    const network = document.getElementById('network-results');
    const count = document.getElementById('network-count');
    const livePlan = document.getElementById('benefit-live-plan');

    if (!card || !network) return;

    card.innerHTML = `
        <div class="benefit-card-top">
            <div>
                <span class="benefit-operator">${escapeBenefitText(data.operadora)}</span>
                <h3>${escapeBenefitText(data.plano)}</h3>
            </div>
            <span class="benefit-score">${escapeBenefitText(data.score)} / 5</span>
        </div>
        <p class="benefit-profile">${escapeBenefitText(data.profile)}</p>
        <div class="benefit-price">${escapeBenefitText(data.price)} <small>/mes</small></div>
        <div class="benefit-pill-row">
            <span class="benefit-pill benefit-pill-featured">${escapeBenefitText(data.badge)}</span>
            ${data.tags.map((tag) => `<span class="benefit-pill">${escapeBenefitText(tag)}</span>`).join('')}
        </div>
        <ul class="benefit-check-list">
            ${data.benefits.map((item) => `<li>${escapeBenefitText(item)}</li>`).join('')}
        </ul>
        <button class="btn btn-primary btn-full" onclick="CIB.openModal('modal-cotacao')">Simular valores deste perfil</button>
    `;

    const locationLabel = location ? `Proximo a ${location}` : 'Area de cobertura demonstrativa';
    network.innerHTML = data.network.map((item) => `
        <article class="network-card">
            <div class="network-icon">${escapeBenefitText(item[1]).slice(0, 1)}</div>
            <div>
                <strong>${escapeBenefitText(item[0])}</strong>
                <span>${escapeBenefitText(item[1])} - ${escapeBenefitText(item[2])}</span>
                <small>${escapeBenefitText(locationLabel)}</small>
            </div>
        </article>
    `).join('');

    if (count) count.textContent = `${data.network.length} redes`;
    if (livePlan) livePlan.textContent = data.plano;
}

document.addEventListener('DOMContentLoaded', renderBenefitNetwork);

function filterOperadoras(tipo, button) {
    // Placeholder para filtro futuro — por enquanto mostra todas
    document.querySelectorAll('.premium-filter .btn').forEach((button) => {
        button.classList.remove('btn-primary');
        button.classList.add('btn-outline');
    });
    const activeButton = button || window.event?.currentTarget;
    activeButton?.classList.remove('btn-outline');
    activeButton?.classList.add('btn-primary');
    CIB.toast('Filtrando por: ' + tipo, 'info', 1500);
}
</script>
