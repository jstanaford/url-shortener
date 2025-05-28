FROM php:8.4-fpm

# Install Composer (and extra PHP extensions if needed)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer (using Composer's installer)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node (and npm) (using NodeSource's setup script)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs


WORKDIR /var/www/html
