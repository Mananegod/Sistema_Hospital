#!/bin/bash
set -e

if [ ! -f "artisan" ]; then
    exec apache2-foreground
fi

php artisan migrate --force


php artisan db:seed --force

exec apache2-foreground