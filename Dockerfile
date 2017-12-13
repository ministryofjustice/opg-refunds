FROM registry.service.opg.digital/opg-php-fpm-1604

RUN apt update && apt install -y \
    php-zip

ADD . /app
RUN mkdir -p /srv/opg-refunds-caseworker-api/application && \
    mkdir /srv/opg-refunds-caseworker-api/application/releases && \
    chown -R app:app /srv/opg-refunds-caseworker-api/application && \
    chmod -R 755 /srv/opg-refunds-caseworker-api/application && \
    ln -s /app /srv/opg-refunds-caseworker-api/application/current

ADD docker/confd /etc/confd
ADD docker/my_init.d /etc/my_init.d
RUN chmod a+x /etc/my_init.d/*

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

ENV OPG_SERVICE caseworker-api
