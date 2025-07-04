FROM php:8.2-fpm

ARG ENV_FILE

# Install dependencies
RUN apt-get update && apt-get install -y \
    zip unzip curl git libzip-dev libpng-dev libonig-dev libxml2-dev \
    libjpeg-dev libfreetype6-dev gnupg ca-certificates lsb-release \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath gd

# Install Node.js & npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy all source
COPY . .

# Copy selected .env
COPY ${ENV_FILE} .env

# Laravel setup (tanpa queue:work karena itu untuk proses runtime, bukan build)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
 && npm install && npm run build \
 && php artisan key:generate \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && php artisan storage:link \
 && chown -R www-data:www-data storage bootstrap/cache public/storage \
 && chmod -R 775 storage bootstrap/cache public/storage

EXPOSE 9000
CMD ["php-fpm"]
