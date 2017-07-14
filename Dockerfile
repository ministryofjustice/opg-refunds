FROM registry.service.opg.digital/opg-php-fpm-1604

ADD . /app
RUN mkdir -p /srv/opg-refunds-public-front/application && \
    mkdir /srv/opg-refunds-public-front/application/releases && \
    chown -R app:app /srv/opg-refunds-public-front/application && \
    chmod -R 755 /srv/opg-refunds-public-front/application && \
    ln -s /app /srv/opg-refunds-public-front/application/current

ADD docker/confd /etc/confd

RUN cd /tmp && \
    curl -s https://getcomposer.org/installer | php && \
    cd /app && \
    gosu app php /tmp/composer.phar install --prefer-dist -o --no-suggest && \
    rm /tmp/composer.phar && \
    rm -rf docker README* LICENSE* composer.*

ENV OPG_SERVICE front
