FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    libzip-dev unzip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Garante que mod_rewrite está ativo via arquivo de módulo
RUN echo "LoadModule rewrite_module /usr/lib/apache2/modules/mod_rewrite.so" \
    >> /etc/apache2/mods-available/rewrite.load 2>/dev/null || true

COPY apache.conf /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/

RUN mkdir -p /var/www/html/storage/uploads /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
