FROM php:8.2-fpm

ARG USER_ID=1000
ARG GROUP_ID=1000

# Создание группы и пользователя
#RUN groupadd -g ${GROUP_ID} appuser && \
#    useradd -u ${USER_ID} -g appuser -m appuser

RUN apt-get update && apt-get install -y \
	curl \
	wget \
	unzip \
	libfreetype6-dev \
	libjpeg62-turbo-dev \
    libwebp-dev \
    libpng-dev \
	libxslt-dev \
	libicu-dev \
	libmcrypt-dev \
	libxml2-dev \
	libzip-dev \
	libonig-dev \
	libpq-dev \
	poppler-utils \
	ffmpeg \
	sox \
    libldap2-dev \
    ldap-utils

RUN docker-php-ext-configure ldap
RUN docker-php-ext-install ldap

RUN docker-php-ext-install -j$(nproc) iconv mbstring mysqli pdo_mysql pgsql pdo_pgsql zip calendar opcache
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl xsl soap exif zip pcntl
RUN docker-php-ext-install sockets

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD ./php.ini /usr/local/etc/php/php.ini

RUN pecl install redis swoole xdebug
RUN docker-php-ext-enable redis pcntl xdebug \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log_level = 0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode = debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey = \"PHPSTORM\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Переключение на созданного пользователя
#USER appuser

