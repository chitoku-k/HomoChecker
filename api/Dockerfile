# syntax = docker/dockerfile:experimental
FROM php:8.0.6-fpm AS runtime
WORKDIR /var/www/html/api
RUN --mount=type=cache,target=/var/cache/apt \
    --mount=type=cache,target=/var/lib/apt/lists \
    apt-get -y update && \
    apt-get -y install \
        libpq5 \
        libpq-dev && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    docker-php-ext-install pdo_pgsql && \
    apt-get -y remove \
        libpq-dev && \
    echo 'display_errors = 0' > /usr/local/etc/php/conf.d/overrides.ini && \
    sed -i 's/^access\.log/; \0/' /usr/local/etc/php-fpm.d/docker.conf && \
    echo 'label ::1/128 0' > /etc/gai.conf
EXPOSE 9000

FROM runtime AS dependencies
COPY --from=composer:1 /usr/bin/composer /usr/bin/composer
RUN --mount=type=cache,target=/var/cache/apt \
    --mount=type=cache,target=/var/lib/apt/lists \
    apt-get -y install \
        git \
        unzip
COPY composer.json composer.lock /var/www/html/api/
RUN --mount=type=cache,target=/root/.composer \
    composer install --no-dev --no-progress --no-scripts --no-autoloader && \
    composer dump-autoload --no-dev --no-scripts --optimize

FROM dependencies AS dev
RUN --mount=type=cache,target=/root/.composer \
    composer install --no-progress --no-scripts && \
    composer dump-autoload --no-scripts --optimize
RUN --mount=type=cache,target=/mnt/.composer,id=/root/.composer \
    cp -r /mnt/.composer /root/

FROM runtime
COPY src /var/www/html/api/src
COPY --from=dependencies /var/www/html/api /var/www/html/api
