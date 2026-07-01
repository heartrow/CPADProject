FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    unzip zip curl \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . .

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

EXPOSE 80

CMD ["sh", "-c", "php -S 0.0.0.0:$PORT -t public public/index.php"]