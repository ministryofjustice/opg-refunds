FROM composer

RUN apk add --update --no-cache libpng

RUN apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS libpng-dev \
        && docker-php-ext-install gd \
        && apk del .build-dependencies
