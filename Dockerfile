# Set our base image
FROM serversideup/php:8.3-fpm-nginx

ARG SSL_MODE
ENV SSL_MODE ${SSL_MODE:-on}

# Switch to root so we can do root things
USER root

# add additional extensions here:
RUN install-php-extensions \
    intl \
    mbstring \
    gd \
    exif \
    fileinfo \
    zip \
    opcache

# Drop back to our unprivileged user
USER www-data
