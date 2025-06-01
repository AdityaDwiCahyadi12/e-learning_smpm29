FROM php:8.2-cli

RUN apt-get update && apt-get install -y unzip zip curl default-mysql-client \
    && docker-php-ext-install pdo_mysql

COPY . /app
WORKDIR /app

CMD php -S 0.0.0.0:9000 -t .