FROM php:8.1-alpine3.16

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer