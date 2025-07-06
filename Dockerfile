FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo_mysql

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
