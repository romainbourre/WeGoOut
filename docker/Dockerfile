FROM ubuntu:18.04

USER root

RUN apt-get update
RUN apt-get install -y software-properties-common
RUN apt-get update
RUN add-apt-repository ppa:ondrej/php
RUN apt-get update
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
RUN apt-get install -y nginx
RUN apt-get install -y curl
RUN apt-get install -y sqlite3
RUN apt-get install -y php-sqlite3
RUN apt-get install -y vim

# SSL CERTIFICATES PACKAGE
RUN apt-get update
RUN apt-get install software-properties-common
RUN add-apt-repository universe
RUN add-apt-repository ppa:certbot/certbot
RUN apt-get update
RUN apt-get install -y \
    certbot \
    python-certbot-nginx

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
