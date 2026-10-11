# Staging Deployment Runbook: VPS with Docker Compose & Caddy HTTPS

**Task**: [[IMPLEMENTATION_PLAN#S0-T5]] | Sprint 0  
**Status**: Active  
**Last Updated**: 2026-10-11  

---

## 1. Overview
The staging environment for Northwind Ceramics Payment App runs on an Ubuntu VPS using Docker Compose. All HTTP/HTTPS traffic is reverse-proxied through a containerized **Caddy v2** service, which automates Let's Encrypt TLS certificate provisioning and termination. Deployments are triggered automatically on every merge to `main` via GitHub Actions SSH.

---

## 2. Infrastructure Architecture
```
                               Internet
                                  │
                                  ▼
                     ┌─────────────────────────┐
                     │    Caddy (Port 80/443)  │  (Auto TLS Let's Encrypt)
                     └────────────┬────────────┘
                                  │ reverse_proxy app:80
                                  ▼
                     ┌─────────────────────────┐
                     │   Laravel 11 Web App    │  (PHP 8.3 FPM + Nginx)
                     └────────────┬────────────┘
                                  │
         ┌────────────────────────┼────────────────────────┐
         ▼                        ▼                        ▼
┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
│  Queue Worker    │    │  Task Scheduler  │    │ Redis / Postgres │
└──────────────────┘    └──────────────────┘    └──────────────────┘
```

---

## 3. GitHub Actions CI/CD Pipeline
File: `.github/workflows/deploy-staging.yml`

### Required GitHub Repository Secrets
To enable the automated deployment pipeline, add the following secrets in **GitHub > Settings > Secrets and variables > Actions**:

| Secret Name | Value Description | Example |
|---|---|---|
| `STAGING_HOST` | IPv4 or domain address of the VPS | `192.0.2.1` or `vps.northwindceramics.com` |
| `STAGING_USER` | Deploy user on the VPS (with Docker access) | `deploy` or `ubuntu` |
| `STAGING_SSH_KEY` | Ed25519 or RSA private SSH key | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `STAGING_PORT` | SSH port (optional, defaults to 22) | `22` |
| `STAGING_PATH` | Full directory path to the repo on the VPS | `/var/www/northwind` |

---

## 4. First-Time VPS Bootstrap Guide

### Step 1: Install Docker & Docker Compose v2 on Ubuntu VPS
```bash
sudo apt update && sudo apt install -y curl git ufw
curl -fsSL https://get.docker.com -o get-docker.sh && sudo sh get-docker.sh
sudo usermod -aG docker $USER
newgrp docker
```

### Step 2: Configure Firewall (UFW)
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Step 3: Clone Repository on VPS
```bash
sudo mkdir -p /var/www/northwind
sudo chown -R $USER:$USER /var/www/northwind
git clone https://github.com/northline-supply/northline-supply.git /var/www/northwind
cd /var/www/northwind
```

### Step 4: Configure Persistent Staging `.env`
```bash
cp .env.example .env
nano .env
```
Ensure the following are set for staging:
- `APP_ENV=staging`
- `APP_DEBUG=false`
- `APP_URL=https://staging.northwindceramics.com.au` (or your staging domain)
- `STAGING_DOMAIN=staging.northwindceramics.com.au`
- `ACME_EMAIL=admin@northwindceramics.com.au`
- Populate `SHOPIFY_API_KEY`, `SHOPIFY_API_SECRET`, `STRIPE_KEY`, `STRIPE_SECRET`, and database passwords.

### Step 5: Start the Staging Stack Manually
```bash
bash scripts/deploy-staging.sh
```

---

## 5. Deployment Step Breakdown (Automated on Merge)
Every push to `main` executes the 6-stage deployment sequence:
1. **Fetch & Reset**: `git fetch origin main && git reset --hard origin/main`
2. **Pull & Up**: `docker compose -f docker-compose.staging.yml up -d --remove-orphans`
3. **Database Migration**: `docker compose -f docker-compose.staging.yml exec -T app php artisan migrate --force`
4. **Cache Warmup**: `config:cache` and `route:cache`
5. **Worker Restart**: `php artisan queue:restart`
6. **Health Verification**: Probe `curl -fsS http://localhost/up` verifying HTTP 200.

---

## 6. Verification and Troubleshooting
- **Check Caddy Certificates & Logs**:
  ```bash
  docker compose -f docker-compose.staging.yml logs caddy -f
  ```
- **Check Application Healthcheck**:
  ```bash
  curl -i https://staging.northwindceramics.com.au/up
  ```
  Expected output: HTTP 200 with OK.
- **Rollback Procedure**:
  If a release fails healthcheck or exhibits critical regressions:
  ```bash
  git checkout <previous_known_good_commit_sha>
  bash scripts/deploy-staging.sh
  ```
