FROM php:8.4-cli-alpine

LABEL maintainer="Telmo Rafael <sloth.dev.guy@gmail.com>"

RUN apk add --no-cache $PHPIZE_DEPS git zip unzip \
    && apk add --update linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del --no-cache $PHPIZE_DEPS \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && touch /var/database.sqlite

#WORKDIR /app

#COPY . /app

ENTRYPOINT ["docker-php-entrypoint"]
