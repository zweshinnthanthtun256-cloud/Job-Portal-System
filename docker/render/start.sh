#!/bin/sh
set -eu
export PORT="${PORT:-10000}"
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf
mkdir -p storage/app/public storage/app/private storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache
php artisan migrate --force
if [ "${DEMO_MODE:-false}" = "true" ]; then php artisan db:seed --force; fi
php artisan optimize
exec /usr/bin/supervisord -c /etc/supervisord.conf
