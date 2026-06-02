// Substitua a função de geração de PIX no app.js por esta:

async function gerarPixAsaas(tipo, valor, usuario_id, dados_cliente = {}) {
  const res = await fetch(`${API}/pagamentos/gerar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ tipo, valor, usuario_id, dados_cliente })
  });
  return await res.json();
}

// Exemplo: Após cadastro de empresa
document.getElementById('form-empresa')?.addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const dados = Object.fromEntries(fd);
  
  // 1. Cadastrar empresa
  const res = await fetch(`${API}/auth/cadastro-empresa`, { method: 'POST', body: fd });
  const data = await res.json();
  
  if (data.success) {
    // 2. Gerar PIX Asaas
    const pix = await gerarPixAsaas('empresa', 490, data.userId, {
      name: dados.razao_social || dados.email,
      email: dados.email,
      phone: dados.telefone,
      cpfCnpj: dados.cnpj,
      cep: dados.cep,
      endereco: dados.endereco,
      bairro: dados.bairro,
      numero: dados.numero
    });
    
    if (pix.qr_code) {
      // Exibir QR Code "copia e cola" + botão para abrir no celular
      const modal = document.getElementById('modal-pagamento-empresa');
      modal.querySelector('#qr-empresa').innerHTML = `
        <div class="asaas-pix-box">
          <p class="asaas-label">PIX Copia e Cola:</p>
          <textarea id="pix-copia-cola" readonly>${pix.qr_code}</textarea>
          <button class="btn-copy" onclick="navigator.clipboard.writeText(document.getElementById('pix-copia-cola').value); alert('Copiado!')">📋 Copiar</button>
          <a href="${pix.invoice_url}" target="_blank" class="btn-invoice">🔗 Abrir no Celular</a>
        </div>
      `;
      modal.style.display = 'flex';
      
      // Polling para verificar confirmação
      const checkStatus = setInterval(async () => {
        const statusRes = await fetch(`${API}/pagamentos/status/${pix.txid}`);
        const statusData = await statusRes.json();
        if (statusData.status === 'confirmado') {
          clearInterval(checkStatus);
          alert('✅ Pagamento confirmado! Seu acesso foi liberado.');
          modal.style.display = 'none';
          location.reload();
        }
      }, 5000); // Verifica a cada 5 segundos
    } else {
      alert(pix.error || 'Erro ao gerar PIX');
    }
  } else {
    alert(data.error);
  }
});