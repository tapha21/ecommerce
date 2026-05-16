FROM php:8.2-apache

# =========================
# Dependencies
# =========================
RUN apt-get update && apt-get install -y \
    git \
    unzip \
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
# Composer
# =========================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =========================
# Workdir
# =========================
WORKDIR /var/www/html

# =========================
# Copy project
# =========================
COPY . .

# =========================
# Install dependencies
# =========================
RUN composer install --no-dev --optimize-autoloader --no-interaction

# =========================
# Symfony permissions
# =========================
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 var

# =========================
# Apache config (public/)
# =========================
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# =========================
# Render PORT support
# =========================
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

EXPOSE 80

CMD ["sh", "-c", "apache2-foreground"]