FROM php:8.2-apache

# Set document root to public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install system dependencies
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        curl \
        git \
        unzip \
        vim \
        nano; \
    rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-configure intl && \
    docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_mysql \
        zip \
        intl \
        mbstring \
        opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure PHP for production
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=60'; \
        echo 'opcache.fast_shutdown=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

RUN { \
        echo 'memory_limit=256M'; \
        echo 'upload_max_filesize=10M'; \
        echo 'post_max_size=10M'; \
        echo 'max_execution_time=60'; \
        echo 'error_reporting=E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED'; \
        echo 'display_errors=Off'; \
        echo 'log_errors=On'; \
        echo 'error_log=/var/log/apache2/php_errors.log'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# Enable Apache modules
RUN a2enmod rewrite expires headers deflate

# Configure Apache for security and performance
RUN { \
        echo 'ServerTokens Prod'; \
        echo 'ServerSignature Off'; \
        echo 'Header always set X-Content-Type-Options nosniff'; \
        echo 'Header always set X-Frame-Options DENY'; \
        echo 'Header always set X-XSS-Protection "1; mode=block"'; \
        echo 'Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"'; \
    } > /etc/apache2/conf-available/security.conf && \
    a2enconf security

# Create necessary directories
RUN mkdir -p /var/www/html/logs /var/www/html/assets/qr && \
    chown -R www-data:www-data /var/www/html/logs /var/www/html/assets

# Working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=www-data:www-data . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/logs /var/www/html/assets

EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

