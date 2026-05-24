FROM php:8.3-apache

# 1. Instalar dependencias del sistema indispensables
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

# 2. Instalar extensiones PHP necesarias para Laravel y manipulación de archivos/imágenes
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

# 3. Habilitar mod_rewrite para el manejo de rutas de Laravel
RUN a2enmod rewrite

# 4. Instalar Composer desde la imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Configurar el directorio de trabajo dentro del contenedor
WORKDIR /var/www/html

# 6. Copiar archivos de dependencias primero para aprovechar la caché de capas de Docker
COPY package*.json ./
COPY composer.json composer.lock ./

# 7. Instalar dependencias frontend y backend (evitando scripts de Artisan que romperían el build)
RUN npm install
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 8. Copiar TODO el código del proyecto al contenedor
COPY . .

# 9. Regenerar el autoloader ejecutando ahora sí los scripts de Laravel (ya existe el archivo artisan)
RUN composer dump-autoload --optimize --no-dev

# 10. Compilar los assets frontend (Tailwind, Flux, etc.) para producción con Vite
RUN npm run build

# 11. Configuración de Apache para Laravel
# Apuntar la raíz del servidor web a la carpeta pública de Laravel
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# PERMITIR .HTACCESS: Indispensable para que Apache procese las rutas y no bloquee los estilos/assets
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 12. Configurar los puertos requeridos por Render
EXPOSE 10000
RUN sed -i 's/80/10000/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# 13. Asignar los permisos correctos al usuario de Apache (www-data)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache public

# 14. Limpiar cualquier caché local remanente
RUN php artisan optimize:clear

CMD ["apache2-foreground"]
