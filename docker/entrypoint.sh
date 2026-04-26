#!/bin/sh
set -e

cd /var/www/html

export PORT="${PORT:-80}"

echo "==> Rendering Nginx config for port ${PORT}..."
envsubst '${PORT}' < /var/www/html/docker/nginx/default.conf > /etc/nginx/http.d/default.conf

echo "==> Linking storage..."
php artisan storage:link --force 2>/dev/null || true

echo "==> Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
