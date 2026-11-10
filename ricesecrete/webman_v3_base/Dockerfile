FROM php:8.2-cli-alpine

RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories \
    && apk add --no-cache \
        $PHPIZE_DEPS \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis pcntl \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize

RUN mkdir -p runtime/logs public/uploads \
    && chmod -R 777 runtime public/uploads

EXPOSE 8787

CMD ["php", "start.php", "start"]
