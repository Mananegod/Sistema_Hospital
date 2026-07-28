
set -e

if [ ! -f "artisan" ]; then
    exec apache2-foreground
fi

# wait_for_db() {
#     echo "Esperando conexión a la base de datos..."
#     until php artisan db:show > /dev/null 2>&1; do
#         echo "Base de datos no disponible, reintentando en 2 segundos..."
#         sleep 2
#     done
#     echo "Base de datos conectada correctamente."
# }

# wait_for_db

php artisan config:clear


if [ "$FORCE_FRESH_MIGRATE" = "true" ]; then
    echo "FORCE_FRESH_MIGRATE activado: eliminando todas las tablas y aplicando migraciones desde cero..."
    php artisan migrate:fresh --seed --force --no-interaction
    echo "Base de datos regenerada exitosamente."
else
   
    php artisan migrate:install --no-interaction
    echo "Aplicando migraciones pendientes..."
    php artisan migrate --force --no-interaction
    echo "Ejecutando seeders..."
    php artisan db:seed --force --no-interaction
fi

exec apache2-foreground