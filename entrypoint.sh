#!/bin/bash
set -e


if [ ! -f "artisan" ]; then
    exec apache2-foreground
fi


# echo "Limpiando la base de datos" /* dejo esta vaina comentada por si se necesita otra vez B)  */
# php artisan db:wipe --no-interaction

php artisan config:clear --force --no-interaction



echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "Running seeders..."
php artisan db:seed --force --no-interaction

exec apache2-foreground