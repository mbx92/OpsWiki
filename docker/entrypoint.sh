#!/bin/sh
set -e

cd /var/www/html

echo "[opswiki] OpsWiki deploy bootstrap starting..."

if [ -z "$APP_KEY" ]; then
    echo "[opswiki] ERROR: APP_KEY is not set. Generate with: php artisan key:generate --show"
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
    echo "[opswiki] ERROR: Cannot connect to database. Check DB_HOST and credentials in Coolify."
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
