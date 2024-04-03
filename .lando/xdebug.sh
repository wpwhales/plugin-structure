#!/bin/bash

if [ "$#" -ne 1 ]; then
  mode=off
  export XDEBUG_MODE="off"
  rm -rf /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
  pkill -o -USR2 php-fpm
  /etc/init.d/apache2 reload
  echo "Xdebug is turned off."
  echo "Use 'lando xdebug <mode>' to load Xdebug in the selected mode."
  echo "Valid modes: https://xdebug.org/docs/all_settings#mode."
else
  mode="$1"
  export XDEBUG_MODE="$1"
  docker-php-ext-enable xdebug
  echo xdebug.mode = "$mode" > /usr/local/etc/php/conf.d/zzz-xxx-xdebug.ini
  pkill -o -USR2 php-fpm
  /etc/init.d/apache2 reload
  echo "Xdebug is loaded in "$mode" mode."
fi