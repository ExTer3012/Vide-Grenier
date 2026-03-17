FROM php:8.2-apache

# Extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
        libzip-dev \
        zip \
        unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite pour le routing
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuration Apache : document root → /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
        /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory /var/www/>|<Directory /var/www/html/public/>|g' \
        /etc/apache2/conf-available/docker-php.conf || true

# Permettre .htaccess
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

WORKDIR /var/www/html

# Copier les fichiers (sauf ce qui est dans .dockerignore)
COPY . .

# Installer les dépendances PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Permissions sur le dossier uploads
RUN mkdir -p public/storage \
    && chown -R www-data:www-data public/storage \
    && chmod -R 775 public/storage

EXPOSE 80