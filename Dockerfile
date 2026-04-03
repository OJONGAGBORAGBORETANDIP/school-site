# Build stage - compile assets with Node.js
FROM node:22-alpine as node_builder

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

# Extract vendor.zip so Vite can find Livewire Flux CSS
RUN if [ -f vendor.zip ]; then unzip -q vendor.zip && rm vendor.zip; fi

# Build Vite assets
RUN npm run build

# PHP builder stage
FROM php:8.3-apache as php_builder

WORKDIR /var/www/html

# Install all required system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    mariadb-client \
    git \
    curl \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-configure intl && \
    docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    gd \
    bcmath \
    intl \
    zip \
    opcache

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . .

# Extract vendor.zip if it exists
RUN if [ -f vendor.zip ]; then unzip -q vendor.zip && rm vendor.zip; fi

# Install PHP dependencies (production only)
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts 2>&1 || true

# Production stage
FROM php:8.3-apache

WORKDIR /var/www/html

# Install runtime dependencies only
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq5 \
    mariadb-client \
    libfreetype6 \
    libjpeg62-turbo \
    libpng6 \
    libicu76 \
    libonig5 \
    libzip4 \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    gd \
    bcmath \
    intl \
    zip \
    opcache

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy PHP configuration
COPY --from=php_builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Copy Laravel files from PHP builder
COPY --from=php_builder --chown=www-data:www-data /var/www/html /var/www/html

# Copy compiled assets from Node builder
COPY --from=node_builder --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configure Apache for Laravel
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n\
<IfModule mod_rewrite.c>\n\
    <IfModule mod_negotiation.c>\n\
        Options -MultiViews\n\
    </IfModule>\n\
    RewriteEngine On\n\
    RewriteCond %{REQUEST_FILENAME} -d [OR]\n\
    RewriteCond %{REQUEST_FILENAME} -f\n\
    RewriteRule ^ ^ [L]\n\
    RewriteRule ^ /index.php [L]\n\
</IfModule>' > /etc/apache2/conf-available/laravel.conf && \
    a2enconf laravel && \
    sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Create .env from .env.example if it doesn't exist
RUN if [ ! -f /var/www/html/.env ]; then cp /var/www/html/.env.example /var/www/html/.env; fi

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start Apache
CMD ["apache2-foreground"]