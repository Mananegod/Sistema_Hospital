
set -e

if [ ! -f "artisan" ]; then
    exec apache2-foreground
fi


echo "Running migrations..."
php artisan migrate:fresh

echo "Running seeders..."
php artisan db:seed --force

exec apache2-foreground