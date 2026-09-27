#!/bin/sh
set -e

# Only prepare the app for the main PHP-FPM process (not for `docker compose exec app ...` style one-off commands)
if [ "$1" = "php-fpm" ]; then
    if [ ! -f vendor/autoload.php ]; then
        composer install --no-interaction --prefer-dist
    fi

    if [ ! -f .env ]; then
        cp .env.example .env
    fi

    if ! grep -q '^APP_KEY=base64:' .env; then
        php artisan key:generate --force
    fi

    # Relative link so public/storage works both in the container and on the host.
    # -e is false for a broken link (e.g. an absolute host path from `artisan storage:link`), so -f replaces it.
    if [ ! -e public/storage ]; then
        ln -sfn ../storage/app/public public/storage
    fi

    if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
        php artisan migrate --force
    fi

    php artisan l5-swagger:generate || true
fi

exec "$@"
