# syntax=docker/dockerfile:1

ARG COMPOSER_VERSION=latest
ARG composerFullPath=composer:${COMPOSER_VERSION}
ARG PHP_VERSION=8.4
ARG PHPImage=php:${PHP_VERSION}-zts
FROM ${composerFullPath} as composer_version
FROM ${PHPImage}

WORKDIR /var/www/wce

#copy composer
COPY --from=composer_version /usr/bin/composer /usr/bin/composer

# Install nano
RUN apt-get update && apt-get install -y nano && apt-get clean

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony
RUN apt-get update && apt-get install -y 

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
        unzip \
        libicu-dev \
        libzip-dev \
        zip \
        libonig-dev \
    && docker-php-ext-install mbstring intl zip \
    && pecl install parallel \
    && docker-php-ext-enable parallel \
    && rm -rf /var/lib/apt/lists/*

#ENTRYPOINT ["php"]
CMD ["tail", "-f", "/dev/null"]
