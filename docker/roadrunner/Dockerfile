FROM ghcr.io/roadrunner-server/roadrunner:latest AS roadrunner_builder

FROM php:8.3-cli

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

COPY --from=roadrunner_builder /usr/bin/rr /usr/local/bin/rr

WORKDIR /app
COPY . /app

EXPOSE 8088

CMD ["rr", "serve", "-c", ".rr.yaml"]