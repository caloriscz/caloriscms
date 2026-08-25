FROM php:7.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        default-mysql-client \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd intl mbstring mysqli pdo_mysql zip \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/caloris-local.ini
COPY docker/php/docker-entrypoint.sh /usr/local/bin/caloris-entrypoint
COPY docker/php/reset-admin-password.sh /usr/local/bin/reset-admin-password

RUN chmod +x /usr/local/bin/caloris-entrypoint /usr/local/bin/reset-admin-password

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --prefer-dist --no-interaction --no-progress

ENTRYPOINT ["caloris-entrypoint"]
CMD ["apache2-foreground"]
