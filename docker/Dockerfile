FROM php:8.4-apache

#PARAMETERS
ARG APCU_VERSION=$APCU_VERSION

#INI FILE
COPY ./opcache.ini "$PHP_INI_DIR/conf.d/docker-php-ext-opcache.ini"
COPY ./xdebug.ini "$PHP_INI_DIR/conf.d/99-xdebug.ini"
COPY ./000-default.conf /etc/apache2/sites-enabled/000-default.conf
#INI

#INSTALL DEPENDENCIES AND ENABLE PHP MODULES
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
    procps nano git unzip wget sudo cron supervisor

RUN apt-get install -y \
    libicu-dev \
    zlib1g-dev \
    libxml2 \
    libxml2-dev \
    libreadline-dev \
    libzip-dev \
    librabbitmq-dev \
    libonig-dev

RUN pecl install amqp && docker-php-ext-enable amqp

RUN docker-php-ext-install pdo_mysql mysqli

RUN docker-php-ext-configure intl && docker-php-ext-install intl

RUN docker-php-ext-install zip

RUN docker-php-ext-install opcache sockets bcmath

#INSTALL APCU
RUN pecl install apcu-${APCU_VERSION} && docker-php-ext-enable apcu
#APCU

#INSTALL XDEBUG
RUN pecl install xdebug && docker-php-ext-enable xdebug
#XDEBUG

#INSTALL COMPOSER
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -sS https://get.symfony.com/cli/installer | bash && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
#COMPOSER

# INSTALL NODE
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs