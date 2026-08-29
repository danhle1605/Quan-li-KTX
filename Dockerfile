FROM php:8.2-apache

# Cài đặt các thư viện hệ thống cần thiết và PHP extensions (pdo_mysql, gd)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd

# Bật Apache mod_rewrite cho routing clean URL
RUN a2enmod rewrite

# Thay đổi Apache DocumentRoot sang /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Cấp quyền thư mục web
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
