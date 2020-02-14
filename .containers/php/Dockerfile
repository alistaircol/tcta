FROM php:7.4-apache
# Install some utilities
RUN apt-get update \
    && apt-get install -y zip git nano pv jq

RUN pecl install xdebug-2.8.1 \
    && docker-php-ext-enable xdebug

# add user of 1000 (usually your userid on your hosts machine)
RUN usermod -u 1000 www-data

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/tcta
