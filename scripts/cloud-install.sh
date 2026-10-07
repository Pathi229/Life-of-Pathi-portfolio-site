#!/usr/bin/env bash
set -euo pipefail
cd /workspace/Life-of-Pathi-portfolio-site
export DOCKER_CONFIG="${DOCKER_CONFIG:-/tmp/pathi-docker}"
pathi_build_args=(--network host --secret id=proxy_ca,src=/etc/ssl/certs/ca-certificates.crt --build-arg http_proxy --build-arg https_proxy --build-arg HTTP_PROXY --build-arg HTTPS_PROXY)
if pathi_proxy_ip="$(getent ahostsv4 proxy | awk 'NR==1{print $1}')" && [ -n "$pathi_proxy_ip" ]; then
  pathi_build_args+=(--add-host "proxy:$pathi_proxy_ip")
fi
docker build "${pathi_build_args[@]}" -t pathi-php -f docker/Dockerfile .
docker run --rm --network host --user "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp/composer -e http_proxy -e https_proxy -e HTTP_PROXY -e HTTPS_PROXY -v /etc/ssl/certs:/etc/ssl/certs:ro -v "$PWD:/app" -w /app pathi-php sh -eu -c '
  composer install --prefer-source --no-interaction
  if [ ! -f .env ]; then cp .env.example .env; php artisan key:generate; fi
  php artisan migrate --force
  php artisan db:seed --force
  php artisan filament:assets
'
npm ci --cache /tmp/pathi-npm --no-audit --no-fund
npm run build
