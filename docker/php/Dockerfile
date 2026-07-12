# syntax=docker/dockerfile:1.7
# =============================================================================
# SocialJobs — Dockerfile multi-stage (PHP 8.3 FPM Alpine)
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1: Composer (dependências PHP de produção)
# -----------------------------------------------------------------------------
FROM composer:2.9 AS composer_builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --ignore-platform-req=ext-gd \
        --ignore-platform-req=ext-intl \
        --ignore-platform-req=ext-bcmath \
        --ignore-platform-req=ext-exif \
        --ignore-platform-req=ext-pcntl \
        --ignore-platform-req=ext-redis

# Copia código-fonte para gerar autoloader otimizado
COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts

# -----------------------------------------------------------------------------
# Stage 2: Node (build de assets Vite)
# -----------------------------------------------------------------------------
FROM node:22-alpine AS node_builder

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci --no-audit --no-fund

COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js ./

RUN npm run build

# -----------------------------------------------------------------------------
# Stage 3: Runtime (PHP-FPM Alpine)
# -----------------------------------------------------------------------------
FROM php:8.3-fpm-alpine AS runtime

# Metadados
LABEL org.opencontainers.image.title="SocialJobs"
LABEL org.opencontainers.image.description="Runtime PHP-FPM para SocialJobs"
LABEL org.opencontainers.image.source="https://github.com/DavidWendel-Dev/socialjobs"

# Dependências de sistema e ferramentas de build (removidas depois)
RUN set -eux; \
    apk add --no-cache \
        bash \
        curl \
        git \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libwebp \
        freetype \
        libzip \
        oniguruma \
        libintl \
        tini \
        supervisor \
        nodejs \
        npm \
    ; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        autoconf \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        freetype-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
        linux-headers \
    ; \
    docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype ; \
    docker-php-ext-configure intl ; \
    docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        gd \
        intl \
        zip \
        bcmath \
        exif \
        pcntl \
        opcache \
    ; \
    pecl install redis ; \
    docker-php-ext-enable redis ; \
    apk del .build-deps ; \
    rm -rf /tmp/* /var/cache/apk/*

# Configuração PHP e Opcache (com preload)
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-socialjobs.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/preload.php /var/www/preload.php

# Composer binário (útil em runtime para manutenção)
COPY --from=composer_builder /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copia vendor otimizado do stage 1
COPY --from=composer_builder /app/vendor ./vendor

# Copia assets construídos do stage 2
COPY --from=node_builder /app/public/build ./public/build

# Copia o restante do código-fonte
COPY . .

# Permissões: www-data já existe na imagem base
RUN set -eux; \
    mkdir -p storage/framework/{cache,sessions,testing,views} storage/logs bootstrap/cache ; \
    chown -R www-data:www-data /var/www ; \
    chmod -R 775 storage bootstrap/cache

# Supervisor config e entrypoint
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www-data

EXPOSE 9000

ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]
