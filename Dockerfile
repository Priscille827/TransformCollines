FROM php:8.2-apache

# Installer les extensions PHP nécessaires pour Laravel et MySQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Activer le module de réécriture d'URL d'Apache
RUN a2enmod rewrite

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier le projet
COPY . .

# Installer les dépendances Composer
RUN composer install --no-dev --optimize-autoloader

# Installer les dépendances Node et compiler le front (Vite)
RUN npm install && npm run build

# Changer le dossier racine d'Apache vers /public (Laravel)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Donner les permissions au dossier storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exposer le port 80 (Apache)
EXPOSE 80

# Démarrer Apache
CMD ["apache2-foreground"]