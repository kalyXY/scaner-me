FROM php:8.2-apache

# Set document root to public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install required extensions: pdo_mysql and gd for QR code PNGs
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends libpng-dev libjpeg-dev libfreetype6-dev curl git unzip; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j$(nproc) gd pdo pdo_mysql; \
    curl -sS https://getcomposer.org/installer -o composer-setup.php; \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer; \
    rm composer-setup.php; \
    rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite expires headers

# Working directory
WORKDIR /var/www/html

