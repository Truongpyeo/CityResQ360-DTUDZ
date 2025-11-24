FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    mysql-client \
    nodejs \
    npm

# Configure and install PHP extensions with JPEG support
RUN docker-php-ext-configure gd --with-jpeg --with-freetype
RUN docker-php-ext-install pdo pdo_mysql gd xml zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader
RUN npm install

# Build assets
RUN npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000 5173

# Create startup script
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'php artisan serve --host=0.0.0.0 --port=8000 &' >> /start.sh && \
    echo 'npm run dev -- --host 0.0.0.0 --port 5173 &' >> /start.sh && \
    echo 'wait' >> /start.sh && \
    chmod +x /start.sh

CMD ["/start.sh"]
