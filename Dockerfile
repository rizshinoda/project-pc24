FROM php:8.2-fpm

# Install dependensi sistem dan ekstensi PHP
RUN apt-get update && apt-get install -y \
    zip unzip curl git libzip-dev libpng-dev libonig-dev libxml2-dev \
    libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set workdir
WORKDIR /var/www

# Salin composer.json dan composer.lock dulu (untuk layer cache)
COPY composer.* ./

# Install dependency Laravel (tanpa vendor dari luar)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Salin semua file Laravel KECUALI vendor, node_modules, dll
COPY . .

# (Opsional) install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs

# (Opsional) build frontend (jika pakai Vite)
# RUN npm install && npm run build

# Set permission untuk Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
