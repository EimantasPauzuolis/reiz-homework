# syntax=docker/dockerfile:1.7

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        git \
        unzip \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        shadow \
    && docker-php-ext-install pcntl bcmath zip intl \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

RUN if getent passwd www-data >/dev/null; then deluser www-data; fi \
    && if getent group www-data >/dev/null; then delgroup www-data; fi \
    && addgroup -g 1000 app \
    && adduser -u 1000 -G app -s /bin/sh -D app \
    && mkdir -p /var/www/html /home/app/.composer \
    && chown -R app:app /var/www/html /home/app

ENV COMPOSER_ALLOW_SUPERUSER=0 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_HOME=/home/app/.composer \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=1

WORKDIR /var/www/html

COPY --chown=app:app docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER app

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
