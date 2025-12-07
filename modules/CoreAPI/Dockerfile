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
    supervisor \
    linux-headers

# Install Redis extension dependencies
RUN apk add --no-cache \
    autoconf \
    g++ \
    make \
    icu-dev

# Configure and install PHP extensions with JPEG support
RUN docker-php-ext-configure gd --with-jpeg --with-freetype
RUN docker-php-ext-install pdo pdo_mysql gd xml zip intl bcmath sockets pcntl

# Install Redis extension via PECL
RUN pecl channel-update pecl.php.net && \
    pecl install redis && \
    docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files (không có vendor/ vì đã gitignore)
COPY . .

# NOTE: Không cài composer/npm ở đây vì:
# 1. vendor/ không có trong Git (gitignore)
# 2. node_modules/ không có trong Git (gitignore)
# 3. Sẽ cài sau khi container chạy trong deploy.sh
# 4. Tránh rebuild image mỗi khi thay đổi dependencies

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

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
    echo '' >> /etc/supervisor.d/supervisord.ini && \
    echo '[program:reverb]' >> /etc/supervisor.d/supervisord.ini && \
    echo 'command=php /var/www/html/artisan reverb:start --host=0.0.0.0 --port=8080' >> /etc/supervisor.d/supervisord.ini && \
    echo 'directory=/var/www/html' >> /etc/supervisor.d/supervisord.ini && \
    echo 'user=www-data' >> /etc/supervisor.d/supervisord.ini && \
    echo 'autostart=true' >> /etc/supervisor.d/supervisord.ini && \
    echo 'autorestart=true' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stdout_logfile=/var/log/supervisor/reverb.log' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stdout_logfile_maxbytes=10MB' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stderr_logfile=/var/log/supervisor/reverb.error.log' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stderr_logfile_maxbytes=10MB' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stopwaitsecs=60' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stopsignal=TERM' >> /etc/supervisor.d/supervisord.ini && \
    echo '' >> /etc/supervisor.d/supervisord.ini && \
    echo '[program:queue-worker]' >> /etc/supervisor.d/supervisord.ini && \
    echo 'command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600' >> /etc/supervisor.d/supervisord.ini && \
    echo 'directory=/var/www/html' >> /etc/supervisor.d/supervisord.ini && \
    echo 'user=www-data' >> /etc/supervisor.d/supervisord.ini && \
    echo 'autostart=true' >> /etc/supervisor.d/supervisord.ini && \
    echo 'autorestart=true' >> /etc/supervisor.d/supervisord.ini && \
    echo 'numprocs=1' >> /etc/supervisor.d/supervisord.ini && \
    echo 'redirect_stderr=true' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stdout_logfile=/var/log/supervisor/queue-worker.log' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stdout_logfile_maxbytes=10MB' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stderr_logfile=/var/log/supervisor/queue-worker.error.log' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stderr_logfile_maxbytes=10MB' >> /etc/supervisor.d/supervisord.ini && \
    echo 'stopwaitsecs=3600' >> /etc/supervisor.d/supervisord.ini && \
    mkdir -p /var/log/supervisor

EXPOSE 80 8080

# Create entrypoint script for migrations, seeding, and supervisor startup
RUN echo '#!/bin/sh' > /entrypoint.sh && \
    echo 'set -e' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'cd /var/www/html' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Ensure dependencies exist (for dev mode with volumes)' >> /entrypoint.sh && \
    echo 'if [ ! -d "vendor" ] || [ ! "$(ls -A vendor)" ]; then' >> /entrypoint.sh && \
    echo '    echo "Installing Composer dependencies..."' >> /entrypoint.sh && \
    echo '    composer install --no-dev --optimize-autoloader --no-interaction' >> /entrypoint.sh && \
    echo 'fi' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'if [ ! -d "node_modules" ] || [ ! "$(ls -A node_modules)" ]; then' >> /entrypoint.sh && \
    echo '    echo "Installing NPM dependencies..."' >> /entrypoint.sh && \
    echo '    npm install --production' >> /entrypoint.sh && \
    echo 'fi' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Ensure .env exists and generate APP_KEY if needed' >> /entrypoint.sh && \
    echo 'if [ ! -f ".env" ]; then' >> /entrypoint.sh && \
    echo '    echo "Creating .env from .env.example..."' >> /entrypoint.sh && \
    echo '    cp .env.example .env 2>/dev/null || true' >> /entrypoint.sh && \
    echo 'fi' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Generate APP_KEY if not set' >> /entrypoint.sh && \
    echo 'if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then' >> /entrypoint.sh && \
    echo '    echo "Generating application key..."' >> /entrypoint.sh && \
    echo '    php artisan key:generate --force' >> /entrypoint.sh && \
    echo 'fi' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "Waiting for MySQL to be ready..."' >> /entrypoint.sh && \
    echo 'until php artisan db:show 2>/dev/null; do' >> /entrypoint.sh && \
    echo '    echo "MySQL unavailable - sleeping"' >> /entrypoint.sh && \
    echo '    sleep 2' >> /entrypoint.sh && \
    echo 'done' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "MySQL is up - running migrations"' >> /entrypoint.sh && \
    echo 'php artisan migrate --force || echo "Migration failed, continuing..."' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Run database seeding only on first startup' >> /entrypoint.sh && \
    echo 'SEED_MARKER="/var/www/html/storage/app/.db_seeded"' >> /entrypoint.sh && \
    echo 'if [ ! -f "$SEED_MARKER" ]; then' >> /entrypoint.sh && \
    echo '    echo "First startup detected - running database seeder..."' >> /entrypoint.sh && \
    echo '    if php artisan db:seed --force; then' >> /entrypoint.sh && \
    echo '        echo "Database seeded successfully!"' >> /entrypoint.sh && \
    echo '        touch "$SEED_MARKER"' >> /entrypoint.sh && \
    echo '        echo "Seed marker created at $SEED_MARKER"' >> /entrypoint.sh && \
    echo '    else' >> /entrypoint.sh && \
    echo '        echo "Warning: Database seeding failed, but continuing..."' >> /entrypoint.sh && \
    echo '    fi' >> /entrypoint.sh && \
    echo 'else' >> /entrypoint.sh && \
    echo '    echo "Database already seeded (marker found), skipping seed..."' >> /entrypoint.sh && \
    echo 'fi' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo '# Clear and cache Laravel configuration' >> /entrypoint.sh && \
    echo 'echo "Optimizing Laravel configuration..."' >> /entrypoint.sh && \
    echo 'php artisan config:clear' >> /entrypoint.sh && \
    echo 'php artisan cache:clear' >> /entrypoint.sh && \
    echo 'php artisan config:cache' >> /entrypoint.sh && \
    echo '' >> /entrypoint.sh && \
    echo 'echo "Starting services with supervisor"' >> /entrypoint.sh && \
    echo 'exec /usr/bin/supervisord -c /etc/supervisor.d/supervisord.ini' >> /entrypoint.sh && \
    chmod +x /entrypoint.sh


CMD ["/entrypoint.sh"]
