FROM composer:2.8 AS dependencies

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

FROM php:8.3-fpm-alpine

RUN apk add --no-cache oniguruma oniguruma-dev \
    && docker-php-ext-install mbstring pdo_mysql \
    && apk del oniguruma-dev

WORKDIR /var/www/html

COPY --from=dependencies /usr/bin/composer /usr/bin/composer
COPY --from=dependencies /app/vendor ./vendor
COPY . .

RUN mkdir -p runtime/cache runtime/compile \
    && chown -R www-data:www-data runtime

EXPOSE 9000

CMD ["php-fpm"]
