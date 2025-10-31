# syntax = docker/dockerfile:1
FROM php:8.4.14-fpm-trixie AS runtime
WORKDIR /var/www/html/api
RUN --mount=type=cache,id=api:/var/cache/apt,target=/var/cache/apt \
    --mount=type=cache,id=api:/var/lib/apt/lists,target=/var/lib/apt/lists \
    apt-get -y update && \
    apt-get -y install \
        libicu-dev \
        libicu76 \
        libpq-dev \
        libpq5 && \
    pecl install \
        apcu && \
    docker-php-ext-enable \
        apcu && \
    docker-php-ext-install \
        intl \
        opcache \
        pdo_pgsql && \
    apt-get -y remove \
        $PHPIZE_DEPS \
        libargon2-dev \
        libc6-dev \
        libcrypt-dev \
        libcurl4-openssl-dev \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        libsodium-dev \
        libsqlite3-dev \
        libssl-dev \
        libxml2-dev \
        zlib1g-dev && \
    apt-get -y autoremove && \
    echo 'display_errors = 0' > /usr/local/etc/php/conf.d/overrides.ini && \
    sed -i '/^access\.log/d' /usr/local/etc/php-fpm.d/docker.conf

FROM runtime AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN --mount=type=cache,id=api:/var/cache/apt,target=/var/cache/apt \
    --mount=type=cache,id=api:/var/lib/apt/lists,target=/var/lib/apt/lists \
    apt-get -y update && \
    apt-get -y install \
        git \
        unzip
COPY composer.json composer.lock /var/www/html/api/
RUN --mount=type=cache,id=api:/root/.composer,target=/root/.composer \
    composer install --no-dev --no-progress --no-scripts --no-autoloader && \
    composer dump-autoload --no-dev --no-scripts --optimize

FROM dependencies AS dev
RUN --mount=type=cache,id=api:/var/cache/apt,target=/var/cache/apt \
    --mount=type=cache,id=api:/var/lib/apt/lists,target=/var/lib/apt/lists \
    apt-get -y update && \
    apt-get -y install \
        autoconf \
        build-essential
RUN --mount=type=cache,id=api:/root/.composer,target=/root/.composer \
    git config --system --add safe.directory /var/www/html && \
    composer install --no-progress --no-scripts && \
    composer dump-autoload --no-scripts --optimize
RUN --mount=type=cache,id=api:/root/.composer,target=/mnt/.composer \
    cp -r /mnt/.composer /root/
RUN pecl install pcov && \
    docker-php-ext-enable pcov

FROM scratch
COPY src /var/www/html/api/src
COPY --from=runtime /etc/group /etc/
COPY --from=runtime /etc/passwd /etc/
COPY --from=runtime /etc/ssl/certs/ /etc/ssl/certs/
COPY --from=runtime /lib/ /lib/
COPY --from=runtime /lib64/ /lib64/
COPY --from=runtime /usr/lib/ /usr/lib/
COPY --from=runtime /usr/local/etc/ /usr/local/etc/
COPY --from=runtime /usr/local/lib/ /usr/local/lib/
COPY --from=runtime /usr/local/sbin/php-fpm /usr/local/sbin/
COPY --from=runtime /usr/share/ca-certificates/ /usr/share/ca-certificates/
COPY --from=dependencies /var/www/html/api /var/www/html/api
RUN --mount=from=dependencies,src=/bin,dst=/bin mkdir --mode=1777 /tmp
STOPSIGNAL SIGQUIT
EXPOSE 9000
ENTRYPOINT ["/usr/local/sbin/php-fpm"]
