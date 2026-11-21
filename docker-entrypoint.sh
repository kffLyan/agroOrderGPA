#!/bin/sh
set -e

# Pastikan permission folder storage dan bootstrap/cache dapat ditulis oleh www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Buat storage symbolic link jika belum ada
if [ ! -e /var/www/html/public/storage ]; then
    php artisan storage:link --quiet || true
fi

# Jalankan migrasi database otomatis jika AUTO_MIGRATE=true
if [ "$AUTO_MIGRATE" = "true" ]; then
    echo "Menjalankan database migration..."
    php artisan migrate --force || true
fi

# Optimalkan cache Laravel jika di mode production
if [ "$APP_ENV" = "production" ]; then
    echo "Mengaktifkan cache konfigurasi dan rute production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

exec "$@"
