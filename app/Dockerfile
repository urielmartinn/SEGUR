# 
FROM php:8.1-apache
# 
RUN docker-php-ext-install mysqli
# 
COPY src/ /var/www/html/
# Beridazketa
RUN a2enmod rewrite
# Baimenak
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
