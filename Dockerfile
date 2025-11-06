# Dockerfile pour Codyssey (Symfony + React/Webpack Encore)

FROM php:8.3-fpm-alpine AS php_base

# Installation des extensions PHP nécessaires
RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    zip \
    libzip-dev \
    && docker-php-ext-install \
    pdo_pgsql \
    pdo_mysql \
    intl \
    opcache \
    zip

# Configuration PHP pour production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# ============================================
# Stage: dependencies (cache layer)
# ============================================
FROM php_base AS dependencies

# Copie des fichiers de dépendances
COPY composer.json composer.lock symfony.lock ./

# Installation des dépendances PHP (sans scripts ni autoload)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# ============================================
# Stage: assets (build frontend)
# ============================================
FROM node:20-alpine AS assets

WORKDIR /app

# Copie des vendor depuis le stage dependencies (needed for Symfony UX assets)
COPY --from=dependencies /app/vendor /app/vendor

# Copie des fichiers package
COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* ./

# Installation de pnpm/npm et des dépendances
RUN if [ -f pnpm-lock.yaml ]; then \
        npm install -g pnpm && pnpm install --frozen-lockfile; \
    elif [ -f yarn.lock ]; then \
        yarn install --frozen-lockfile; \
    else \
        npm ci; \
    fi

# Copie des fichiers nécessaires pour le build
COPY webpack.config.js postcss.config.mjs* ./
COPY assets/ ./assets/

# Build des assets pour production
RUN if [ -f pnpm-lock.yaml ]; then \
        pnpm run build; \
    elif [ -f yarn.lock ]; then \
        yarn run build; \
    else \
        npm run build; \
    fi

# ============================================
# Stage: production
# ============================================
FROM php_base AS production

# Copie de l'app
COPY . /app

# Copie des vendor depuis le stage dependencies
COPY --from=dependencies /app/vendor /app/vendor

# Copie des assets compilés
COPY --from=assets /app/public/build /app/public/build

# Génération de l'autoloader optimisé
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# Création des répertoires et permissions
RUN mkdir -p /app/var/cache /app/var/log && \
    chown -R www-data:www-data /app/var

# Création du cache Symfony (warmup) sans connexion DB
# Utilise une fausse DATABASE_URL pour éviter la connexion pendant le build
RUN DATABASE_URL="sqlite:///:memory:" php bin/console cache:clear --env=prod --no-debug && \
    DATABASE_URL="sqlite:///:memory:" php bin/console cache:warmup --env=prod --no-debug && \
    chown -R www-data:www-data /app/var

# Expose port PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
