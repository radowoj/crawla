FROM php:8.2-alpine3.16
RUN apk add --no-cache $PHPIZE_DEPS
RUN pecl install pcov && \
  docker-php-ext-enable pcov
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app