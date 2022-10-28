FROM composer:2

ENV COMPOSERUSER=laravel
ENV COMPOSERGROUP=laravel

RUN adduser ${COMPOSERGROUP} -s /bin/sh -D ${COMPOSERUSER}
