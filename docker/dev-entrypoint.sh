#!/bin/sh
# Runs every time a dev container (app or test) starts, then runs the
# command given in compose.yaml (php artisan serve, or php artisan test).
set -e

# Fast when nothing changed, vendor lives in a Docker volume
composer install --no-interaction

if [ ! -f .env ]; then
    cp .env.example .env
fi

if grep -q '^APP_KEY=$' .env; then
    php artisan key:generate
fi

# Sample data only the first time, after that just new migrations
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    php artisan migrate --force --seed
else
    php artisan migrate --force
fi

# Replace this script with the command from compose.yaml
exec "$@"
