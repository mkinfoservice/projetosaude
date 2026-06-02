const asaasService = require('./services/asaas');

// 💳 GERAR PIX VIA ASAAS
app.post('/api/pagamentos/gerar', async (req, res) => {
  try {
    const { tipo, valor, usuario_id, dados_cliente } = req.body;
    
    // Buscar dados do usuário no banco para enviar ao Asaas
    const [user] = await pool.query('SELECT u.*, e.cnpj, e.razao_social, e.cep, e.endereco, e.bairro, e.numero FROM usuarios u LEFT JOIN empresas e ON u.id = e.usuario_id WHERE u.id = ?', [usuario_id]);
    
    if (!user.length) return res.status(404).json({ error: 'Usuário não encontrado' });
    const u = user[0];

    const externalReference = `${tipo.toUpperCase()}_${usuario_id}_${Date.now()}`;
    
    const pixData = await asaasService.createPixPayment({
      customer: {
        name: u.razao_social || u.email?.split('@')[0] || 'Cliente',
        cpfCnpj: u.cnpj || u.cpf || '',
        email: u.email,
        phone: u.telefone?.replace(/\D/g, '') || '',
        address: u.endereco || '',
        addressNumber: u.numero || 'S/N',
        complement: '',
        bairro: u.bairro || '',
        cep: u.cep?.replace(/\D/g, '') || ''
      },
      value: Number(valor),
      description: `Ativação ${tipo} - Concessionária Bem`,
      externalReference,
      dueDate: new Date(Date.now() + 2*86400000).toISOString().split('T')[0] // +2 dias
    });

    // Salvar no banco
    await pool.query(
      'INSERT INTO pagamentos (id, usuario_id, tipo, valor, pix_qr_base64, pix_copia_ecola, txid, status) VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?)',
      [usuario_id, tipo, valor, null, pixData.pixQrCode, pixData.paymentId, pixData.status]
    );

    res.json({ 
      qr_code: pixData.pixQrCode, 
      qr_code_url: pixData.qrCodeUrl,
      invoice_url: pixData.invoiceUrl,
      txid: pixData.paymentId,
      status: pixData.status
    });

  } catch (error) {
    console.error('Erro gerar PIX Asaas:', error);
    res.status(500).json({ error: error.message || 'Erro ao gerar PIX' });
  }
});

// 🔔 WEBHOOK ASAAS (Configure no painel Asaas)
app.post('/api/pagamentos/webhook', express.raw({ type: 'application/json' }), async (req, res) => {
  try {
    // Validar assinatura do webhook (recomendado)
    // if (!asaasService.validateWebhook(req)) return res.sendStatus(401);
    
    const event = JSON.parse(req.body);
    
    // Eventos relevantes: PAYMENT_CREATED, PAYMENT_RECEIVED, PAYMENT_OVERDUE
    if (event.event === 'PAYMENT_RECEIVED' || event.event === 'PAYMENT_CONFIRMED') {
      const paymentId = event.data?.id || event.data?.paymentId;
      if (!paymentId) return res.sendStatus(200);

      // Atualizar status no banco
      await pool.query('UPDATE pagamentos SET status = ? WHERE txid = ?', ['confirmado', paymentId]);

      // Ativar usuário/empresa/vendedor
      const [pag] = await pool.query('SELECT * FROM pagamentos WHERE txid = ? LIMIT 1', [paymentId]);
      if (pag.length && pag[0].status === 'confirmado') {
        const tabela = pag[0].tipo === 'empresa' ? 'empresas' : 'vendedores';
        await pool.query(`UPDATE ${tabela} SET pagamento_status = 'confirmado' WHERE usuario_id = ?`, [pag[0].usuario_id]);
        await pool.query(`UPDATE usuarios SET status = 'ativo' WHERE id = ?`, [pag[0].usuario_id]);
        
        // Log LGPD
        await pool.query('INSERT INTO audit_logs (usuario_id, acao, tabela_afetada, ip_origem) VALUES (?, ?, ?, ?)',
          [pag[0].usuario_id, 'pagamento_confirmado', tabela, req.ip]);
      }
    }

    res.sendStatus(200);
  } catch (error) {
    console.error('Erro webhook Asaas:', error);
    res.sendStatus(500);
  }
});

// 🔍 CONSULTAR STATUS DO PIX (Para frontend polling)
app.get('/api/pagamentos/status/:txid', async (req, res) => {
  try {
    const { txid } = req.params;
    const status = await asaasService.getPaymentStatus(txid);
    
    // Atualizar localmente se mudou
    if (status.status === 'RECEIVED') {
      await pool.query('UPDATE pagamentos SET status = ? WHERE txid = ?', ['confirmado', txid]);
    }
    
    res.json({ 
      status: status.status === 'RECEIVED' ? 'confirmado' : status.status.toLowerCase(),
      confirmed_at: status.confirmedDate
    });
  } catch (error) {
    res.status(500).json({ error: 'Erro ao consultar status' });
  }
});