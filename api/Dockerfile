FROM php:7.3.3-fpm

# Install dependencies
RUN apt-get -y update \
    && apt-get -y install git unzip

# Install PHP extensions
RUN curl -sL 'https://raw.githubusercontent.com/composer/getcomposer.org/master/web/installer' | php -- --quiet \
    && mv composer.phar /usr/bin/composer \
    && pecl install redis \
    && echo 'extension=redis.so' > /usr/local/etc/php/conf.d/redis.ini \
    && pecl install xdebug-2.7.0beta1 \
    && docker-php-ext-install pdo_mysql

# Prioritise IP version
RUN echo 'label ::1/128 0' > /etc/gai.conf
