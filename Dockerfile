FROM php:7.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    zlib1g-dev \
    libzip-dev

RUN docker-php-ext-install ctype
RUN docker-php-ext-install iconv
RUN docker-php-ext-install json
RUN docker-php-ext-install sockets
RUN docker-php-ext-install zip

COPY . /var/www/

WORKDIR /var/www

RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN php composer.phar install