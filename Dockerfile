FROM php:8.2-fpm

# Install PHP dependencies
RUN apt-get update && apt-get install -y \
    zip unzip curl git libzip-dev libpng-dev libonig-dev libxml2-dev \
    libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files for caching
COPY composer.* ./

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copy all Laravel source code
COPY . .

# Copy default .env if not present
RUN [ ! -f .env ] && cp .env.example .env || true

# Laravel setup
RUN php artisan key:generate && \
    php artisan storage:link && \
    php artisan migrate --force && \
    php artisan optimize

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
