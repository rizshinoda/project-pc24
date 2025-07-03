FROM php:8.2-fpm

ARG ENV_FILE

# Install dependencies
RUN apt-get update && apt-get install -y \
    zip unzip curl git libzip-dev libpng-dev libonig-dev libxml2-dev \
    libjpeg-dev libfreetype6-dev gnupg ca-certificates lsb-release \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath gd

# Install Node.js & npm (v18 LTS)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy source code
COPY . .

# Copy selected .env
COPY ${ENV_FILE} .env


# Optional: install npm packages and build assets (uncomment if needed)
# RUN npm install && npm run build

# Laravel config
RUN php artisan key:generate \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
