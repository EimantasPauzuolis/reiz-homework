FROM php:8.4-fpm-alpine

RUN apk add --no-cache git unzip libzip-dev icu-dev oniguruma-dev \
 && docker-php-ext-install pcntl bcmath zip intl

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
