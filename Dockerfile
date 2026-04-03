FROM php:8.3-apache

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq5 \
    mariadb-client \
    git \
    curl \
    unzip \
    libfreetype6 \
    libjpeg62-turbo \
    libpng16 \
    libicu76 \
    libonig5 \
    libzip5 \
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

# Copy project files
COPY . .

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

# Start Apache
CMD ["apache2-foreground"]