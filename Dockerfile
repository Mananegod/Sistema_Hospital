
FROM node:18 AS frontend-build
WORKDIR /app
COPY . .
RUN npm install && npm run build


FROM php:8.2-apache
RUN apt-get update && apt-get install -y \
    libpq-dev unzip curl \
    && docker-php-ext-install pdo pdo_pgsql
RUN a2enmod rewrite


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


COPY . /var/www/html

COPY --from=frontend-build /app/public/build /var/www/html/public/build

WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache


ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80
CMD ["apache2-foreground"]