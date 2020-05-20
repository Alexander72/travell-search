FROM php:7.3-cli

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
RUN pecl install redis
RUN docker-php-ext-enable redis
RUN docker-php-ext-install pdo_mysql

COPY . /var/www/html/

WORKDIR /var/www/html/

RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer
RUN composer install

CMD /usr/local/bin/php /var/www/html/bin/console LoadMultipleFlights --env=prod --no-debug --quiet
