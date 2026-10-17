#!/bin/sh
set -e

echo "[entrypoint] Incognito Testing — booting…";

# Make sure writable dirs exist with correct ownership for www-data.
mkdir -p /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/testing /var/www/html/storage/framework/views || true
mkdir -p /var/www/html/bootstrap/cache || true
mkdir -p /var/www/html/database || true
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# SQLite file must exist and be writable by www-data.
if [ -z "$DB_CONNECTION" ] || [ "$DB_CONNECTION" = "sqlite" ]; then
  touch /var/www/html/database/database.sqlite 2>/dev/null || true
  chown www-data:www-data /var/www/html/database/database.sqlite 2>/dev/null || true
fi

# Generate an in-memory APP_KEY if none was provided via environment.
# (Not persisted: set APP_KEY explicitly for stable sessions across restarts.)
if [ -z "$APP_KEY" ]; then
  APP_KEY=$(php artisan key:generate --show --no-interaction 2>/dev/null | tail -1)
  export APP_KEY
  echo "[entrypoint] APP_KEY not set — generated a temporary one."
fi

# Run idempotent migrations.
php artisan migrate --force

# Cache config for production speed (safe: config comes from env only).
php artisan config:cache || true
php artisan route:cache || true

exec apache2-foreground
