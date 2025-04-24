#!/bin/bash
set -e

# Ensure the DB file exists for SQLite
mkdir -p var/db
touch var/db/data.db

# Fix permissions so php-fpm (www-data) can write
chown -R www-data:www-data var/db
chmod 664 var/db/data.db

# Run migrations 
php bin/console doctrine:migrations:migrate --no-interaction

# Optional: create default user
if [[ -n "$DEFAULT_USERNAME" && -n "$DEFAULT_PASSWORD" ]]; then
  php bin/console app:create-user "$DEFAULT_USERNAME" "$DEFAULT_PASSWORD" "ROLE_ADMIN" || true
fi

# Start supervisord (to run php-fpm and nginx)
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
