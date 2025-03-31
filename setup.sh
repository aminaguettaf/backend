#!/bin/bash

# Set permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Create symlink
php artisan storage:link

# Start PHP-FPM
php-fpm