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

### Required GitHub Secrets & Variables
To enable automated deployments, configure the following secrets under **GitHub > Settings > Secrets and variables > Actions** (either as Repository Secrets or inside the `staging` Environment):

| Secret / Variable Name | Type | Value Description | Example |
|---|---|---|---|
| `STAGING_HOST` | Secret (Required) | IPv4 or domain address of the VPS | `192.0.2.1` or `vps.northwindceramics.com` |
| `STAGING_USER` | Secret (Required) | Deploy user on VPS (member of `docker` group) | `deploy` or `ubuntu` |
| `STAGING_SSH_KEY` | Secret (Required) | Ed25519 or RSA private SSH key | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `STAGING_PORT` | Secret (Optional) | SSH port (defaults to 22) | `22` |
| `STAGING_PATH` | Secret (Optional) | Full deployment path on VPS (defaults to `/var/www/northwind`) | `/var/www/northwind` |
| `STAGING_DOMAIN` | Variable (Optional) | Domain name mapped to staging Caddy proxy | `staging.northwindceramics.com.au` |

---

## 4. First-Time VPS Bootstrap Guide

### Step 1: Install Docker on Ubuntu VPS
```bash
sudo apt update && sudo apt install -y curl ufw
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

### Step 3: Create Application Directory on VPS
```bash
sudo mkdir -p /var/www/northwind
sudo chown -R $USER:$USER /var/www/northwind
cd /var/www/northwind
```

### Step 4: Configure Persistent Staging `.env`
```bash
nano /var/www/northwind/.env
```
Ensure the following are set for staging:
- `APP_ENV=staging`
- `APP_DEBUG=false`
- `APP_KEY=base64:...` (Generate with `docker run --rm northwind-payment-app:staging php artisan key:generate --show` or local artisan)
- `APP_URL=https://staging.northwindceramics.com.au`
- `STAGING_DOMAIN=staging.northwindceramics.com.au`
- `ACME_EMAIL=admin@northwindceramics.com.au`
- Populate `SHOPIFY_API_KEY`, `SHOPIFY_API_SECRET`, `STRIPE_KEY`, `STRIPE_SECRET`, and database passwords.

---

## 5. Deployment Step Breakdown (Automated on CI Success or Manual Dispatch)
Every merge to `main` (or manual `workflow_dispatch` trigger) executes the automated sequence:
1. **Pre-flight Validation**: Verifies that required secrets (`STAGING_HOST`, `STAGING_USER`, `STAGING_SSH_KEY`) exist with actionable error reporting if unset.
2. **Directory Bootstrapping**: Runner runs SSH command `mkdir -p $APP_DIR/docker $APP_DIR/scripts`.
3. **Artifact Sync via SCP**: Copies tested `docker/Caddyfile` and `scripts/deploy-staging.sh` directly from CI runner to VPS (VPS needs **zero git credentials**).
4. **GHCR Authentication**: Injects runner `GITHUB_TOKEN` into remote `docker login ghcr.io -u $ACTOR --password-stdin` to pull private packages safely.
5. **Pure Docker Deploy**: Executes `scripts/deploy-staging.sh` with target image:
   - Runs database migrations via temporary container (`php artisan migrate --force`). Old release stays active if migrations fail.
   - Replaces web, worker, and scheduler containers with zero downtime.
   - Updates Caddy proxy configuration and validates HTTPS.
   - Probes `/health` endpoint (database PDO, Redis ping, queue depth).
6. **External Health Probe**: Runner validates public healthcheck status via `curl -fsS https://$DOMAIN/health`.

---

## 6. Verification and Troubleshooting
- **Check Running Containers**:
  ```bash
  docker ps --filter name=northwind-staging
  ```
- **Check Caddy Certificates & Logs**:
  ```bash
  docker logs -f northwind-staging-caddy
  ```
- **Check Application Healthcheck**:
  ```bash
  docker exec northwind-staging-web curl -fsS http://localhost/health
  ```
- **Rollback Procedure**:
  Trigger GitHub Actions `Deploy Staging` with `workflow_dispatch` specifying the previous known good Docker image tag (e.g., `sha-a1256b7`).
