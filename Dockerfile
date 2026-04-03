# Use official PHP image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and build tools
RUN apt-get update && apt-get install -y \
    build-essential \
    autoconf \
    automake \
    libtool \
    pkg-config \
    libpq-dev \
    mariadb-client \
    git \
    curl \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && rm -rf /var/lib/apt/lists/*

# Install oniguruma from source
RUN cd /tmp && \
    curl -L -o oniguruma.tar.gz https://github.com/kkos/oniguruma/releases/download/v6.9.8/oniguruma-6.9.8.tar.gz && \
    tar -xzf oniguruma.tar.gz && \
    cd oniguruma-6.9.8 && \
    ./configure && \
    make && \
    make install && \
    ldconfig && \
    cd /tmp && \
    rm -rf oniguruma*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    gd \
    bcmath

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Install PHP dependencies (if composer.json exists)
RUN if [ -f composer.json ]; then composer install --no-interaction --optimize-autoloader; fi

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]