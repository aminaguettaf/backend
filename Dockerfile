# Étape 1 : Builder PHP + extensions
FROM php:8.1-fpm AS php

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zlib1g-dev \
    libgrpc-dev \
    && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP
RUN docker-php-ext-install pdo_mysql zip mbstring exif pcntl bcmath intl opcache
RUN pecl install grpc protobuf && docker-php-ext-enable grpc

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier l'application et installer les dépendances
WORKDIR /var/www/html
COPY . .
RUN composer install --optimize-autoloader --no-dev

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Étape 2 : Nginx
FROM nginx:alpine

# Copier la config Nginx
COPY .docker/nginx.conf /etc/nginx/conf.d/default.conf

# Copier les fichiers PHP depuis l'étape "php"
COPY --from=php /var/www/html /var/www/html

# Exposer le port (Railway utilise souvent 8080)
EXPOSE 8080

# Lancer Nginx
CMD ["nginx", "-g", "daemon off;"]