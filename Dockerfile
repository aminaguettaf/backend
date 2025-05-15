# Utilise une image officielle PHP avec Apache et extensions nécessaires
FROM php:8.1-apache

# Installe les dépendances système
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

# Installe les extensions PHP nécessaires
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Active mod_rewrite pour Laravel
RUN a2enmod rewrite

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copie les fichiers du projet
COPY . /var/www/html

# Définit le dossier de travail
WORKDIR /var/www/html

# Installe les dépendances PHP via Composer
RUN composer install --no-dev --optimize-autoloader

# Donne les permissions sur les dossiers de stockage et cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose le port 80
EXPOSE 80

# Démarre Apache en avant-plan
CMD ["apache2-foreground"]
