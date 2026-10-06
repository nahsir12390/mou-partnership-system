#!/usr/bin/env bash
set -euo pipefail

sed -i "s/Listen 80/Listen ${PORT:-10000}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:10000>/<VirtualHost *:${PORT:-10000}>/" /etc/apache2/sites-available/000-default.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

if [ "${DEMO_MODE:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec apache2-foreground
