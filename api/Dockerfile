FROM php:7.1.9-fpm

RUN apt-get -y update
RUN apt-get -y install git unzip

RUN curl -sL 'https://raw.githubusercontent.com/composer/getcomposer.org/master/web/installer' | php -- --quiet
RUN mv composer.phar /usr/bin/composer

RUN pecl install xdebug-2.5.5
RUN docker-php-ext-install pdo_mysql
