# Stage 1: Build & Dependencies
FROM php:8.2-fpm AS base

# Install necessary PHP and system packages
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project (for installing dependencies)
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist

# Set upload directory permissions
RUN mkdir -p public/uploads && chown -R www-data:www-data public/uploads

# --- Stage 2: Final Image with Nginx and Supervisor ---
FROM base

# Install Nginx and Supervisor (Process Manager)
RUN apt-get update && apt-get install -y \
    nginx supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy Nginx configuration
# NOTE: Ensure this nginx.conf points fastcgi_pass to 127.0.0.1:9000
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# Remove default Nginx site config
RUN rm -f /etc/nginx/sites-enabled/default

# Copy the Supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port 80 (where Nginx will be listening)
EXPOSE 80

# Define the default command to run Supervisor.
# This will be overridden in docker-compose to run the setup script first.
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]