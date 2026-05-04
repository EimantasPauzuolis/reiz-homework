#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    echo "[entrypoint] .env missing, copying .env.example"
    cp .env.example .env
fi

exec "$@"
