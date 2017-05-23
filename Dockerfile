FROM registry.service.opg.digital/opg-php-fpm-1604

ADD . /app
RUN mkdir -p /srv/opg-lpa-refund-front/application && \
    mkdir /srv/opg-lpa-refund-front/application/releases && \
    chown -R app:app /srv/opg-lpa-refund-front/application && \
    chmod -R 755 /srv/opg-lpa-refund-front/application && \
    ln -s /app /srv/opg-lpa-refund-front/application/current

ADD docker/confd /etc/confd

RUN cd /tmp && \
    curl -s https://getcomposer.org/installer | php && \
    cd /app && \
    gosu app php /tmp/composer.phar install --prefer-dist -o --no-suggest && \
    rm /tmp/composer.phar && \
    rm -rf docker README* LICENSE* composer.*

ENV OPG_SERVICE front
