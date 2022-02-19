# syntax = docker/dockerfile:experimental
FROM debian:bullseye-slim AS build-dependencies
RUN --mount=type=cache,id=api:/var/cache/apt,target=/var/cache/apt \
    --mount=type=cache,id=api:/var/lib/apt/lists,target=/var/lib/apt/lists \
    apt-get -y update && \
    apt-get -y install \
        autoconf \
        build-essential \
        git \
        libnghttp2-dev \
        libtool \
        pkg-config

FROM build-dependencies AS openssl
RUN git clone --depth 1 -b OpenSSL_1_1_1k+quic https://github.com/quictls/openssl && \
    cd openssl && \
    ./config enable-tls1_3 && \
    make -j $(nproc) && \
    make install_sw && \
    ldconfig

FROM build-dependencies AS nghttp3
RUN git clone --depth=1 -b v0.1.0 https://github.com/ngtcp2/nghttp3 && \
    cd nghttp3 && \
    autoreconf -fi && \
    ./configure --enable-lib-only && \
    make -j $(nproc) && \
    make install

FROM build-dependencies AS ngtcp2
COPY --from=openssl /usr/local/ /usr/local/
COPY --from=nghttp3 /usr/local/ /usr/local/
RUN git clone --depth=1 -b v0.1.0 https://github.com/ngtcp2/ngtcp2 && \
    cd ngtcp2 && \
    autoreconf -fi && \
    ./configure --enable-lib-only && \
    make -j $(nproc) && \
    make install

FROM build-dependencies AS curl
COPY --from=openssl /usr/local/ /usr/local/
COPY --from=nghttp3 /usr/local/ /usr/local/
COPY --from=ngtcp2 /usr/local/ /usr/local/
RUN git clone --depth=1 -b curl-7_80_0 https://github.com/curl/curl && \
    cd curl && \
    autoreconf -fi && \
    ./configure --enable-alt-svc --with-openssl --with-nghttp2 --with-nghttp3 --with-ngtcp2 && \
    make -j $(nproc) && \
    make install && \
    ldconfig

FROM php:8.1.3-fpm AS runtime
WORKDIR /var/www/html/api
COPY --from=openssl /usr/local/include/ /usr/local/include/
COPY --from=nghttp3 /usr/local/include/ /usr/local/include/
COPY --from=ngtcp2 /usr/local/include/ /usr/local/include/
COPY --from=curl /usr/local/include/curl/ /usr/local/include/curl/
COPY --from=curl /usr/local/bin/curl /usr/local/bin/
COPY --from=curl /usr/local/bin/curl-config /usr/local/bin/
COPY --from=curl /usr/local/lib/ /usr/local/lib/
RUN docker-php-source extract
COPY php/ /usr/src/php/
RUN --mount=type=cache,id=api:/var/cache/apt,target=/var/cache/apt \
    --mount=type=cache,id=api:/var/lib/apt/lists,target=/var/lib/apt/lists \
    apt-get -y update && \
    apt-get -y install \
        libargon2-dev \
        libicu-dev \
        libnghttp2-dev \
        libonig-dev \
        libpq5 \
        libpq-dev \
        libreadline-dev \
        libsodium-dev \
        libsqlite3-dev \
        libxml2-dev \
        zlib1g-dev && \
    ldconfig && \
    cd /usr/src/php/ext/curl && \
    patch -u < /usr/src/php/ext/curl/interface.patch && \
    cd /usr/src/php && \
    ./configure \
        --disable-cgi \
        --enable-fpm \
        --enable-mbstring \
        --with-config-file-path=$PHP_INI_DIR \
        --with-config-file-scan-dir=$PHP_INI_DIR/conf.d \
        --with-curl \
        --with-openssl \
        --with-pear \
        --with-readline \
        --with-fpm-group=www-data \
        --with-fpm-user=www-data && \
    make -j $(nproc) && \
    make install && \
    make clean && \
    ln -s /etc/ssl /usr/local/ssl && \
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
    docker-php-source delete && \
    rm -rf /tmp/* && \
    echo 'display_errors = 0' > /usr/local/etc/php/conf.d/overrides.ini && \
    sed -i 's/^access\.log/; \0/' /usr/local/etc/php-fpm.d/docker.conf && \
    echo 'label ::1/128 0' > /etc/gai.conf

FROM runtime AS dependencies
COPY --from=composer:2.2.1 /usr/bin/composer /usr/bin/composer
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
    composer install --no-progress --no-scripts && \
    composer dump-autoload --no-scripts --optimize
RUN --mount=type=cache,id=api:/root/.composer,target=/mnt/.composer \
    cp -r /mnt/.composer /root/
RUN pecl install pcov && \
    docker-php-ext-enable pcov

FROM scratch
COPY src /var/www/html/api/src
COPY --from=runtime /etc/gai.conf /etc/
COPY --from=runtime /etc/group /etc/
COPY --from=runtime /etc/ld.so.cache /etc/
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
