FROM php:8.3-cli

# 1. Install dependensi sistem dasar yang dibutuhkan CLI/Composer
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# 2. Copy installer dan install ekstensifikasi PHP pre-compiled (Mencegah kompilasi dari source yang menghabiskan RAM)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# 3. Copy Composer dari image resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy seluruh source code
COPY . .

# 4. Buat folder storage, atur izin akses dan kepemilikan
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# 5. Install dependency Composer tanpa dev-tools
RUN composer install --no-dev --optimize-autoloader

EXPOSE 8000

# 6. Command utama untuk menjalankan aplikasi
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]