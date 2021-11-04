FROM php:5.6-apache

ENV APP_ENV docker
ENV APACHE_DOCUMENT_ROOT /kim_online/online

COPY ./ /kim_online/online

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -i -e 's/<FilesMatch \\.php$>/<FilesMatch \\.(php|php5)$>/'  /etc/apache2/conf-available/docker-php.conf
RUN sed -i -e 's/DirectoryIndex index.php index.html/DirectoryIndex index.php index.php5 index.html/' /etc/apache2/conf-available/docker-php.conf
