FROM php:8.2-apache

RUN apt-get update
RUN apt-get update --fix-missing
RUN apt-get install -y build-essential libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev libjpeg62-turbo-dev libzip-dev
RUN apt-get update --fix-missing
RUN apt-get install -y libmagickwand-dev --no-install-recommends && rm -rf /var/lib/apt/lists/*
RUN apt-get update --fix-missing
RUN apt-get install -y unzip
RUN apt-get update --fix-missing
RUN apt-get install -y git
RUN apt-get update --fix-missing
RUN apt-get install -y default-mysql-client && rm -rf /var/lib/apt
RUN apt-get update --fix-missing
RUN printf "\n" | pecl install imagick

ENV ACCEPT_EULA=Y
RUN apt-get update && apt-get install -y gnupg2
RUN apt-get update
RUN ACCEPT_EULA=Y apt-get -y --no-install-recommends install unixodbc-dev
RUN pecl install sqlsrv
RUN pecl install pdo_sqlsrv
RUN docker-php-ext-enable sqlsrv pdo_sqlsrv

RUN docker-php-ext-install soap
RUN docker-php-ext-enable imagick
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) mysqli pdo_mysql gd zip exif

RUN mkdir /var/www/html/public -p

RUN rm /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini-production
COPY ./php.ini /usr/local/etc/php

COPY app.conf /etc/apache2/sites-available/app.conf
RUN a2ensite app && a2dissite 000* && a2enmod rewrite

RUN service apache2 restart

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
