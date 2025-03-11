FROM php:8.2-apache

# Enable MySQL extension
RUN docker-php-ext-install mysqli

# Copy your files into the container
COPY . /var/www/html

# Expose port 80 for web access
EXPOSE 80

# Automatically run SQL script every time the container starts
# COPY reset_comments.sql /docker-entrypoint-initdb.d/