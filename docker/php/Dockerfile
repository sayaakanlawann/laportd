FROM php:8.3-fpm

# 1. Menginstal alat-alat dasar yang dibutuhkan oleh Laravel dan PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# 2. Mengambil Composer (Manajer paket untuk PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Menentukan lokasi folder tempat kita bekerja nanti di dalam Docker
WORKDIR /var/www