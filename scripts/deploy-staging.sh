#!/usr/bin/env bash
set -euo pipefail

# shortcut: pure docker single-node deploy; upgrade to swarm or k8s if scaling beyond one node
# Runs the same topology as scripts/stack.ps1 (local): web + worker + scheduler from ONE app image,
# plus postgres, redis and the caddy reverse proxy. CI runs this exact script as its smoke test.
#
# Usage: DOCKER_IMAGE=ghcr.io/<owner>/<repo>:sha-abc1234 bash scripts/deploy-staging.sh
#        (DOCKER_IMAGE unset = build the image from this checkout)
echo "=== Starting Staging Deployment via Pure Docker ==="

cd "$(dirname "${BASH_SOURCE[0]}")/.."
ROOT_DIR="$(pwd)"

if [ ! -f .env ]; then
    echo "Error: .env file missing in $ROOT_DIR. Please create it before deploying."
    exit 1
fi

env_get() { grep -E "^$1=" .env | tail -n1 | cut -d= -f2- | tr -d '"' || true; }

IMAGE="${DOCKER_IMAGE:-northwind-payment-app:staging}"
NETWORK="northwind-staging-net"
P="northwind-staging"
DB_NAME="$(env_get DB_DATABASE)"; DB_NAME="${DB_NAME:-northwind_payment}"
DB_USER="$(env_get DB_USERNAME)"; DB_USER="${DB_USER:-postgres}"
DB_PASS="$(env_get DB_PASSWORD)"; DB_PASS="${DB_PASS:-postgres}"
DOMAIN="$(env_get STAGING_DOMAIN)"; DOMAIN="${DOMAIN:-localhost}"
ACME="$(env_get ACME_EMAIL)"; ACME="${ACME:-admin@northwindceramics.com.au}"

echo "--> [1/6] Resolving application image: $IMAGE"
if [ -z "${DOCKER_IMAGE:-}" ]; then
    docker build -t "$IMAGE" .
elif ! docker image inspect "$IMAGE" >/dev/null 2>&1; then
    docker pull "$IMAGE" || (echo "Notice: Could not pull $IMAGE from registry, building locally..." && docker build -t "$IMAGE" .)
fi

docker network inspect "$NETWORK" >/dev/null 2>&1 || docker network create "$NETWORK" >/dev/null
docker rm -f staging-app >/dev/null 2>&1 || true # legacy single-container deploy held port 80

echo "--> [2/6] Ensuring Postgres & Redis are running (data persists across deploys)..."
if [ -z "$(docker ps -q -f "name=^/${P}-postgres$")" ]; then
    docker rm -f "${P}-postgres" >/dev/null 2>&1 || true
    docker run -d --name "${P}-postgres" --network "$NETWORK" --network-alias postgres --restart unless-stopped \
        -e POSTGRES_DB="$DB_NAME" -e POSTGRES_USER="$DB_USER" -e POSTGRES_PASSWORD="$DB_PASS" \
        -v "${P}_postgres_data:/var/lib/postgresql/data" postgres:16-alpine >/dev/null
fi
if [ -z "$(docker ps -q -f "name=^/${P}-redis$")" ]; then
    docker rm -f "${P}-redis" >/dev/null 2>&1 || true
    docker run -d --name "${P}-redis" --network "$NETWORK" --network-alias redis --restart unless-stopped \
        -v "${P}_redis_data:/data" redis:7-alpine >/dev/null
fi
for i in $(seq 1 30); do
    # -h 127.0.0.1: the postgres init server only listens on the socket, so TCP readiness = truly ready
    if docker exec "${P}-postgres" pg_isready -h 127.0.0.1 -U "$DB_USER" -d "$DB_NAME" >/dev/null 2>&1 \
        && docker exec "${P}-redis" redis-cli ping >/dev/null 2>&1; then break; fi
    if [ "$i" -eq 30 ]; then echo "Error: Postgres/Redis did not become ready."; exit 1; fi
    sleep 2
done

# .env is mounted (not --env-file) so Laravel parses quotes itself; -e values override it.
APP_ARGS=(--network "$NETWORK"
    -v "$ROOT_DIR/.env:/var/www/html/.env:ro"
    -v "${P}_storage:/var/www/html/storage"
    -e APP_ENV=staging -e APP_DEBUG=false -e LOG_CHANNEL=stderr
    -e DB_HOST=postgres -e REDIS_HOST=redis)

echo "--> [3/6] Running database migrations with the new image (old release keeps serving if this fails)..."
docker run --rm "${APP_ARGS[@]}" "$IMAGE" php artisan migrate --force

echo "--> [4/6] Replacing web, worker & scheduler containers..."
docker stop -t 90 "${P}-worker" >/dev/null 2>&1 || true # let the in-flight job finish (matches --timeout=90)
docker rm -f "${P}-web" "${P}-worker" "${P}-scheduler" >/dev/null 2>&1 || true
docker run -d --name "${P}-web" --network-alias app --restart unless-stopped "${APP_ARGS[@]}" "$IMAGE" >/dev/null
docker run -d --name "${P}-worker" --restart unless-stopped "${APP_ARGS[@]}" "$IMAGE" \
    php artisan queue:work --tries=3 --timeout=90 >/dev/null
docker run -d --name "${P}-scheduler" --restart unless-stopped "${APP_ARGS[@]}" "$IMAGE" \
    php artisan schedule:work >/dev/null
docker exec "${P}-web" php artisan config:cache
docker exec "${P}-web" php artisan route:cache

echo "--> [5/6] Starting Caddy reverse proxy for $DOMAIN..."
docker rm -f "${P}-caddy" >/dev/null 2>&1 || true
docker run -d --name "${P}-caddy" --network "$NETWORK" --restart unless-stopped -p 80:80 -p 443:443 \
    -e STAGING_DOMAIN="$DOMAIN" -e ACME_EMAIL="$ACME" \
    -v "$ROOT_DIR/docker/Caddyfile:/etc/caddy/Caddyfile:ro" \
    -v "${P}_caddy_data:/data" -v "${P}_caddy_config:/config" caddy:2-alpine >/dev/null

echo "--> [6/6] Verifying health (/health checks DB, Redis and queue)..."
for i in $(seq 1 15); do
    if docker exec "${P}-web" curl -fsS http://localhost/health; then echo; break; fi
    if [ "$i" -eq 15 ]; then docker logs --tail 50 "${P}-web"; exit 1; fi
    sleep 2
done
for c in worker scheduler caddy; do
    if [ "$(docker inspect -f '{{.State.Running}}:{{.RestartCount}}' "${P}-$c")" != "true:0" ]; then
        echo "Error: ${P}-$c is not running cleanly."
        docker logs --tail 50 "${P}-$c"
        exit 1
    fi
done

docker image prune -f >/dev/null
echo "=== Staging Deployment Successfully Complete! ($IMAGE) ==="
