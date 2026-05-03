#!/bin/sh
set -e

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "[entrypoint] Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

exec "$@"
