FROM composer AS installdep

COPY composer.json /app/

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN composer install

FROM php:8.2-apache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=installdep /app /var/www/html
COPY .htaccess /var/www/html/.htaccess

COPY __config.docker.php /var/www/html/__config.php

COPY assets /var/www/html/assets
COPY component /var/www/html/component
COPY internal /var/www/html/internal
COPY view /var/www/html/view
COPY index.php /var/www/html/index.php