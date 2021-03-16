FROM php:7.4-cli

RUN docker-php-ext-install bcmath

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
RUN echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=172.17.0.1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "done"

COPY --from=composer /usr/bin/composer /usr/bin/composer
