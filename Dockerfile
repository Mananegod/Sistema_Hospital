FROM php:8.3-apache

# ------------------------------------------------------------------
# Dependencias del sistema y extensiones PHP
# ------------------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libxml2-dev \
        libonig-dev \
        zip \
        unzip \
        git \
        curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_pgsql \
        pgsql \
        bcmath \
        gd \
        zip \
        xml \
        mbstring \
        opcache \
        exif \
        pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ------------------------------------------------------------------
# Configuración de Apache
# ------------------------------------------------------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN a2enmod rewrite headers expires \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# ------------------------------------------------------------------
# Composer
# ------------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ------------------------------------------------------------------
# Código de la aplicación
# ------------------------------------------------------------------
COPY . /var/www/html

RUN chmod +x /var/www/html/entrypoint.sh

# ------------------------------------------------------------------
# Dependencias PHP
#   --no-scripts porque durante el build no hay .env y package:discover
#   podría fallar; lo ejecutamos en runtime desde entrypoint.sh
# ------------------------------------------------------------------
ENV COMPOSER_MEMORY_LIMIT=-1
RUN composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --prefer-dist \
        --no-progress \
        --no-scripts \
    && composer dump-autoload --optimize --no-dev

# ------------------------------------------------------------------
# Permisos
# ------------------------------------------------------------------
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/var/www/html/entrypoint.sh"]