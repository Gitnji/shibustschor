# Step 1: Use the official high-performance FrankenPHP production image
FROM dunglas/frankenphp:1.3-php8.4

WORKDIR /var/www/html

# Install system dependencies + Node.js
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    nodejs \
    npm \
    && docker-php-ext-install \
    pdo_pgsql \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer files and install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

# Copy the application source code
COPY . .

# Build production assets smoothly
RUN rm -f public/hot \
    && npm ci \
    && npm run build

# Run Laravel post-install caching commands
RUN php artisan package:discover --ansi \
    && php artisan route:cache \
    && php artisan view:cache

# Set directory permissions for the FrankenPHP server user
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 10000

# Run migrations and start the high-performance concurrent worker server
CMD php artisan migrate --force && frankenphp php-server --listen :${PORT:-10000} --public-dir public
