# Stage 1 — Compilation CSS
FROM node:20-alpine AS assets

WORKDIR /app
COPY package*.json ./
RUN npm install
COPY style/ ./style/
RUN npm run build

# Stage 2 — Image PHP/Apache finale
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
        libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
        /etc/apache2/sites-available/000-default.conf \
    && echo '<Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>' \
        > /etc/apache2/conf-available/app.conf \
    && a2enconf app

WORKDIR /var/www/html

COPY . .
COPY --from=assets /app/public/style/ ./public/style/

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN mkdir -p public/storage \
    && chown -R www-data:www-data public/storage \
    && chmod -R 775 public/storage

EXPOSE 80