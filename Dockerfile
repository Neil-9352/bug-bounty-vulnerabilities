FROM php:8.2-apache

# Install MySQL and PHP MySQL extension
RUN apt-get update && apt-get install -y mariadb-server && \
    docker-php-ext-install mysqli

# Start MySQL and Apache together
COPY ./start.sh /start.sh
RUN chmod +x /start.sh

# Copy project files
COPY . /var/www/html

# Expose port 80 for web access
EXPOSE 80

# Start script
CMD ["/start.sh"]
