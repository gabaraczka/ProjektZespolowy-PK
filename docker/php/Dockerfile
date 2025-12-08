FROM php:8.3-fpm

ENV POSTGRES_PASSWORD=docker
ENV POSTGRES_DB=db
ENV POSTGRES_USER=docker

RUN apt-get update \
    && apt-get install -y \
        libpq-dev \
        git \
        unzip \
        libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip pgsql

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

RUN mkdir -p /app/public/uploads \
    && mkdir -p /app/public/assets \
    && chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 777 /app/public/uploads

COPY composer.json composer.lock ./

RUN composer install --no-scripts --no-autoloader

COPY . .

RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 777 /app/public/uploads

