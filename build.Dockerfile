FROM arquivei/php:7.4-cli-alpine

RUN apk update \
    && apk add autoconf build-base \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.max_nesting_level=15000" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
    && echo "xdebug.remote_enable=true" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
    && echo "xdebug.remote_host=localhost" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
    && echo "xdebug.idekey=PHPSTORM" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
    && echo "xdebug.remote_handler=dbgp" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
    && echo "xdebug.remote_autostart=1" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini" \
    && echo "xdebug.remote_connect_back=0" >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini"

RUN curl https://getcomposer.org/download/1.10.15/composer.phar -o /usr/local/bin/composer -LR -z /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer
