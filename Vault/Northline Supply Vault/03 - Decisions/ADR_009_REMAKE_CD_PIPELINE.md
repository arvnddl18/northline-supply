# ADR 009: Remake Staging Continuous Deployment (CD) Pipeline

## Context
The staging Continuous Deployment workflow (`.github/workflows/deploy-staging.yml`) consistently failed within 8–9 seconds on GitHub Actions.
Diagnostic root causes identified:
1. **GitHub Actions Expression Boolean Coercion**: The expressions `${{ secrets.STAGING_PORT || 22 }}` and `${{ secrets.STAGING_PATH || '/var/www/northwind' }}` coerced empty/unset secrets to boolean string `"true"`, crashing Go string-to-int parsing in `appleboy/ssh-action` (`strconv.Atoi("true")`).
2. **Missing Pre-Flight Secrets Validation**: Incomplete or missing repository/environment secrets (`STAGING_HOST`, `STAGING_USER`, `STAGING_SSH_KEY`) caused opaque instant SSH client crashes without actionable diagnostic guidance.
3. **Missing GitHub Environment Binding**: The workflow did not bind to `environment: staging`, blocking access to GitHub Environment Secrets and preventing deployment URL tracking in GitHub releases/PRs.
4. **Fragile Remote Git Dependency on VPS**: The script attempted `git fetch origin main` on the remote server, requiring SSH deploy keys or personal access tokens on the VPS.
5. **Private GHCR Pull Failure**: The remote VPS Docker daemon was unauthenticated to `ghcr.io`, triggering `401 Unauthorized` during image pulls.

## Decision
In accordance with Ponytail Principles (minimal code, zero bloat, highest reliability) and Definition of Done:
1. **Pre-Flight Validation**: Introduce an explicit `preflight` job checking for `STAGING_HOST`, `STAGING_USER`, and `STAGING_SSH_KEY`, emitting actionable `::error::` annotations pointing engineers directly to GitHub Settings if secrets are missing.
2. **GitHub Environment Binding**: Bind jobs to `environment: staging` with URL tracking (`url: https://${{ vars.STAGING_DOMAIN || 'staging.northwindceramics.com.au' }}`).
3. **Native OpenSSH Automation via `webfactory/ssh-agent`**: Replace `appleboy/ssh-action` with official `webfactory/ssh-agent@v0.9.0` and native OpenSSH (`ssh` / `scp`) with `-o StrictHostKeyChecking=accept-new`.
4. **Direct Artifact Sync via SCP**: Sync `docker/Caddyfile` and `scripts/deploy-staging.sh` directly from CI runner to the VPS. The remote VPS no longer requires Git credentials or repository checkouts.
5. **Dynamic GHCR Authentication**: Pipe the runner's ephemeral `GITHUB_TOKEN` to `docker login ghcr.io -u $ACTOR --password-stdin` on the remote VPS before pulling the container image.
6. **Robust Shell Defaults**: Use standard POSIX shell fallback expansions (`${CUSTOM_PORT:-22}`) in runner steps rather than GitHub Actions boolean operators in YAML parameters.
7. **Safe Pull Resolution in Deploy Script**: Updated `scripts/deploy-staging.sh` to halt cleanly with clear instructions if image pull fails, avoiding RAM exhaustion from local build fallbacks on resource-constrained staging nodes.
8. **Graceful Degradation on Unconfigured Secrets**: When triggered automatically via `workflow_run` (push to `main`), unconfigured VPS secrets emit an informative GitHub Actions `::warning::` and skip deployment cleanly with exit code 0 (`ready=false`), keeping `main` green and healthy while alerting developers on how to activate CD. When triggered manually via `workflow_dispatch`, it fails fast with `exit 1` to alert the operator.

## Status
Accepted / Active

## Date
2026-10-12

## Consequences
- Eliminates 8-second crash caused by boolean coercion and missing secrets.
- Decouples the VPS from Git hosting credentials; VPS functions purely as a Docker container runtime host.
- Full deployment history and environment URL tracking visible in GitHub Actions UI.
- Deterministic rollback capability by passing any tagged Docker image (`sha-xxxxxxx` or `latest`) via manual `workflow_dispatch`.
