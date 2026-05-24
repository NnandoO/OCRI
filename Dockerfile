FROM php:8.3-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    nodejs \
    npm

# Instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio Laravel
WORKDIR /var/www/html

# Copiar package primero (mejor cache)
COPY package*.json ./

# Instalar dependencias frontend
RUN npm install

# Copiar composer
COPY composer.json composer.lock ./

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Copiar TODO el proyecto
COPY . .

# Build Vite para producción
RUN npm run build

# Limpiar caches Laravel
RUN php artisan optimize:clear

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Apache apuntando a public/
RUN sed -i 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf

# Puerto Render
EXPOSE 10000

# Apache usando puerto Render
RUN sed -i 's/80/10000/g' \
    /etc/apache2/ports.conf \
    /etc/apache2/sites-available/000-default.conf

CMD ["apache2-foreground"]
