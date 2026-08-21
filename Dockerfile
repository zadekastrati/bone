# Optional single-image build (e.g. some PaaS). Local development uses Laravel Sail: see compose.yaml.
# Requires PHP 8.2+ per composer.json — bump the FROM line if you use this file.
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql zip mbstring gd

# Copy composer.lock and composer.json
COPY composer.lock composer.json ./

# Install PHP dependencies
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

# Copy the rest of the app
COPY . .

# Install frontend dependencies & build Vite
RUN npm install && npm run build

# Set Apache document root to Laravel public folder
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# storage/ and bootstrap/cache/ must be writable by the user Apache runs as
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port 80
EXPOSE 80

# Cache config/routes/views on container start (not at build time — env vars
# like DB credentials aren't available yet during the image build, and
# config:cache would otherwise bake in empty/wrong values permanently).
CMD php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && apache2-foreground
