FROM php:8.2-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libgmp-dev

# Extensiones de PHP
# - gd con WebP: miniaturas de las portadas.
# - bcmath y gmp: aceleran el cifrado de las notificaciones push.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql gd bcmath gmp

# Instalar Node.js + npm (LTS)
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - \
    && apt-get install -y nodejs

# Habilitar mod_rewrite para Laravel
RUN a2enmod rewrite

# Configurar carpeta de trabajo
WORKDIR /var/www/html

# Copiar proyecto
COPY . .

# Instalar dependencias de npm
RUN npm install && npm run build

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copiar configuración de Apache
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]
