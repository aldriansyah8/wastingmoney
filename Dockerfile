FROM php:8.2-apache

# Install dependencies sistem & ekstensi PHP untuk SQLite
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite zip

# Aktifkan mod_rewrite Apache untuk URL Laravel
RUN a2enmod rewrite

# Ubah root document Apache ke folder /public Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Salin seluruh kode proyek ke dalam container
COPY . /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Berikan hak akses untuk folder storage & cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Salin script jalan utama
COPY start.sh /usr/local/bin/start
RUN chmod +x /usr/local/bin/start

CMD ["/usr/local/bin/start"]