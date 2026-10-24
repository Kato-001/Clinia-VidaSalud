# MediTurno · imagen para publicar en Render (o cualquier hosting con Docker)
# Contiene backend/ (Laravel) y frontend/ (vistas y assets), que el backend lee desde ../frontend
FROM php:8.3-apache

# Extensiones de PHP para PostgreSQL y módulos de Apache para las URL de Laravel
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev libzip-dev unzip \
    && docker-php-ext-install pdo_pgsql pgsql zip opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www

# Dependencias primero (se aprovecha la caché de Docker si no cambian)
COPY backend/composer.json backend/composer.lock backend/
RUN cd backend && composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY backend backend
COPY frontend frontend

RUN cd backend \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/produccion.ini /usr/local/etc/php/conf.d/produccion.ini
COPY docker/iniciar.sh /usr/local/bin/iniciar
RUN chmod +x /usr/local/bin/iniciar

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PORT=10000

EXPOSE 10000
CMD ["iniciar"]
