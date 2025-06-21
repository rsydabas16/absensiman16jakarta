#!/usr/bin/env bash

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate APP_KEY dan cache konfigurasi
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan migrasi jika perlu
php artisan migrate --force
