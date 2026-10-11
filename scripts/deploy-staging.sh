#!/usr/bin/env bash
set -e

# shortcut: assumes local docker compose installation; upgrade to swarm or k8s if scaling beyond single node
echo "=== Starting Staging Deployment ==="

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$SCRIPT_DIR")"
cd "$ROOT_DIR"

if [ ! -f .env ]; then
    echo "Error: .env file missing in $ROOT_DIR. Please create it before deploying."
    exit 1
fi

echo "--> [1/5] Building & starting containers..."
docker compose -f docker-compose.staging.yml pull || true
docker compose -f docker-compose.staging.yml build
docker compose -f docker-compose.staging.yml up -d --remove-orphans

echo "--> [2/5] Running database migrations..."
docker compose -f docker-compose.staging.yml exec -T app php artisan migrate --force

echo "--> [3/5] Caching routes and configuration..."
docker compose -f docker-compose.staging.yml exec -T app php artisan config:cache
docker compose -f docker-compose.staging.yml exec -T app php artisan route:cache

echo "--> [4/5] Restarting queue workers..."
docker compose -f docker-compose.staging.yml exec -T app php artisan queue:restart

echo "--> [5/5] Verifying application health endpoint..."
docker compose -f docker-compose.staging.yml exec -T app curl -fsS http://localhost/up

echo "=== Staging Deployment Successfully Complete! ==="
