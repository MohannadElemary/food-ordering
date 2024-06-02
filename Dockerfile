FROM php:8.2-fpm

# Install dependencies and PHP Core extensions
RUN apt-get update && apt-get install -y \
    build-essential \
    nginx \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    curl \
    libzip-dev \
    libmagickwand-dev  # Required for Imagick

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl gd bcmath ctype fileinfo pdo xml zip sockets

# Install Imagick and Redis extension
RUN pecl install imagick redis && docker-php-ext-enable imagick redis

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory permissions
COPY --chown=www-data:www-data ./src /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Change current user to www
USER www-data

# Set working directory
WORKDIR /var/www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]