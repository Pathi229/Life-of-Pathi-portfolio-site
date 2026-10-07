FROM composer:2@sha256:af98f42dfff7c68ba8d53c2164fd9fde1087b7d449514baa38c418b1f6bc4bac AS composer
FROM php:8.4-cli-bookworm@sha256:836ac6c672d1372a47c8fd61b625015bd07760f493fb2eaab2087636498a2b4b
RUN --mount=type=secret,id=proxy_ca,required=false if [ -f /run/secrets/proxy_ca ]; then cp /run/secrets/proxy_ca /etc/ssl/certs/ca-certificates.crt; fi; sed -i "s|http://deb.debian.org|https://deb.debian.org|g" /etc/apt/sources.list.d/debian.sources && apt-get update && apt-get install -y libicu-dev libonig-dev libxml2-dev libzip-dev libsqlite3-dev unzip git && docker-php-ext-install intl mbstring dom zip pdo_sqlite && rm -rf /var/lib/apt/lists/*
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
