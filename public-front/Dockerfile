FROM registry.service.opg.digital/opg-php-fpm-71-ppa-1604:0.0.318

RUN apt update && apt install -y \
    php7.1-bcmath

ADD . /app
RUN mkdir -p /srv/opg-refunds-public-front/application && \
    mkdir /srv/opg-refunds-public-front/application/releases && \
    chown -R app:app /srv/opg-refunds-public-front/application && \
    chmod -R 755 /srv/opg-refunds-public-front/application && \
    ln -s /app /srv/opg-refunds-public-front/application/current

ADD docker/confd /etc/confd
COPY docker/nginx/cache.conf /etc/nginx/app.conf.d/cache.conf
COPY docker/nginx/redirect.conf /etc/nginx/app.conf.d/redirect.conf
RUN cd /tmp && \
    curl -s https://getcomposer.org/installer | php && \
    cd /app && \
    mkdir -p /app/vendor && \
    chown -R app:app /app/vendor && \
    gosu app php /tmp/composer.phar install --prefer-dist -o --no-suggest --no-interaction --no-scripts && \
    rm /tmp/composer.phar && \
    rm -rf docker README* LICENSE* composer.*

RUN mkdir -p /var/log/app && \
    touch /var/log/app/application.log && \
    chown app:app /var/log/app/application.log

ENV OPG_SERVICE public-front
