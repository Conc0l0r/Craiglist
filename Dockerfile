FROM php:8.2-apache

# Install mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Disable conflicting MPM modules
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Make Apache listen on port 8080
ENV PORT=8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-enabled/000-default.conf

# Copy files
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080

CMD ["apache2-foreground"]