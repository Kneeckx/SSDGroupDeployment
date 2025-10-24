# Dockerfile for DIWA - Updated for Linode Deployment
FROM php:8.1-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite

# Enable Apache modules
RUN a2enmod rewrite

# Copy application files
COPY ./app /var/www/html
COPY ./docs /var/www/docs
COPY ./database /var/www/database

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create and set database permissions
RUN mkdir -p /var/www/html/database \
    && chown -R www-data:www-data /var/www/html/database \
    && chmod 777 /var/www/html/database

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]