#!/usr/bin/env bash
set -euo pipefail

sed -i "s/Listen 80/Listen ${PORT:-10000}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:10000>/<VirtualHost *:${PORT:-10000}>/" /etc/apache2/sites-available/000-default.conf

export APP_KEY="${APP_KEY:-$(php artisan key:generate --show --no-ansi)}"

mkdir -p "$(dirname "${DB_DATABASE:-/var/www/html/database/database.sqlite}")"
touch "${DB_DATABASE:-/var/www/html/database/database.sqlite}"
chown www-data:www-data "${DB_DATABASE:-/var/www/html/database/database.sqlite}"

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --force

exec apache2-foreground
