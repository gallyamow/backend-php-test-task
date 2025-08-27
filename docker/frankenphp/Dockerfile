FROM dunglas/frankenphp

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

ENV FRANKENPHP_CONFIG="worker ./src/frankenphp-worker.php"

WORKDIR /app
COPY . /app

EXPOSE 8080
