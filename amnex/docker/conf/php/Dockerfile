FROM amnex-base

RUN echo "extension = swoole.so" >> /usr/local/etc/php/php.ini

RUN pecl install redis swoole-5.1.1
RUN docker-php-ext-enable redis
