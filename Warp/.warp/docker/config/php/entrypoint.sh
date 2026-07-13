#!/bin/bash

apt-get update && apt-get install -y gosu

if ! php -m | grep -q '^ftp$'; then
  apt-get install -y libzip-dev libssl-dev && \
  docker-php-ext-install ftp
fi

# Llamá al entrypoint original o al supervisord
exec gosu www-data supervisord
