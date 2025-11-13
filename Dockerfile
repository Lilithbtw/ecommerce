# Dockerfile
FROM php:8.2-fpm

# Install needed system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist

# Set upload directory permissions
RUN mkdir -p public/uploads && chown -R www-data:www-data public/uploads

CMD ["php-fpm"]
