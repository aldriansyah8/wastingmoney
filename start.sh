#!/bin/bash

# Buat file serviceAccountKey.json dari Environment Variable Render
if [ ! -z "$FIREBASE_CREDENTIALS" ]; then
    echo "$FIREBASE_CREDENTIALS" > /var/www/html/serviceAccountKey.json
fi

# Buat file sqlite kosong (jika belum ada) dan jalankan migrasi database
touch /var/www/html/database/database.sqlite
php artisan migrate --force
php artisan db:seed --force

# Jalankan Apache server
apache2-foreground