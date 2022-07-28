#!/bin/sh

set -e
echo "clear_env = no" >> /etc/php/8.1/fpm/pool.d/www.conf
/etc/init.d/php8.1-fpm start && nginx
tail -f application.log