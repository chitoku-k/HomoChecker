FROM php:7.4.11-fpm-alpine
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

RUN echo 'display_errors = 0' > /usr/local/etc/php/conf.d/overrides.ini
RUN sed -i 's/^access\.log/; \0/' /usr/local/etc/php-fpm.d/docker.conf

EXPOSE 9000
