// backend/services/asaas.js
const axios = require('axios');
require('dotenv').config();

const asaas = axios.create({
  baseURL: process.env.ASAAS_BASE_URL,
  headers: {
    'access_token': process.env.ASAAS_API_KEY,
    'Content-Type': 'application/json'
  }
});

// Criar pagamento PIX
exports.createPixPayment = async ({ customer, value, description, externalReference, dueDate }) => {
  try {
    // 1. Criar ou buscar cliente no Asaas
    let customerData = await asaas.get('/customers', { params: { cpfCnpj: customer.cpfCnpj } });
    
    if (!customerData.data.data.length) {
      customerData = await asaas.post('/customers', {
        name: customer.name,
        cpfCnpj: customer.cpfCnpj,
        email: customer.email,
        phone: customer.phone,
        mobilePhone: customer.phone,
        address: customer.address,
        addressNumber: customer.addressNumber,
        complement: customer.complement,
        province: customer.bairro,
        postalCode: customer.cep,
        addressRequired: false,
        companyName: customer.name
      });
    }
    const customerId = customerData.data.data[0]?.id || customerData.data.id;

    // 2. Criar pagamento PIX
    const payment = await asaas.post('/payments', {
      customer: customerId,
      billingType: 'PIX',
      value,
      dueDate: dueDate || new Date(Date.now() + 86400000).toISOString().split('T')[0], // +1 dia
      description,
      externalReference,
      postalService: false,
      pix: {
        expirationDate: new Date(Date.now() + 3600000).toISOString() // QR Code válido por 1h
      }
    });

    return {
      paymentId: payment.data.id,
      pixQrCode: payment.data.pixQrCode, // String "copia e cola"
      qrCodeUrl: `${process.env.ASAAS_BASE_URL}/payments/${payment.data.id}/pixQrCode`,
      invoiceUrl: payment.data.invoiceUrl,
      status: payment.data.status
    };
  } catch (error) {
    console.error('Erro Asaas:', error.response?.data || error.message);
    throw new Error(error.response?.data?.errors?.[0]?.description || 'Erro ao gerar PIX');
  }
};

// Consultar status do pagamento
exports.getPaymentStatus = async (paymentId) => {
  try {
    const { data } = await asaas.get(`/payments/${paymentId}`);
    return {
      status: data.status, // PENDING, RECEIVED, OVERDUE, etc.
      value: data.value,
      confirmedDate: data.confirmationDate
    };
  } catch (error) {
    console.error('Erro ao consultar pagamento:', error.message);
    throw error;
  }
};

// Validar webhook (segurança)
exports.validateWebhook = (req) => {
  const token = req.headers['asaas_signature'] || req.query.token;
  return token === process.env.ASAAS_WEBHOOK_TOKEN;
};

module.exports = exports;