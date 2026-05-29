FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        supervisor \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath pcntl gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

RUN mkdir -p /var/run/php \
    && echo "[www]\nlisten = 0.0.0.0:9000\nlisten.mode = 0666" > /usr/local/etc/php-fpm.d/zz-custom.conf

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/eldorado-entrypoint
RUN chmod +x /usr/local/bin/eldorado-entrypoint

ENTRYPOINT ["/usr/local/bin/eldorado-entrypoint"]
CMD ["php-fpm"]
