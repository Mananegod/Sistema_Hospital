
set -e  

echo "🚀 Iniciando entrada del contenedor..."

if [ -f "artisan" ]; then
    echo "Ejecutando migraciones pendientes..."
    php artisan migrate --force
    echo "Migraciones completadas."
else
    echo "No se encuentra artisan, omitiendo migraciones."
fi


exec apache2-foreground