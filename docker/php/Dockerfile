FROM php:7.4-apache
RUN cd /usr/bin && curl -s http://getcomposer.org/installer | php && ln -s /usr/bin/composer.phar /usr/bin/composer
RUN apt-get update \
    && apt-get install -y zlib1g-dev \
    && apt-get install -y libzip-dev \
    && apt-get install -y unzip
RUN docker-php-ext-install pdo_mysql zip
ENV COMPOSER_HOME /composer
ENV PATH $PATH:/composer/vendor/bin
RUN composer require jwilsson/spotify-web-api-php