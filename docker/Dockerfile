FROM composer:2 AS composer

FROM php:8.0

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get -y --no-install-recommends install \
        git \
        libzip-dev \
        unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip
