FROM caseworker-api-composer AS composer

COPY caseworker-api/docker/seeding /app

# RUN composer install --prefer-dist --no-suggest --no-interaction --no-scripts --optimize-autoloader

FROM php:7-cli-alpine

# Postgres needs to remain in the container
RUN apk add --update --no-cache postgresql-libs postgresql-client

# Postgres dev lib is temporary
RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS postgresql-dev \
  && pecl install xdebug \
  && pecl clear-cache \
  && docker-php-ext-install pgsql pdo_pgsql \
  && docker-php-ext-install opcache \
  && docker-php-ext-install pcntl \
  && apk del .build-dependencies

ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit

COPY caseworker-api/docker/seeding /app

# Default for AWS. Should be set to 1 for local development.
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0"

WORKDIR /app

CMD chmod +x *.sh \
  && ./db-seeding.sh