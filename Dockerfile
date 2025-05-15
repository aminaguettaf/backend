FROM php:8.2-fpm-alpine

# 1. Installer les dépendances système + Composer
RUN apk add --no-cache \
    nginx \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# 2. Copier seulement les fichiers nécessaires pour l'installation des dépendances d'abord
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 3. Copier le reste du code
COPY --chown=www-data:www-data . .

# 4. Configurer les permissions
RUN chmod -R 775 storage bootstrap/cache

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]