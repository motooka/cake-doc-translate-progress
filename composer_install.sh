#!/bin/bash

set -e
set -u
set -x

# install dependencies
cd /repository/webserver
yes | composer install

# set permissions
cd /repository/webserver/bin
chmod 755 cake

# exec migration
cd /repository/webserver
bin/cake migrations migrate
