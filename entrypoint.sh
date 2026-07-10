#!/bin/bash
set -e

if [ ! -f "artisan" ]; then
    exec apache2-foreground
fi

echo "Running migrations..."
php artisan migrate --force

echo "Running seeders..."
php artisan db:seed --force

exec apache2-foreground