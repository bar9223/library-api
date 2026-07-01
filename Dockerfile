FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev git unzip postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --no-progress

COPY . .

RUN composer dump-autoload --no-dev --optimize \
    && mkdir -p var \
    && chown -R www-data:www-data var

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
