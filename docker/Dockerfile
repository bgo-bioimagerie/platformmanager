#FROM php:7.3-apache
FROM php:8-apache

LABEL author="GenOuest <support@genouest.org>" \
      description="Image for platform manager processes" \
      org.irisa.genouest.vendor="2021 IRISA" \
      homepage="https://github.com/bgo-bioimagerie/platformmanager"

# ENV TINI_VERSION v0.9.0
# RUN set -x \
#     && curl -fSL "https://github.com/krallin/tini/releases/download/$TINI_VERSION/tini" -o /usr/local/bin/tini \
#     && curl -fSL "https://github.com/krallin/tini/releases/download/$TINI_VERSION/tini.asc" -o /usr/local/bin/tini.asc \
#     && export GNUPGHOME="$(mktemp -d)" \
#     && gpg --keyserver hkp://p80.pool.sks-keyservers.net:80 --recv-keys 6380DC428747F6C393FEACA59A84159D7001A4E5 \
#     || gpg --keyserver pgp.mit.edu --recv-keys 6380DC428747F6C393FEACA59A84159D7001A4E5 \
#     && gpg --batch --verify /usr/local/bin/tini.asc /usr/local/bin/tini \
#     && rm -r "$GNUPGHOME" /usr/local/bin/tini.asc \
#     && chmod +x /usr/local/bin/tini

# ENTRYPOINT ["/usr/local/bin/tini", "--"]

WORKDIR /var/www

VOLUME ["/var/www/platformmanager/data"]

# Install packages and PHP-extensions
RUN apt-get -q update && \
    DEBIAN_FRONTEND=noninteractive apt-get -yq --no-install-recommends install wget nano vim at git unzip \
    libfreetype6 libjpeg62 libpng16-16 libx11-6 libxpm4 zlib1g-dev libzip4 libldap2-dev libc-client2007e libkrb5-3 && \
    BUILD_DEPS="libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng-dev libxpm-dev zlib1g-dev libzip-dev libc-client2007e-dev libkrb5-dev" && \
    DEBIAN_FRONTEND=noninteractive apt-get -yq --no-install-recommends install $BUILD_DEPS && \
    pecl install redis && docker-php-ext-enable redis && \
    docker-php-ext-configure imap --with-imap-ssl --with-kerberos && \
    docker-php-ext-configure gd \
    --with-jpeg \
    --with-xpm --with-freetype && \
    docker-php-ext-install gd pdo pdo_mysql mysqli zip ldap imap sockets && \
    a2enmod rewrite && \
    a2enmod headers && \
    a2enmod proxy_http && \
    rm -rf /var/lib/apt/lists/* && \
    touch /var/log/php_errors.log && \
    chown www-data /var/log/php_errors.log && \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false $BUILD_DEPS && \
    rm -rf /var/lib/apt/lists/*

RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --snapshot \
    && rm -f /tmp/composer-setup.*

ADD php_logs.ini /usr/local/etc/php/conf.d/php_logs.ini
ADD php_timezone.ini /usr/local/etc/php/conf.d/tz.ini
ADD apache2/platformmanager.conf /etc/apache2/conf-enabled/platformmanager.conf

RUN mkdir -p /var/www/platformmanager \
    && chown -R www-data /var/www/platformmanager

ARG BRANCH=master

# install Platform-Manager sources
RUN git clone https://github.com/bgo-bioimagerie/platformmanager.git /tmp/platformmanager_git \
  && cd /tmp/platformmanager_git \
  && git checkout $BRANCH \
  && cp -r /tmp/platformmanager_git/data /opt \
  && cp -r /tmp/platformmanager_git/* /var/www/platformmanager \
  && cp /tmp/platformmanager_git/.htaccess /var/www/platformmanager \
  && cp -r /tmp/platformmanager_git/.git /var/www/platformmanager \
  && rm -rf /tmp/platformmanager_git \
  && cd /var/www/ \
  && mkdir platformmanager/tmp \
  && chown -R www-data: platformmanager \
  && rm -rf html \
  && ln -s platformmanager html \
  && mkdir -p /etc/platformmanager \
  && cp platformmanager/Config/conf.ini.sample /etc/platformmanager/

RUN cd /var/www/platformmanager && composer install --ignore-platform-reqs

ENV MYSQL_HOST="mysql" \
    MYSQL_DBNAME="platformmanager" \
    MYSQL_USER="username" \
    MYSQL_PASS="password"

ADD entrypoint.sh /
ADD pfmextra-entrypoint.sh /
ADD setup.sh /

RUN chmod a+x /entrypoint.sh
RUN chmod a+x /pfmextra-entrypoint.sh
RUN chmod a+x /setup.sh

ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.6.0/wait /wait
RUN chmod +x /wait

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

CMD ["/entrypoint.sh"]
