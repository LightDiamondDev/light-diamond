FROM php:8.3-fpm

# Install all necessary packets and extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    npm \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    webp \
    libavif-bin && \
    pecl config-set php_ini "${PHP_INI_DIR}/php.ini" && \
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
      pdo_pgsql \
      bcmath \
      gd \
      zip && \
    npm install -g svgo && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# Set the uid, gid and home directory of the www-data user to default values
RUN usermod --uid 1000 www-data && \
    groupmod --gid 1000 www-data && \
    usermod -d /home/www-data www-data && \
    mkdir /home/www-data && \
    chown -R www-data:www-data /home/www-data

USER www-data
WORKDIR /var/www
