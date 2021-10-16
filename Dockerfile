FROM composer AS installdep

COPY * /app/

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN composer install

FROM php:8.0-apache

COPY --from=installdep /app /var/www/html
COPY --from=installdep /app/__config.docker.php /var/www/html/__config.php