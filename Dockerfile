# ==============================================================================
# DARK MATTER — Elite Fitness Production Docker Image
# PHP 8.3 + Apache + MySQL PDO + GD + Security Hardening
# ==============================================================================

FROM php:8.3-apache-bookworm

LABEL maintainer="DARK MATTER Infrastructure Team <ops@darkmatter-fitness.com>"
LABEL description="Production Web & Telemetry Container for DARK MATTER Gym"

# Set non-interactive environment
ENV DEBIAN_FRONTEND=noninteractive

# Install operating system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    ca-certificates \
    curl \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        gd \
        fileinfo \
        mbstring \
        opcache \
        intl \
        zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable required Apache modules
RUN a2enmod rewrite headers deflate expires

# Configure Apache virtual host
RUN sed -ri -e 's!/var/www/html!/var/www/html!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure production PHP settings
COPY docker/php.ini $PHP_INI_DIR/conf.d/darkmatter-production.ini

# Set working directory
WORKDIR /var/www/html

# Copy application source code
COPY . /var/www/html/

# Copy and setup entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create required directories and set safe ownership
RUN mkdir -p /var/www/html/uploads/gallery \
             /var/www/html/uploads/trainers \
             /var/www/html/backups \
             /var/www/html/certs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/uploads /var/www/html/backups

# Ensure Aiven CA certificate has safe read permissions and is registered in OS trust store
RUN if [ -f /var/www/html/certs/ca.pem ]; then \
        chmod 644 /var/www/html/certs/ca.pem && \
        cp /var/www/html/certs/ca.pem /usr/local/share/ca-certificates/aiven-ca.crt && \
        update-ca-certificates; \
    fi

# Expose standard web port
EXPOSE 80

# Health check configuration
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/healthz.php || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
