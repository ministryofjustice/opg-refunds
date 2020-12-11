FROM composer AS composer

COPY public-front /app

RUN composer install --prefer-dist --no-suggest --no-interaction --no-scripts --optimize-autoloader --ignore-platform-reqs

FROM php:7-fpm-alpine

RUN apk add --update --no-cache postgresql-libs

RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS postgresql-dev \
        && pecl install xdebug \
        && pecl clear-cache \
        && docker-php-ext-install pgsql pdo_pgsql \
        && docker-php-ext-install opcache \
        && apk del .build-dependencies

COPY public-front /app
COPY --from=composer /app/vendor /app/vendor
COPY public-front/docker/app/app-php.ini /usr/local/etc/php/conf.d/

# Default for AWS. Should be set to 1 for local development.
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

WORKDIR /app

RUN touch /tmp/application.log

CMD ([[ -z "${ENABLE_XDEBUG}" ]] || docker-php-ext-enable xdebug) \
    && php-fpm
