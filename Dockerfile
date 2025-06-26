FROM php:8.2-fpm

# Install ekstensi PHP dan dependensi sistem
RUN apt-get update && apt-get install -y \
    zip unzip curl git libzip-dev libpng-dev libonig-dev libxml2-dev \
    libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set workdir
WORKDIR /var/www

# Salin file Laravel
COPY . /var/www

# (Opsional) versi terbaru Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs
# Beri permission
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www
# Install dependency Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 9000
