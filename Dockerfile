# Use the official PHP Apache image
FROM php:8.2-apache

# Copy all project files into the web root
COPY . /var/www/html/

# Enable Apache mod_rewrite (optional, for pretty URLs)
RUN a2enmod rewrite

# Expose port 80 to the outside world
EXPOSE 80
