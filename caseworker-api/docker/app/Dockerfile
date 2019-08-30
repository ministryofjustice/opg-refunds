FROM caseworker-api-composer AS composer

COPY caseworker-api /app

RUN composer install --prefer-dist --no-suggest --no-interaction --no-scripts --optimize-autoloader

FROM php:7-fpm-alpine

# Postgres lib needs to remain in the container
RUN apk add --update --no-cache postgresql-libs libpng

# Postgres dev lib is temporary
RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS postgresql-dev libpng-dev \
        && pecl install xdebug \
        && pecl clear-cache \
        && docker-php-ext-install pgsql pdo_pgsql \
        && docker-php-ext-install opcache \
        && docker-php-ext-install gd \
        && apk del .build-dependencies

COPY caseworker-api /app
COPY --from=composer /app/vendor /app/vendor
COPY caseworker-api/docker/app/app-php.ini /usr/local/etc/php/conf.d/

# Default for AWS. Should be set to 1 for local development.
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

WORKDIR /app

CMD ([[ -z "${ENABLE_XDEBUG}" ]] || docker-php-ext-enable xdebug) \
    && php-fpm

# && chmod +x /app/docker/app/db-migrations.sh && /app/docker/app/db-migrations.sh \
