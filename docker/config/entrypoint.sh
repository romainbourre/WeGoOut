#!/bin/sh

set -e
echo "clear_env = no" >> /etc/php/8.2/fpm/pool.d/www.conf
/etc/init.d/php8.2-fpm start && nginx
tail -f application.log
