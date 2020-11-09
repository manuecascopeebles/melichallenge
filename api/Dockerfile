FROM php:7.2-apache

RUN apt-get update && apt-get install -y git curl git unzip zip openssl libxml2-dev
RUN apt-get install -y default-mysql-client
RUN docker-php-ext-install pdo mbstring pdo_mysql
RUN docker-php-ext-enable pdo mbstring pdo_mysql


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .
RUN composer install

COPY docker/apache2/sites-enabled /etc/apache2/sites-enabled
COPY docker/apache2/apache2.conf /etc/apache2/apache2.conf

COPY docker/docker-php-entrypoint /usr/local/bin/

RUN a2enmod rewrite
RUN a2enmod headers
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN service apache2 restart

RUN chmod 755 /usr/local/bin/docker-php-entrypoint

RUN chmod 777 /var/www/html/storage/*


WORKDIR /var/www/html
RUN chmod 755 /var/www/html
