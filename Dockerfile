# Production image: React is built once, no dev tools, Apache serves the app.

# Step 1: build the React frontend
FROM node:22-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js tailwind.config.js postcss.config.js jsconfig.json ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# Step 2: PHP + Apache with the app inside
FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache serves the public folder, and .htaccess needs mod_rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel \
    && a2enmod rewrite

WORKDIR /var/www/html

# PHP packages without dev tools. The code is copied after, so this step stays cached
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
COPY --from=assets /app/public/build ./public/build
# Tests can't run without the dev packages, so they are left out of this image
RUN rm -rf tests \
    && composer dump-autoload --no-dev --optimize

# The SQLite file lives in /data, which compose.yaml turns into a volume so it survives restarts
RUN mkdir -p /data \
    && touch /data/database.sqlite \
    && chown -R www-data:www-data /data storage bootstrap/cache

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    QUEUE_CONNECTION=sync \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/data/database.sqlite

EXPOSE 80

# On start: update the tables (as www-data, so it owns the database), cache the config, run Apache
CMD ["sh", "-c", "runuser -u www-data -- php artisan migrate --force && php artisan config:cache && apache2-foreground"]
