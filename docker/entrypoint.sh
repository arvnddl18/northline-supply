#!/bin/sh
set -e

# Create storage and bootstrap cache directories if missing
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache 2>/dev/null || true

# Fix permissions
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Copy .env from .env.example if missing
if [ ! -f /var/www/html/.env ] && [ -f /var/www/html/.env.example ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Ensure composer dependencies exist
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "vendor/autoload.php missing. Installing composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

# Execute passed command
exec "$@"

