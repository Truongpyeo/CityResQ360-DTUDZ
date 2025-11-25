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
    npm \
    nginx \
    supervisor

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

# Copy Nginx configuration
COPY ./nginx/default.conf /etc/nginx/http.d/default.conf

# Create supervisor configuration
RUN mkdir -p /etc/supervisor.d && \
    echo '[supervisord]' > /etc/supervisor.d/supervisord.ini && \
    echo 'nodaemon=true' >> /etc/supervisor.d/supervisord.ini && \
    echo 'user=root' >> /etc/supervisor.d/supervisord.ini && \
    echo 'logfile=/var/log/supervisor/supervisord.log' >> /etc/supervisor.d/supervisord.ini && \
    echo 'pidfile=/var/run/supervisord.pid' >> /etc/supervisor.d/supervisord.ini && \
    echo '' >> /etc/supervisor.d/supervisord.ini && \
    echo '[program:php-fpm]' >> /etc/supervisor.d/supervisord.ini && \
    echo 'command=php-fpm -F' >> /etc/supervisor.d/supervisord.ini && \
    echo 'autostart=true' >> /etc/supervisor.d/supervisord.ini && \
    echo 'autorestart=true' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stdout_logfile=/dev/stdout' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stdout_logfile_maxbytes=0' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stderr_logfile=/dev/stderr' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stderr_logfile_maxbytes=0' >> /etc/supervisor.d/supervisord.ini && \
    echo '' >> /etc/supervisor.d/supervisord.ini && \
    echo '[program:nginx]' >> /etc/supervisor.d/supervisord.ini && \
    echo 'command=nginx -g "daemon off;"' >> /etc/supervisor.d/supervisord.ini && \
    echo 'autostart=true' >> /etc/supervisor.d/supervisord.ini && \
    echo 'autorestart=true' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stdout_logfile=/dev/stdout' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stdout_logfile_maxbytes=0' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stderr_logfile=/dev/stderr' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stderr_logfile_maxbytes=0' >> /etc/supervisor.d/supervisord.ini && \
    mkdir -p /var/log/supervisor

EXPOSE 80

# Create entrypoint script for migrations and supervisor startup
RUN echo '#!/bin/sh' > /entrypoint.sh && \
    echo 'set -e' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "Waiting for MySQL to be ready..."' >> /entrypoint.sh && \
    echo 'until php artisan db:show 2>/dev/null; do' >> /entrypoint.sh && \
    echo '    echo "MySQL is unavailable - sleeping"' >> /entrypoint.sh && \
    echo '    sleep 2' >> /entrypoint.sh && \
    echo 'done' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "MySQL is up - running migrations"' >> /entrypoint.sh && \
    echo 'php artisan migrate --force || echo "Migration failed, continuing..."' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "Starting services with supervisor"' >> /entrypoint.sh && \
    echo 'exec /usr/bin/supervisord -c /etc/supervisor.d/supervisord.ini' >> /entrypoint.sh && \
    chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]
