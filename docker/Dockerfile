FROM php:8.3-fpm

# Install PHP extensions (pdo_mysql, bcmath, zip, etc.)
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/install-php-extensions
RUN chmod +x /usr/local/bin/install-php-extensions && install-php-extensions \
    pdo_mysql bcmath zip intl redis pcntl exif gd

# Install system dependencies
RUN apt-get update && apt-get install -y git unzip nano supervisor \
    && rm -rf /var/lib/apt/lists/*

# Install Composer (latest version)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy php.ini if you want to customize PHP settings (skip if not needed)
# COPY docker/php.ini /usr/local/etc/php/conf.d/php.ini

# Start PHP-FPM
CMD ["php-fpm"]
