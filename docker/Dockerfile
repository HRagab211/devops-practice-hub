# syntax=docker/dockerfile:1
FROM composer:2.8.12 AS composer

FROM node:22.16.0-bookworm-slim AS assets
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php:8.4.23-fpm-bookworm AS application
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        gosu libfcgi-bin libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
        libwebp-dev libzip-dev libonig-dev libxml2-dev libsqlite3-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd mbstring pdo_sqlite pcntl bcmath zip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader --no-progress
COPY . .
COPY --from=assets /build/public/build ./public/build
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-hub.ini
RUN mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs storage/app/public storage/app/private \
        storage/app/reports /var/lib/hub \
    && composer dump-autoload --optimize --no-scripts \
    && php artisan package:discover --no-interaction \
    && ln -s /var/www/html/storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache /var/lib/hub \
    && chmod +x docker/entrypoint.sh docker/fpm-health.sh
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["php-fpm", "-F"]

FROM nginx:1.28.0-bookworm AS nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=application /var/www/html/public /var/www/html/public
