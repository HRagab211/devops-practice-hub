# syntax=docker/dockerfile:1

# ==========================================
# 1. Build frontend assets
# ==========================================
FROM node:22-alpine AS frontend-builder

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

RUN npm run build


# ==========================================
# 2. Shared PHP environment
# ==========================================
FROM php:8.5-fpm-alpine3.24 AS php-base

WORKDIR /var/www/html

# Libraries required at runtime.
RUN apk add --no-cache \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
        oniguruma \
    # Libraries required to compile extensions.
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
    && apk del .build-deps

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY tools/docker/app/opcache.ini \
    /usr/local/etc/php/conf.d/opcache.ini


# ==========================================
# 3. Install Composer dependencies
# ==========================================
FROM php-base AS composer-builder

COPY --from=composer:2 \
    /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache git unzip

ENV COMPOSER_ALLOW_SUPERUSER=1

# Cache dependencies separately from application code.
COPY composer.json composer.lock ./

# Application classes are not present yet.
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --no-autoloader

COPY . .

# Generate the optimized autoloader after copying app code.
RUN composer dump-autoload \
        --no-dev \
        --optimize \
        --no-scripts \
    && php artisan package:discover --ansi \
    && composer check-platform-reqs --no-dev


# ==========================================
# 4. Final application image
# ==========================================
FROM php-base AS production-stage

COPY --from=composer-builder \
    /var/www/html /var/www/html

COPY --from=frontend-builder \
    /app/public/build /var/www/html/public/build

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R u+rwX,go-rwx storage bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm", "-F"]


# ==========================================
# Nginx image
# ==========================================
FROM nginx:stable-alpine AS web-stage

COPY tools/docker/nginx/default.conf \
    /etc/nginx/conf.d/default.conf

COPY --from=production-stage \
    /var/www/html/public /var/www/html/public
