FROM caseworker-api-composer AS composer

COPY caseworker-api /app

RUN composer install --prefer-dist --no-suggest --no-interaction --no-scripts --optimize-autoloader

FROM php:7-fpm-alpine

# Postgres lib needs to remain in the container
RUN apk add --update --no-cache postgresql-libs libpng libzip openssh

# Postgres dev lib is temporary
RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS postgresql-dev libpng-dev libzip-dev \
        && pecl install xdebug \
        && pecl clear-cache \
        && docker-php-ext-install pgsql pdo_pgsql \
        && docker-php-ext-install opcache \
        && docker-php-ext-install gd \
        && docker-php-ext-install zip \
        && apk del .build-dependencies

# configure SSH
RUN echo "Port 443" >> etc/ssh/sshd_config
RUN echo "PasswordAuthentication no" >> etc/ssh/sshd_config

COPY caseworker-api /app
COPY --from=composer /app/vendor /app/vendor
COPY caseworker-api/docker/app/app-php.ini /usr/local/etc/php/conf.d/

# Default for AWS. Should be set to 1 for local development.
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

WORKDIR /app

CMD ([[ -z "${ENABLE_XDEBUG}" ]] || docker-php-ext-enable xdebug) \
    && php-fpm \
    && echo $OPG_REFUNDS_PUBLIC_KEY > ~/.ssh/authorized_keys \
    && /etc/init.d/sshd start


# && chmod +x /app/docker/app/db-migrations.sh && /app/docker/app/db-migrations.sh \
