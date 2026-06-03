
set -e  

echo "Iniciando entrada del contenedor..."


if [ ! -f "artisan" ]; then
    echo "No se encuentra artisan. Omitiendo migraciones y seeders."
    exec apache2-foreground
fi


echo "Ejecutando migraciones pendientes..."
php artisan migrate --force
echo "Migraciones completadas."


echo "🔍 Verificando si los seeders ya fueron ejecutados..."


SEED_NEEDED=$(php -r "
try {
    \\DB::connection()->getPdo();
    if (\\Schema::hasTable('sectores') && \\DB::table('sectores')->count() > 0) {
        echo '0';
    } else {
        echo '1';
    }
} catch (Exception \$e) {
    echo '1';
}
" 2>/dev/null)

if [ "$SEED_NEEDED" == "1" ]; then
    echo "Tabla 'sectores' vacía o inexistente. Ejecutando seeders generales..."
    php artisan db:seed --force
    echo "Seeders completados."
else
    echo "Los seeders ya se ejecutaron anteriormente (tabla 'sectores' con datos). Omitiendo."
fi


exec apache2-foreground