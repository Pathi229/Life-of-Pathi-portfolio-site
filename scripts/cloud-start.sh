#!/usr/bin/env bash
set -euo pipefail
cd /workspace/Life-of-Pathi-portfolio-site
export DOCKER_CONFIG="${DOCKER_CONFIG:-/tmp/pathi-docker}"
if docker container inspect pathi-app >/dev/null 2>&1; then
  docker start pathi-app >/dev/null
else
  docker run -d --name pathi-app --network host --user "$(id -u):$(id -g)" -v /etc/ssl/certs:/etc/ssl/certs:ro -v "$PWD:/app" -w /app pathi-php php artisan serve --host=127.0.0.1 --port=8000 >/dev/null
fi
for pathi_attempt in {1..20}; do
  if curl -fsS http://127.0.0.1:8000/ -o /tmp/pathi-readiness.html && rg -q 'Explore the tree' /tmp/pathi-readiness.html; then
    echo 'Life of Pathi public homepage is responding.'
    curl -fsS http://127.0.0.1:8000/admin/login -o /tmp/pathi-admin-readiness.html
    rg -q 'Sign in' /tmp/pathi-admin-readiness.html
    exit 0
  fi
  sleep 1
done
docker logs --tail 40 pathi-app
exit 1
