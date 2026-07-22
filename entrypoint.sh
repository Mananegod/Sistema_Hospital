#!/bin/bash
set -e


if [ ! -f "artisan" ]; then
    exec apache2-foreground
fi


echo "Limpiando la base de datos"
php artisan db:wipe

php artisan config:clear


echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "Running seeders..."
php artisan db:seed --force --no-interaction

exec apache2-foreground