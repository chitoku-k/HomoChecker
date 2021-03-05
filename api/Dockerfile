FROM php:8.0.2-fpm AS runtime
COPY --from=composer:1 /usr/bin/composer /usr/bin/composer

RUN apt-get -y update && \
    apt-get -y install \
        git \
        unzip \
        libpq5 \
        libpq-dev && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    docker-php-ext-install pdo_pgsql && \
    echo 'display_errors = 0' > /usr/local/etc/php/conf.d/overrides.ini && \
    sed -i 's/^access\.log/; \0/' /usr/local/etc/php-fpm.d/docker.conf && \
    echo 'label ::1/128 0' > /etc/gai.conf

FROM runtime AS production
WORKDIR /var/www/html/api
COPY . /var/www/html/api

RUN composer install --no-dev --no-scripts && \
    apt-get -y remove \
        git \
        unzip \
        libpq-dev && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

EXPOSE 9000
