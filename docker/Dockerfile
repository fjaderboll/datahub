FROM php:7.4-apache

RUN ln -s ../mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load

COPY html/ /var/www/html/
RUN chown www-data:www-data /var/www/html/api/data

VOLUME /var/www/html/api/data
