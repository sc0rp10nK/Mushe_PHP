FROM php:7.4-apache
RUN apt-get update \
    && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev libmcrypt-dev \
    && mv /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled
RUN apt-get install -y vim
RUN docker-php-ext-install pdo_mysql
RUN /bin/sh -c a2enmod rewrite