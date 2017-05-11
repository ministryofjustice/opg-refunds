FROM registry.service.opg.digital/opguk/php-fpm0x644:0.0.40-dev

ADD . /app
RUN mkdir -p /srv/opg-lpa-refund-front/application && \
    mkdir /srv/opg-lpa-refund-front/application/releases && \
    chown -R app:app /srv/opg-lpa-refund-front/application && \
    chmod -R 755 /srv/opg-lpa-refund-front/application && \
    ln -s /app /srv/opg-lpa-refund-front/application/current

ADD docker/confd /etc/confd

ENV OPG_SERVICE front