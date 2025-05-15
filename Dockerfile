FROM php:8.2-fpm-alpine
WORKDIR /var/www/html

RUN apk add --no-cache nginx && \
    docker-php-ext-install pdo pdo_mysql && \
    echo "events {} http { server { listen 8000; root /var/www/html/public; location / { try_files \$uri /index.php?\$args; } location ~ \.php\$ { fastcgi_pass 127.0.0.1:9000; include fastcgi_params; fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name; } } }" > /etc/nginx/nginx.conf

COPY --chown=www-data:www-data . .
RUN chmod -R 775 storage bootstrap/cache && \
    composer install --no-dev --optimize-autoloader

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]