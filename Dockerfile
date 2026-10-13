FROM php:8.3-cli

# Install dependencies sistem (termasuk libpq-dev untuk PostgreSQL)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    curl

# Install extension PHP yang dibutuhkan Laravel
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Copy Composer dari official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Buat direktori storage & bootstrap/cache serta atur izin tulis sebelum composer install
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Install dependency PHP tanpa dev-tools
RUN composer install --no-dev --optimize-autoloader

# Set hak akses kepemilikan ke www-data
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

# Jalankan migrasi, optimasi cache, dan jalankan server
CMD php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=8000