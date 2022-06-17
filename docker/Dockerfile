FROM php:8.1-cli-alpine

LABEL maintainer="Telmo Rafael <sloth.dev.guy@gmail.com>"

RUN apk add --no-cache $PHPIZE_DEPS git zip unzip \
    && pecl install xdebug-3.1.2 \
    && docker-php-ext-enable xdebug \
    && apk del --no-cache $PHPIZE_DEPS \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && touch /var/database.sqlite

#WORKDIR /app

#COPY . /app

ENTRYPOINT ["docker-php-entrypoint"]
