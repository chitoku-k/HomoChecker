# syntax = docker/dockerfile:experimental
FROM php:8.0.10-fpm AS runtime
WORKDIR /var/www/html/api
RUN --mount=type=cache,target=/var/cache/apt \
    --mount=type=cache,target=/var/lib/apt/lists \
    apt-get -y update && \
    apt-get -y install \
        libpq5 \
        libpq-dev && \
    pecl install \
        apcu \
        redis && \
    docker-php-ext-enable \
        apcu \
        redis && \
    docker-php-ext-install \
        opcache \
        pdo_pgsql && \
    apt-get -y remove \
        $PHPIZE_DEPS \
        libargon2-dev \
        libc6-dev \
        libcurl4-openssl-dev \
        libedit-dev \
        libonig-dev \
        libpq-dev \
        libsodium-dev \
        libsqlite3-dev \
        libssl-dev \
        libxml2-dev \
        zlib1g-dev && \
    apt-get -y autoremove && \
    rm -rf /tmp/* && \
    echo 'display_errors = 0' > /usr/local/etc/php/conf.d/overrides.ini && \
    sed -i 's/^access\.log/; \0/' /usr/local/etc/php-fpm.d/docker.conf && \
    echo 'label ::1/128 0' > /etc/gai.conf

FROM runtime AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN --mount=type=cache,target=/var/cache/apt \
    --mount=type=cache,target=/var/lib/apt/lists \
    apt-get -y update && \
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

FROM scratch
COPY src /var/www/html/api/src
COPY --from=runtime /etc/gai.conf /etc/
COPY --from=runtime /etc/group /etc/
COPY --from=runtime /etc/passwd /etc/
COPY --from=runtime /etc/ssl/certs/ /etc/ssl/certs/
COPY --from=runtime /lib/ /lib/
COPY --from=runtime /lib64/ /lib64/
COPY --from=runtime /tmp/ /tmp/
COPY --from=runtime /usr/lib/ /usr/lib/
COPY --from=runtime /usr/local/etc/ /usr/local/etc/
COPY --from=runtime /usr/local/lib/ /usr/local/lib/
COPY --from=runtime /usr/local/sbin/php-fpm /usr/local/sbin/
COPY --from=runtime /usr/share/ca-certificates/ /usr/share/ca-certificates/
COPY --from=dependencies /var/www/html/api /var/www/html/api
STOPSIGNAL SIGQUIT
EXPOSE 9000
ENTRYPOINT ["/usr/local/sbin/php-fpm"]
