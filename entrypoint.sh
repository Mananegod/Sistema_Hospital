#!/bin/bash
set -e

if [ ! -f "artisan" ]; then
    exec apache2-foreground
fi

echo "==> Iniciando despliegue del Sistema Hospital..."

# ------------------------------------------------------------------
# 1) Limpiar cachés de Laravel (por si el build dejó algo viejo)
# ------------------------------------------------------------------
php artisan config:clear  2>/dev/null || true
php artisan cache:clear   2>/dev/null || true
php artisan view:clear    2>/dev/null || true
php artisan route:clear   2>/dev/null || true

# ------------------------------------------------------------------
# 2) Reconstruir el manifest de paquetes (necesario porque el Dockerfile
#    hace composer install con --no-scripts). Sin esto, Laravel 10/11
#    puede fallar con "Target class [...] does not exist".
# ------------------------------------------------------------------
echo "==> Descubriendo paquetes instalados..."
php artisan package:discover --ansi 2>/dev/null || echo "   (warning: package:discover falló, continuando...)"

# ------------------------------------------------------------------
# 3) Helpers para el marcador de "fresh migrate ya ejecutado".
#    No usamos tinker porque en producción (--no-dev) no está instalado.
# ------------------------------------------------------------------
check_fresh_marker() {
    php -r '
        require "/var/www/html/vendor/autoload.php";
        try {
            $app = require "/var/www/html/bootstrap/app.php";
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            if (!\Illuminate\Support\Facades\Schema::hasTable("_deploy_markers")) {
                echo "no"; exit;
            }
            echo \Illuminate\Support\Facades\DB::table("_deploy_markers")
                    ->where("name", "fresh_migrate_2024")->exists() ? "yes" : "no";
        } catch (\Throwable $e) {
            echo "no";
        }
    ' 2>/dev/null | tr -d '\r\n[:space:]'
}

create_fresh_marker() {
    php -r '
        require "/var/www/html/vendor/autoload.php";
        $app = require "/var/www/html/bootstrap/app.php";
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        if (!\Illuminate\Support\Facades\Schema::hasTable("_deploy_markers")) {
            \Illuminate\Support\Facades\Schema::create("_deploy_markers", function ($t) {
                $t->string("name")->primary();
                $t->timestamp("created_at")->nullable();
            });
        }
        \Illuminate\Support\Facades\DB::table("_deploy_markers")->updateOrInsert(
            ["name" => "fresh_migrate_2024"],
            ["created_at" => now()]
        );
    ' 2>&1 || echo "   (warning: no se pudo crear el marcador de deploy)"
}

# ------------------------------------------------------------------
# 4) Lógica principal de migración
# ------------------------------------------------------------------
if [ "$FORCE_FRESH_MIGRATE" = "true" ]; then
    MARKER=$(check_fresh_marker)

    if [ "$MARKER" = "yes" ]; then
        echo "==> FORCE_FRESH_MIGRATE=true pero el marcador ya existe."
        echo "    Aplicando solo migraciones pendientes (NO se borran datos)."
        php artisan migrate --force --no-interaction
    else
        echo "==> FORCE_FRESH_MIGRATE=true y sin marcador previo."
        echo "    Ejecutando migrate:fresh + seed (SE BORRA TODA LA DATA)..."
        php artisan migrate:fresh --seed --force --no-interaction
        create_fresh_marker
        echo "==> Base de datos regenerada y marcador creado."
    fi
else
    echo "==> Aplicando migraciones pendientes..."
    php artisan migrate:install --force --no-interaction 2>/dev/null || true
    php artisan migrate --force --no-interaction

    # Los seeders SOLO se ejecutan si explícitamente lo pides,
    # porque en cada reinicio de Render Free duplicarían data
    # si no son 100 % idempotentes (updateOrCreate/firstOrCreate).
    if [ "$RUN_SEEDERS" = "true" ]; then
        echo "==> Ejecutando seeders (RUN_SEEDERS=true)..."
        php artisan db:seed --force --no-interaction
    fi
fi

# ------------------------------------------------------------------
# 5) Symlink público de storage (logos, membreses, etc.)
# ------------------------------------------------------------------
php artisan storage:link 2>/dev/null || true

# ------------------------------------------------------------------
# 6) Permisos finales: cualquier archivo creado por artisan (root)
#    durante la migración debe quedar escribible por Apache (www-data).
# ------------------------------------------------------------------
chown -R www-data:www-data /var/www/html/storage 2>/dev/null || true
chown -R www-data:www-data /var/www/html/bootstrap/cache 2>/dev/null || true

echo "==> Despliegue completado. Arrancando Apache..."
exec apache2-foreground