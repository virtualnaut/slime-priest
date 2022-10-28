FROM php:8.1-fpm-alpine

# ENV PHPGROUP=www-data
# ENV PHPUSER=www-data

#RUN adduser -g ${PHPGROUP} -s /bin/sh -D ${PHPUSER}

ARG user
# ARG uid
ARG group

RUN adduser -g ${group} -s /bin/sh -D ${user}

# RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN sed -i "s/user = www-data/user = ${user}/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = ${group}/g" /usr/local/etc/php-fpm.d/www.conf

RUN mkdir -p /var/www/html/public

RUN set -ex \
  && apk --no-cache add \
    postgresql-dev

RUN docker-php-ext-install pdo pgsql pdo_pgsql

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]
