FROM php:8.2-apache

# Install mysqli and pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli

# Make Apache listen on port 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf
RUN sed -i 's/:80>/:8080>/' /etc/apache2/sites-enabled/000-default.conf

# Copy all your PHP files into the web server root
COPY . /var/www/html/

# Give Apache the right permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080