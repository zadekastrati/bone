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
    && docker-php-ext-install pdo pdo_mysql zip mbstring gd bcmath

# Copy composer.lock and composer.json
COPY composer.lock composer.json ./

# Install PHP dependencies. --no-scripts: composer.json's post-autoload-dump
# hook runs `artisan package:discover`, which needs the actual app code —
# not copied in yet at this point (kept separate so this slow dependency
# install layer only reruns when composer.lock changes, not on every code
# change). Re-run the discover step below, once the app code is present.
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-scripts

# Copy the rest of the app
COPY . .

RUN composer run-script post-autoload-dump

# Install frontend dependencies & build Vite
RUN npm install && npm run build

# Set Apache document root to Laravel public folder
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Enable Apache mod_rewrite, mod_expires, mod_headers (the latter two back the
# Cache-Control/Expires rules in public/.htaccess)
RUN a2enmod rewrite expires headers

# mod_php (used by this image) requires exactly one MPM, and only
# mpm_prefork is thread-safe with it. Force that regardless of whatever
# else may have gotten enabled — some build environments were seeing
# "More than one MPM loaded" at container start despite this image
# building clean locally, so don't rely on the default state.
RUN (a2dismod mpm_event || true) \
    && (a2dismod mpm_worker || true) \
    && a2enmod mpm_prefork

# storage/ and bootstrap/cache/ must be writable by the user Apache runs as
RUN chown -R www-data:www-data storage bootstrap/cache

# Expose port 80
EXPOSE 80

# Cache config/routes/views on container start (not at build time — env vars
# like DB credentials aren't available yet during the image build, and
# config:cache would otherwise bake in empty/wrong values permanently).
#
# The mkdir/chown here run on every start, not just once at build time,
# because a host that mounts a persistent volume over storage/ (e.g. to
# keep uploaded files across deploys) replaces whatever the image had
# there with the volume's own — initially empty — contents, silently
# wiping out the framework/{cache,sessions,views}, logs, and their
# ownership that the RUN step below only set up at build time.
# The MPM re-enable here (identical to the RUN step above) runs again as the
# very last thing before Apache actually starts, in case the platform this
# runs on does anything to the container between "image built" and
# "container started" that re-enables a conflicting MPM — build-time alone
# wasn't enough to stop "AH00534: More than one MPM loaded" in that case.
CMD mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && (a2dismod mpm_event || true) \
    && (a2dismod mpm_worker || true) \
    && a2enmod mpm_prefork \
    && apache2ctl -M \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && apache2-foreground
