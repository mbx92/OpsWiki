#!/bin/sh
set -e

cd /var/www/html

echo "[opswiki] OpsWiki deploy bootstrap starting..."

if [ -z "$APP_KEY" ]; then
    echo "[opswiki] ERROR: APP_KEY is not set. Generate with: php artisan key:generate --show"
    exit 1
fi

if ! php -r "require 'vendor/autoload.php';" 2>/dev/null; then
    echo "[opswiki] ERROR: PHP does not meet Composer requirements."
    php -v
    php -r "require 'vendor/autoload.php';" 2>&1 || true
    exit 1
fi

# Non-destructive migrations only — never migrate:fresh / migrate:refresh
echo "[opswiki] Waiting for database (${DB_HOST:-unknown})..."
TRIES=30
while [ "$TRIES" -gt 0 ]; do
    if php artisan migrate:status > /dev/null 2>&1; then
        break
    fi
    TRIES=$((TRIES - 1))
    sleep 2
done

if ! php artisan migrate:status > /dev/null 2>&1; then
    echo "[opswiki] ERROR: Cannot connect to database."
    echo "[opswiki]   DB_HOST=${DB_HOST:-<empty>} DB_PORT=${DB_PORT:-5432} DB_DATABASE=${DB_DATABASE:-<empty>} DB_USERNAME=${DB_USERNAME:-<empty>}"
    echo "[opswiki]   Pastikan di Coolify: Advanced → Connect to Predefined Network = ON"
    echo "[opswiki]   DB_HOST = hostname internal Postgres (bukan URL publik / bukan localhost)"
    php artisan db:show 2>&1 || true
    exit 1
fi

echo "[opswiki] Running pending migrations (data preserved)..."
php artisan migrate --force

php artisan package:discover --ansi 2>/dev/null || true

echo "[opswiki] Bootstrap seeder (first deploy only)..."
php artisan opswiki:bootstrap

php artisan storage:link --force 2>/dev/null || true

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "[opswiki] Bootstrap complete."

exec "$@"
