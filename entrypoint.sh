#!/bin/bash
set -e

if [ ! -f "artisan" ]; then
    exec apache2-foreground
fi

# Limpiar caché de configuración (sin --force)
php artisan config:clear

echo "Revisando Migraciones...\n"
php artisan migrate:status
echo "\n Migraciones revisadas \n"

echo "Running migrations..."
php artisan migrate:fresh --force --no-interaction

echo "Running seeders..."
php artisan db:seed --force --no-interaction

exec apache2-foreground