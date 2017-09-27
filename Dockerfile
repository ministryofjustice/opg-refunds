FROM registry.service.opg.digital/opg-php-fpm-1604

ADD . /app
RUN mkdir -p /srv/opg-refunds-public-front/application && \
    mkdir /srv/opg-refunds-public-front/application/releases && \
    chown -R app:app /srv/opg-refunds-public-front/application && \
    chmod -R 755 /srv/opg-refunds-public-front/application && \
    ln -s /app /srv/opg-refunds-public-front/application/current

ADD docker/confd /etc/confd

COPY docker/nginx/cache.conf /etc/nginx/app.conf.d/cache.conf

# add SSH key
ADD keys/composer_deployment /tmp/id_rsa

RUN chmod 600 /tmp/id_rsa && \
    eval $(ssh-agent) && \
    echo -e "StrictHostKeyChecking no" >> /etc/ssh/ssh_config && \
    ssh-add /tmp/id_rsa && \
    cd /tmp && \
    curl -s https://getcomposer.org/installer | php && \
    cd /app && \
    mkdir -p /app/vendor && \
    chown -R app:app /app/vendor && \
    gosu app php /tmp/composer.phar install --prefer-dist -o --no-suggest && \
    rm /tmp/composer.phar && \
    eval $(ssh-agent -k) && \
    rm -rf docker README* LICENSE* composer.* keys /tmp/id_rsa

ENV OPG_SERVICE public-front
