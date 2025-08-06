# syntax=docker/dockerfile:1
ARG PHP_VERSION=8.2
FROM docker.io/library/php:${PHP_VERSION}-fpm

LABEL "language"="php"
LABEL "framework"="laravel"

ENV APP_ENV=prod
ENV APP_DEBUG=false
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

WORKDIR /var/www

RUN apt update && apt install -y \
  cron curl git libicu-dev nginx pkg-config unzip \
  && rm -rf /var/www/html \
  && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
  && apt install -y nodejs \
  && rm -rf /var/lib/apt/lists/*

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

# Install all PHP extensions -- note: 'mongodb' is required
RUN install-php-extensions @composer apcu bcmath gd intl mbstring mysqli opcache pcntl pdo_mysql sysvsem zip exif fileinfo sockets xml xmlwriter xmlreader curl mongodb

RUN cat <<'EOF' > /etc/nginx/sites-enabled/default
server {
    listen 8080;
    index index.php index.html;
    root /var/www/public;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }
    error_log /dev/stderr;
    access_log /dev/stderr;
}
EOF

RUN git clone --single-branch --branch develop https://github.com/toskabnk/N16Fin_Back.git .

RUN [ -f .env ] || cp .env.example .env

RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

RUN composer install --optimize-autoloader --no-dev --no-ansi --no-interaction -vvv

RUN npm install
RUN npm run build

RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

RUN chown -R www-data:www-data /var/www

EXPOSE 8080

CMD nginx; php-fpm;