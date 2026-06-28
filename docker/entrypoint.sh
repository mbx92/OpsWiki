#!/bin/sh
set -e

cd /var/www/html

echo "[opswiki] OpsWiki deploy bootstrap starting..."

if [ -z "$APP_KEY" ]; then
    echo "[opswiki] ERROR: APP_KEY is not set. Generate with: php artisan key:generate --show"
    exit 1
fi

# Non-destructive migrations only — never migrate:fresh / migrate:refresh
echo "[opswiki] Running pending migrations (data preserved)..."
php artisan migrate --force

if [ "${RUN_BOOTSTRAP_SEEDER:-true}" = "true" ]; then
    echo "[opswiki] Running idempotent production bootstrap seeder..."
    php artisan db:seed --class=ProductionBootstrapSeeder --force
fi

php artisan storage:link --force 2>/dev/null || true

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "[opswiki] Bootstrap complete."

exec "$@"
