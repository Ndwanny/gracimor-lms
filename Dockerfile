FROM php:8.2-cli

# System dependencies (cache bust: v2)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        xml \
        zip \
        bcmath \
        gd \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project
COPY . .

# Create required directories and storage symlink at build time
RUN mkdir -p bootstrap/cache \
        storage/framework/{sessions,views,cache} \
        storage/logs \
        storage/app/public \
    && chmod -R 775 bootstrap/cache storage \
    && ln -sf /app/storage/app/public /app/public/storage

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 8080

CMD php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan migrate --force \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
