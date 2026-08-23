FROM php:8.4-cli

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

# Copy the application source. The Docker ignore file deliberately excludes
# Vite's development marker and previously-built assets.
COPY . .

# Build production assets. Removing `public/hot` ensures Laravel's `@vite`
# directive uses the generated manifest instead of attempting to contact a
# development Vite server, which is not available on Render.
RUN rm -f public/hot \
    && npm ci \
    && npm run build

# Run Laravel post-install commands
RUN php artisan package:discover --ansi \
    # && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
