FROM php:8.1.22-apache

RUN docker-php-ext-install mysqli

# X-Powered-By kendu
RUN echo "expose_php=Off" > /usr/local/etc/php/conf.d/security.ini \
 && echo "display_errors=Off" >> /usr/local/etc/php/conf.d/security.ini \
 && echo "log_errors=On" >> /usr/local/etc/php/conf.d/security.ini


RUN a2enmod rewrite headers


RUN sed -i 's/^ServerTokens .*/ServerTokens Prod/' /etc/apache2/conf-available/security.conf || true \
 && sed -i 's/^ServerSignature .*/ServerSignature Off/' /etc/apache2/conf-available/security.conf || true

#goiburuak kendu
RUN printf '%s\n' '<IfModule mod_headers.c>' \
                  '  Header always unset X-Powered-By' \
                  '  Header always unset X-AspNet-Version' \
                  '  Header always unset X-AspNetMvc-Version' \
                  '</IfModule>' > /etc/apache2/conf-available/remove-powered-by.conf \
 && a2enconf remove-powered-by

RUN sed -i 's/Options Indexes FollowSymLinks/Options -Indexes +FollowSymLinks/' /etc/apache2/apache2.conf || true


COPY src/ /var/www/html/


RUN chown -R www-data:www-data /var/www/html \
 && find /var/www/html -type f -exec chmod 640 {} \; \
 && find /var/www/html -type d -exec chmod 750 {} \;

HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD curl -fsS http://localhost/ || exit 1

EXPOSE 80
