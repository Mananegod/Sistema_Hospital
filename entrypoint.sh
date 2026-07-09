#!/bin/bash
set -e

if [ ! -f "artisan" ]; then
    exec apache2-foreground
fi

php artisan migrate --force

SEED_NEEDED=$(php -r "
try {
    \DB::connection()->getPdo();
    if (\Schema::hasTable('sectores') && \DB::table('sectores')->count() > 0) {
        echo '0';
    } else {
        echo '1';
    }
} catch (Exception \$e) {
    echo '1';
}
" 2>/dev/null)

if [ "$SEED_NEEDED" == "1" ]; then
    php artisan db:seed --force
fi

exec apache2-foreground