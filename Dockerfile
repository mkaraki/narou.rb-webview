FROM composer AS installdep

COPY composer.json /app/

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN composer install --ignore-platform-reqs

FROM php:8.2-apache

RUN pecl install apcu \
    && docker-php-ext-install opcache \
    && docker-php-ext-enable apcu

RUN <<EOF cat >> $PHP_INI_DIR/conf.d/apcu.ini
[apcu]
apc.enable=1
apc.enable_cli=1
EOF

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN a2enmod rewrite
COPY .htaccess /var/www/html/.htaccess

RUN apt-get update && \
    apt-get install -y git ca-certificates --no-install-recommends && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN git config --system --add safe.directory "*"

COPY --from=installdep /app /var/www/html

COPY __config.docker.php /var/www/html/__config.php

COPY assets /var/www/html/assets
COPY component /var/www/html/component
COPY internal /var/www/html/internal
COPY view /var/www/html/view
COPY index.php /var/www/html/index.php
COPY intra_route.php /var/www/html/intra_route.php