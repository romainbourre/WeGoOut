FROM ubuntu:18.04

USER root

# Add package sources
RUN apt-get update
RUN apt-get install -y software-properties-common
RUN add-apt-repository ppa:ondrej/php

# Install Php
RUN apt-get install -y php8.1-fpm \
php8.1-curl \
    php8.1-intl \
    php8.1-mbstring \
    php8.1-mysql \
    php8.1-opcache \
    php8.1-readline \
    php8.1-soap \
    php8.1-tidy \
    php8.1-xml \
    php8.1-zip

# Install Nginx and Certbot
RUN apt-get install -y nginx
RUN apt-get install -y \
    certbot \
    python-certbot-nginx

# Install Composer
RUN apt-get install -y curl
RUN curl -sS https://getcomposer.org/installer | php \
      && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

ENV nginx_vhost /etc/nginx/sites-available/default
ENV nginx_conf /etc/nginx/nginx.conf

ADD docker/config/default ${nginx_vhost}
ADD docker/config/nginx.conf ${nginx_conf}

RUN mkdir /var/www/outside

COPY ./composer.json /var/www/outside
COPY ./src/ /var/www/outside/src
COPY ./docker/config/entrypoint.sh /var/www/outside

WORKDIR /var/www/outside

RUN touch application.log

RUN composer install --no-dev --no-progress

RUN chown -R www-data:www-data /var/www/outside

RUN chmod 777 entrypoint.sh

ENTRYPOINT ["/bin/sh", "entrypoint.sh"]
