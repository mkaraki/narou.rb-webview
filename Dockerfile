FROM composer AS installdep

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN git clone https://github.com/mkaraki/narou.rb-webview.git /app

WORKDIR /app

RUN composer install

FROM php:8.0-apache

COPY --from=installdep /app /var/www/html
COPY --from=installdep /app/__config.docker.php /var/www/html/__config.php