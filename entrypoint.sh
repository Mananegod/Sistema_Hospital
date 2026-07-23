#!/bin/bash
set -e

if [ ! -f "artisan" ]; then
    exec apache2-foreground
fi

wait_for_db() {
    echo "Esperando conexión a la base de datos..."
    until php artisan db:show > /dev/null 2>&1; do
        echo "Base de datos no disponible, reintentando en 2 segundos..."
        sleep 2
    done
    echo "Base de datos conectada correctamente."
}

wait_for_db


php artisan config:clear

php artisan migrate:install --no-interaction

echo "Aplicando migraciones pendientes..."
php artisan migrate --force --no-interaction


echo "Ejecutando seeders..."
php artisan db:seed --force --no-interaction

exec apache2-foreground