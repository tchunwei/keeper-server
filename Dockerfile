FROM php:7.0-apache

RUN mkdir -p /var/www/html
WORKDIR /var/www/html

RUN cd /var/www/html

# Install dependencies (requirements)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql \
    && apt-get clean

COPY site.conf /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod rewrite && service apache2 restart

EXPOSE 80