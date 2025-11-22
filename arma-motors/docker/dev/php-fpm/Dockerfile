FROM php:8.0-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    nano \
    mc \
    wget \
    bash \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libvpx-dev \
    zlib1g-dev \
    libicu-dev \
    libxpm-dev \
    libzip-dev \
    libmemcached-dev \
    libmagickwand-dev \
    g++

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Imagic
RUN pecl install imagick

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN docker-php-ext-install pdo pdo_pgsql opcache zip intl
RUN docker-php-ext-enable imagick

# Redis
RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# Change TimeZone
RUN echo "Set default timezone - Europe/Kiev"
RUN echo "Europe/Kiev" > /etc/timezone

COPY ./dev/php/conf.d/php.ini /usr/local/etc/php/php.ini

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet \
    && rm -rf /root/.composer/cache

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log_level = 0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode = debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey = \"PHPSTORM\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /app
