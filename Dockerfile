FROM php:8.2-fpm

# Install system dependencies with WebP support
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip unzip git curl \
 && docker-php-ext-configure gd \
    --with-jpeg \
    --with-freetype \
    --with-webp \
 && docker-php-ext-install \
    pdo_mysql mbstring exif pcntl bcmath gd zip



# ✅ Install Node.js 18 (untuk Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs
# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project (used in production build)
COPY . .

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

EXPOSE 9000
CMD ["php-fpm"]
