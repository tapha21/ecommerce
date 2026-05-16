FROM php:8.2-apache

# =========================
# SYSTEM DEPENDENCIES
# =========================
RUN apt-get update && apt-get install -y \
    git unzip curl \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_pgsql \
        zip \
        gd \
    && a2enmod rewrite

# =========================
# COMPOSER
# =========================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =========================
# WORKDIR
# =========================
WORKDIR /var/www/html

# =========================
# COPY PROJECT
# =========================
COPY . .

# =========================
# INSTALL PROD DEPENDENCIES ONLY
# (IMPORTANT: no scripts to avoid DebugBundle crash)
# =========================
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# =========================
# SYMFONY CACHE SAFE CLEAN
# =========================
RUN rm -rf var/cache/*

# =========================
# PERMISSIONS
# =========================
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 var

# =========================
# APACHE CONFIG (Symfony PUBLIC)
# =========================
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# =========================
# RENDER PORT SUPPORT
# =========================
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]