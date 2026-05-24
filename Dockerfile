FROM php:8.3-apache

# 1. Instalar dependencias del sistema
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
    npm && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Instalar extensiones PHP
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

# 3. Habilitar mod_rewrite para Laravel
RUN a2enmod rewrite

# 4. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Configurar directorio de trabajo
WORKDIR /var/www/html

# 6. Copiar archivos de dependencias (Aprovecha la caché de capas)
COPY package*.json ./
COPY composer.json composer.lock ./

# 7. Instalar dependencias backend y frontend
RUN npm install
RUN composer install --no-dev --optimize-autoloader

# 8. Copiar TODO el resto del proyecto
COPY . .

# 9. Build de Vite para producción (Ahora sí escanea las vistas reales)
RUN npm run build

# 10. Configuración avanzada de Apache para Laravel
# Apuntar la raíz a public/
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# PERMITIR .HTACCESS: Esto evita que Apache ignore las rutas y los assets de Laravel
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 11. Configurar Puertos para Render
EXPOSE 10000
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# 12. Permisos correctos para que Apache pueda escribir y leer los assets compilados
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache public

# 13. Limpiar optimizaciones previas locales
RUN php artisan optimize:clear

CMD ["apache2-foreground"]
