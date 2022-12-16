FROM ubuntu:18.04

USER root

# Add package sources
RUN apt-get update && apt-get install -y gnupg2
RUN echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu bionic main" >> /etc/apt/sources.list
RUN echo "deb-src http://ppa.launchpad.net/ondrej/php/ubuntu bionic main" >> /etc/apt/sources.list
RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C
RUN apt-get update

# Install Php
RUN apt-get install -y php8.2-fpm \
php8.2-curl \
    php8.2-intl \
    php8.2-mbstring \
    php8.2-mysql \
    php8.2-opcache \
    php8.2-readline \
    php8.2-soap \
    php8.2-tidy \
    php8.2-xml \
    php8.2-zip

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
