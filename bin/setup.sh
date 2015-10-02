#!/usr/bin/env bash

export SYMFONY__MYSQL__HOST=$MYSQL_PORT_3306_TCP_ADDR
mkdir -p /tmp/sf2
chown -R www-data:www-data /tmp/sf2

php /var/www/PhotoWorld/app/console doctrine:schema:create