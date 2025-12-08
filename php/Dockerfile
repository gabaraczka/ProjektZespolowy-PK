FROM php:8.1-fpm-alpine

RUN apk add --no-cache \
    zlib-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    postgresql-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        opcache \
        zip \
        gd \
        bcmath \
        pgsql \
        pdo_pgsql

RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/custom.ini

COPY . /app

WORKDIR /app

VOLUME ["/app"]