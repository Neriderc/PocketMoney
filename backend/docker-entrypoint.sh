#!/bin/bash
echo "Running entrypoint script..."

set -e

# Ensure the DB file exists for SQLite
mkdir -p var/db
touch var/db/data.db

# Fix permissions so php-fpm (www-data) can write
chown -R www-data:www-data var/db
chmod 664 var/db/data.db

# Set CORS from domain declared in docker environment variable
if [ -n "$DOMAIN" ]; then
  export CORS_ALLOW_ORIGIN="^https?://(.+\.)?${DOMAIN//./\\.}(:[0-9]+)?$"
  echo "CORS_ALLOW_ORIGIN set to $CORS_ALLOW_ORIGIN"
fi

# Generate JWT keys if they don't exist
JWT_DIR="config/jwt"
PRIVATE_KEY="$JWT_DIR/private.pem"
PUBLIC_KEY="$JWT_DIR/public.pem"

if [ ! -f "$PRIVATE_KEY" ] || [ ! -f "$PUBLIC_KEY" ]; then
  echo "Generating JWT keys..."
  mkdir -p "$JWT_DIR"
  openssl genrsa -out "$PRIVATE_KEY" 4096
  openssl rsa -pubout -in "$PRIVATE_KEY" -out "$PUBLIC_KEY"
  chown -R www-data:www-data "$JWT_DIR"
  chmod 600 "$PRIVATE_KEY"
  chmod 644 "$PUBLIC_KEY"
else
  echo "Found JWT keys"
fi

# Run migrations
echo "Running migrations"
php bin/console doctrine:migrations:migrate --no-interaction

php bin/console cache:clear
php bin/console cache:warmup

# Optional: create default user
if [[ -n "$DEFAULT_USERNAME" && -n "$DEFAULT_PASSWORD" ]]; then
  php bin/console app:create-user "$DEFAULT_USERNAME" "$DEFAULT_PASSWORD" "ROLE_ADMIN" || true
fi

# Set time zone
if [ -n "$TZ" ]; then
  echo "Setting timezone to $TZ"

  # System time
  ln -snf "/usr/share/zoneinfo/$TZ" /etc/localtime
  echo "$TZ" > /etc/timezone

  # PHP time
  echo "date.timezone=$TZ" > /usr/local/etc/php/conf.d/docker-timezone.ini
fi

# Start supervisord (to run php-fpm and nginx)
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
