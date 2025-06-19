FROM php:8.3-fpm-alpine AS php-system-setup

RUN apk --no-cache add \
    curl \
    git \
    linux-headers \
    icu-dev \
    zip \
    libzip-dev \
    unzip

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    intl \
    zip

RUN docker-php-ext-install opcache

RUN apk --no-cache add $PHPIZE_DEPS && \
    pecl install apcu \
    && docker-php-ext-enable apcu

RUN curl --remote-name https://curl.se/ca/cacert.pem \
    && mv cacert.pem /etc/ssl/certs/ca-certificates.crt

COPY --from=composer:2.8.9 /usr/bin/composer /usr/local/bin/composer

FROM php-system-setup AS webserver-setup

RUN apk add --update --no-cache \
    nginx \
    supervisor

COPY ./docker/main/default.conf /etc/nginx/http.d/default.conf
COPY ./docker/main/supervisord.conf /etc/supervisord.conf

FROM webserver-setup AS app-setup

COPY . /app
RUN chown -R nginx:nginx /app

COPY ./docker/main/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER nginx
WORKDIR /app

RUN composer install --prefer-dist --no-scripts --no-dev --optimize-autoloader

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
