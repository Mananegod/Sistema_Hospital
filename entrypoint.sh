#!/bin/bash
set -e


if [ ! -f "artisan" ]; then
    exec apache2-foreground
fi


php artisan config:clear --force --no-interaction


echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "Running seeders..."
php artisan db:seed --force --no-interaction

exec apache2-foreground