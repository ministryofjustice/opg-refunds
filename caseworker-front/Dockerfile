FROM registry.service.opg.digital/opg-php-fpm-71-ppa-1604

RUN apt update && apt install -y \
    php7.1-bcmath

ADD . /app
RUN mkdir -p /srv/opg-refunds-caseworker-front/application && \
    mkdir /srv/opg-refunds-caseworker-front/application/releases && \
    chown -R app:app /srv/opg-refunds-caseworker-front/application && \
    chmod -R 755 /srv/opg-refunds-caseworker-front/application && \
    ln -s /app /srv/opg-refunds-caseworker-front/application/current

ADD docker/confd /etc/confd

RUN cd /tmp && \
    curl -s https://getcomposer.org/installer | php && \
    cd /app && \
    mkdir -p /app/vendor && \
    chown -R app:app /app/vendor && \
    gosu app php /tmp/composer.phar install --prefer-dist -o --no-suggest && \
    rm /tmp/composer.phar && \
    rm -rf docker README* LICENSE* composer.*

RUN mkdir -p /var/log/app && \
    touch /var/log/app/application.log && \
    chown app:app /var/log/app/application.log

ENV OPG_SERVICE caseworker-front
