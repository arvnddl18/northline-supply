FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    bash \
    postgresql-dev \
    postgresql-client \
    libzip-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    bcmath \
    pcntl \
    zip \
    opcache

# Install Redis extension via PECL
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Install Composer from official image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Configure Nginx & Supervisor
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

# Copy application source code
COPY . /var/www/html

# Pre-create writable directories before composer post-autoload scripts run
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
    && composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 80

ENTRYPOINT ["/bin/sh", "/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
