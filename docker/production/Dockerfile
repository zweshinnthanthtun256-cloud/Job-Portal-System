FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* pnpm-lock.yaml* ./
RUN corepack enable && pnpm install --frozen-lockfile
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN pnpm run build

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM php:8.3-fpm-alpine
RUN apk add --no-cache icu-dev libzip-dev oniguruma-dev supervisor && docker-php-ext-install intl mbstring pdo_mysql opcache zip
WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY docker/production/php.ini /usr/local/etc/php/conf.d/jobsphere.ini
RUN chown -R www-data:www-data storage bootstrap/cache
USER www-data
CMD ["php-fpm", "-F"]
