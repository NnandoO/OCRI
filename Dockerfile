FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install \
    gd \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl

# Habilitar rewrite Apache
RUN a2enmod rewrite

# Instalar Node.js 22
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos
COPY . .

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader

# Instalar dependencias Node
RUN npm install

# Compilar assets Vite
RUN npm run build

# Configuración Laravel
RUN cp .env.example .env

RUN php artisan key:generate

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Apache config
COPY apache.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
