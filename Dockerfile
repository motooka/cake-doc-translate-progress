FROM ubuntu:24.04

RUN DEBIAN_FRONTEND=noninteractive

RUN apt-get update
RUN apt-get upgrade -y

# see https://askubuntu.com/questions/909277/avoiding-user-interaction-with-tzdata-when-installing-certbot-in-a-docker-contai
RUN apt-get install -y tzdata

# CakePHP 5.x minimum dependencies and minimum requirements for apps with PHP / Composer
RUN apt-get install -y apache2 php8.3 php8.3-mbstring php8.3-intl php8.3-xml ca-certificates zip composer

# for this app
RUN apt-get install -y sqlite3 php8.3-sqlite3 git

# for debugging
RUN apt-get install -y vim less sqlite3


# composer
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY composer_install.sh /
RUN chmod 755 /composer_install.sh

# apache setting
COPY site.conf /etc/apache2/conf-enabled/
RUN cd /etc/apache2/mods-enabled; ln -s ../mods-available/rewrite.load ./

# document root
RUN rm -rf /var/www/html
RUN cd /var/www; ln -s /repository/webserver ./html


# the process
CMD ["apachectl", "-D", "FOREGROUND"]
