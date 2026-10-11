#!/bin/sh
set -e

# Create storage and bootstrap cache directories if missing
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Fix permissions
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Copy .env from .env.example if missing
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Ensure composer dependencies are installed
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "vendor/autoload.php missing. Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Ensure application key is set
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Execute passed command
exec "$@"
