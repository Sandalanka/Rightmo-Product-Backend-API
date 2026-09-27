FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# PHP extensions: MySQL, intl, zip, bcmath, opcache, pcntl (pdo_sqlite is already built in for tests)
RUN apk add --no-cache icu-libs libzip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev linux-headers \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath intl zip opcache pcntl \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini "$PHP_INI_DIR/conf.d/99-app.ini"
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Composer cache must be writable by the non-root user the container runs as
ENV COMPOSER_HOME=/tmp/composer

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
