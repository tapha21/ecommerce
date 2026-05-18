FROM php:8.2-apache

# --------------------------------------------------
# Dépendances système + extensions PHP nécessaires
# --------------------------------------------------
RUN apt-get update \
    && apt-get install -y \
        git \
        unzip \
        curl \
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
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# Installation de Composer
# --------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --------------------------------------------------
# Dossier de travail
# --------------------------------------------------
WORKDIR /var/www/html

# --------------------------------------------------
# Copie du projet (avec .env inclus)
# --------------------------------------------------
COPY . .

# --------------------------------------------------
# Définir l’environnement PROD pour éviter DebugBundle
# --------------------------------------------------
ENV APP_ENV=prod
ENV APP_DEBUG=0

# --------------------------------------------------
# Création des dossiers Symfony nécessaires
# --------------------------------------------------
RUN mkdir -p var/cache var/log public/uploads

# --------------------------------------------------
# Installation des dépendances PHP (prod uniquement)
# --no-dev : pas de DebugBundle, pas de WebProfilerBundle, etc.
# --optimize-autoloader : optimisation en prod
# --------------------------------------------------
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# --------------------------------------------------
# Nettoyage du cache Symfony
# --------------------------------------------------
RUN rm -rf var/cache/*

# --------------------------------------------------
# Permissions
# --------------------------------------------------
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 var public/uploads

# --------------------------------------------------
# Configuration Apache pour Symfony (/public)
# --------------------------------------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# --------------------------------------------------
# Exposer le port 80 (Render s’occupe du mapping)
# --------------------------------------------------
EXPOSE 80

# --------------------------------------------------
# Lancement du serveur Apache
# --------------------------------------------------
CMD ["apache2-foreground"]

RUN composer dump-autoload --optimize
RUN php bin/console cache:clear --env=prod || true
RUN php bin/console cache:warmup --env=prod || true