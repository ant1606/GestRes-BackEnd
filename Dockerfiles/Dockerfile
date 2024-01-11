FROM php:8.1-apache

ARG UID
ARG GID

ENV UID=${UID:-1000}
ENV GID=${GID:-1000}

# 1. Install development packages and clean up apt cache.
RUN apt-get update && apt-get install -y \
  curl \
  g++ \
  git \
  libbz2-dev \
  libfreetype6-dev \
  libicu-dev \
  libjpeg-dev \
  libmcrypt-dev \
  libpng-dev \
  libreadline-dev \
  sudo \
  unzip \
  zip \
  && rm -rf /var/lib/apt/lists/*

# 2. Apache configs + document root.
#RUN echo "ServerName laravel-app.local" >> /etc/apache2/apache2.conf
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 3. mod_rewrite for URL rewrite and mod_headers for .htaccess extra headers like Access-Control-Allow-Origin-
RUN a2enmod rewrite headers

# 4. Start with base PHP config, then add extensions.
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# RUN docker-php-ext-install \
#   bcmath \
#   bz2 \
#   calendar \
#   iconv \
#   intl \
#   mbstring \
#   opcache \
#   pdo_mysql \
#   zip

RUN docker-php-ext-install pdo pdo_mysql
# RUN docker-php-ext-install zip

# 5. Composer.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. We need a user with the same UID/GID as the host user
# MacOS staff group's gid is 20, so is the dialout group in alpine linux. We're not using it, let's just remove it.
RUN delgroup dialout

RUN addgroup --gid ${GID} --system laravel
RUN adduser --gid ${GID} --system -u ${UID} laravel

USER laravel

WORKDIR /var/www/html

COPY ./../* /var/www/html/
RUN rm -rf ./Dockerfiles ./tests ./vendor ./.idea
# RUN rm ./.editorconfig ./.env.development ./.env.example ./.env.production ./.phpunit.result.cache ./docker-compose.prod.yml ./docker-compose.yml ./phpunit.xml ./README.md