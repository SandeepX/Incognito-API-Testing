# syntax=docker/dockerfile:1

# ---------- Stage 1: build frontend assets ----------
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* .npmrc ./
RUN npm install --ignore-scripts
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---------- Stage 2: install PHP dependencies (no dev) ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ---------- Stage 3: runtime (PHP 8.3 + Apache) ----------
FROM php:8.3-apache

# Laravel needs a couple of extra extensions (pdo_sqlite/sqlite3/mbstring/curl ship enabled)
RUN docker-php-ext-install pdo_mysql

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev postgresql-client default-mysql-client sqlite3 \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

# App layout: DocumentRoot must be /var/www/html/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf 2>/dev/null || true; \
    printf '<VirtualHost *:80>\n    DocumentRoot /var/www/html/public\n    <Directory /var/www/html/public>\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>\n' \
    > /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# App source
COPY --from=vendor /app/vendor ./vendor
COPY . /var/www/html

# Frontend build output (public/build is produced by the node stage)
COPY --from=frontend /app/public/build ./public/build

# Bash-less entrypoint needs sh compat
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh && ln -sf /usr/local/bin/entrypoint.sh /entrypoint.sh

# Runtime-writable bits must exist at runtime, not build; default ownership is root-inaccessible paths:
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/testing storage/framework/views bootstrap/cache database \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R ug+rwX storage bootstrap/cache database

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
