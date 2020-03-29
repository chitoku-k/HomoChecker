FROM php:7.4.4-fpm-alpine
WORKDIR /var/www/html/api
COPY . /var/www/html/api

RUN apk add --no-cache --virtual build-dependencies \
        composer \
        autoconf \
        build-base && \
    composer install --no-dev && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    docker-php-ext-install pdo_mysql && \
    apk del build-dependencies

EXPOSE 9000
