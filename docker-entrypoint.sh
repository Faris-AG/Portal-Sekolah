#!/bin/bash
set -e

# Sesuaikan port Apache dengan variabel PORT dari Render
PORT_TO_USE="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT_TO_USE}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT_TO_USE}/g" /etc/apache2/sites-available/000-default.conf

# Buat symlink storage & bersihkan cache lama
php artisan storage:link || true
php artisan config:clear

# Jalankan migrasi 17 tabel otomatis
php artisan migrate --force

# Jalankan DatabaseSeeder otomatis HANYA jika tabel users masih kosong
if php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); exit(App\Models\User::count() === 0 ? 0 : 1);"; then
    php artisan db:seed --force
fi

# Optimalkan cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground