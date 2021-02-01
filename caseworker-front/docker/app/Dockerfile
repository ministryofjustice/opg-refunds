FROM composer:1.10.19 AS composer

COPY caseworker-front /app

RUN composer install --prefer-dist --no-suggest --no-interaction --no-scripts --optimize-autoloader

FROM php:7-fpm-alpine

RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
        && pecl install xdebug \
        && pecl clear-cache \
        && docker-php-ext-install opcache \
        && apk del .build-dependencies

COPY caseworker-front /app
COPY --from=composer /app/vendor /app/vendor
COPY caseworker-front/docker/app/app-php.ini /usr/local/etc/php/conf.d/

# Default for AWS. Should be set to 1 for local development.
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

WORKDIR /app

RUN touch /tmp/application.log

CMD ([[ -z "${ENABLE_XDEBUG}" ]] || docker-php-ext-enable xdebug) \
    && php-fpm
