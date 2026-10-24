#!/bin/sh
# Arranque del contenedor: Apache escucha en el puerto que asigna Render ($PORT)
# y Laravel prepara sus cachés con las variables de entorno del hosting.
set -e

PUERTO="${PORT:-10000}"
sed -i "s/^Listen .*/Listen ${PUERTO}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PUERTO}>/" /etc/apache2/sites-available/000-default.conf

cd /var/www/backend
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
