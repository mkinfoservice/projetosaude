#!/bin/bash
set -e

# Gera o arquivo .env a partir das variáveis de ambiente do container (Render)
cat > /var/www/html/.env <<EOF
DB_HOST=${DB_HOST:-localhost}
DB_NAME=${DB_NAME:-cardsaude_db}
DB_USER=${DB_USER:-root}
DB_PASS=${DB_PASS}
DB_CHARSET=${DB_CHARSET:-utf8mb4}
DB_PORT=${DB_PORT:-3306}

APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-https://localhost}
APP_SECRET=${APP_SECRET}

ASAAS_API_URL=${ASAAS_API_URL:-https://api-sandbox.asaas.com/v3}
ASAAS_API_KEY=${ASAAS_API_KEY}
ASAAS_WEBHOOK_TOKEN=${ASAAS_WEBHOOK_TOKEN}

EMPRESA_CNPJ=${EMPRESA_CNPJ}
EMPRESA_NOME=${EMPRESA_NOME:-Concessionaria Inteligente Bem}
EMPRESA_BANCO=${EMPRESA_BANCO}
EMPRESA_AGENCIA=${EMPRESA_AGENCIA}
EMPRESA_CONTA=${EMPRESA_CONTA}

SESSION_LIFETIME=${SESSION_LIFETIME:-3600}
UPLOAD_MAX_SIZE_MB=${UPLOAD_MAX_SIZE_MB:-10}
LOG_ENABLED=${LOG_ENABLED:-true}
EOF

chown www-data:www-data /var/www/html/.env
chmod 640 /var/www/html/.env

# Docker routing is handled by FallbackResource. Remove any runtime artifact
# restored by the platform before Apache can inspect it.
find /var/www/html -name '.htaccess' -delete

exec apache2-foreground
