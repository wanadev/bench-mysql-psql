FROM php:5.6-fpm

RUN apt-get update \
	&& apt-get install -y \
		libpq-dev \
		php5-cli \
		php5-fpm \
		php5-intl \
		php5-mysqlnd \
	&& docker-php-ext-install mysqli \
	&& docker-php-ext-install pdo_mysql \ 
	&& docker-php-ext-install pdo_pgsql \ 
	&& docker-php-ext-install pgsql