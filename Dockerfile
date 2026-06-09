FROM dunglas/frankenphp:1-php8.4

LABEL maintainer="SAFT Checker"

# Install PHP extensions
RUN install-php-extensions \
    pcntl \
    bcmath \
    intl \
    xml \
    simplexml \
    dom \
    libxml \
    zip

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first for caching
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

# Copy application code
COPY . .

# Run post-install scripts
RUN composer dump-autoload --optimize

# Build assets if needed
RUN if [ -f "package.json" ]; then \
        curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
        apt-get install -y nodejs && \
        npm ci && npm run build && \
        rm -rf node_modules; \
    fi

# Create SQLite database
RUN mkdir -p /app/database && \
    touch /app/database/database.sqlite

# Cache routes and views (NOT config — secrets unavailable at build time)
RUN php artisan route:cache && \
    php artisan view:cache

ENTRYPOINT ["php", "artisan", "octane:frankenphp"]
CMD ["--host=0.0.0.0", "--port=8080"]
