# Use the official PHP 8.3 FPM image as the base image
FROM php:8.3-fpm

# Install dependencies required for PDO MySQL
RUN apt-get update && apt-get install -y \
    && docker-php-ext-install pdo pdo_mysql

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy any additional configuration files if necessary (optional)
# COPY php.ini /usr/local/etc/php/

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
