FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libbz2-dev \
    libsodium-dev

RUN docker-php-ext-install \
    zip \
    sodium \
    sockets

RUN pecl install redis && docker-php-ext-enable redis

#WORKDIR /app
#COPY . /app

