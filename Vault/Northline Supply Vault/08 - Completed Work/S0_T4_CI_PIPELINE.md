# Completed Work: S0-T4 CI Pipeline

## Overview
- **Task ID**: `S0-T4`
- **Title**: CI pipeline
- **Priority**: `#p1-should`
- **Work Type**: `#dev`
- **Sprint**: Sprint 0
- **Estimate**: 1.0d
- **Status**: Completed

## Objectives Achieved
1. **GitHub Actions CI Workflow (`.github/workflows/ci.yml`)**:
   - Implemented automated multi-job pipeline triggered on pushes and pull requests targeting the `main` branch.
   - Configured concurrency groups to auto-cancel superseded in-progress pipeline runs.
   - Enforced least-privilege token permissions (`contents: read`, `packages: write`).
2. **Frontend Quality Job (`frontend`)**:
   - Executes on Node.js 22 with npm dependency caching.
   - Runs ESLint across the codebase using project ignore definitions (`Vault`, `vendor`, `.agents` excluded).
   - Runs TypeScript static typecheck (`tsc --noEmit`).
3. **Backend Test & Validation Job (`backend`)**:
   - Executes PHP 8.3 with required extensions (`pdo`, `pdo_sqlite`, `pdo_pgsql`, `pgsql`, `bcmath`, `pcntl`, `zip`, `redis`).
   - Validates `composer.json` strictly.
   - Caches composer dependencies across runs.
   - Installs composer dependencies and executes automated PHPUnit feature/unit test suites (`php artisan test`).
4. **Containerization & GHCR Publication Job (`docker`)**:
   - Depends on both `frontend` and `backend` test jobs completing successfully.
   - Sets up Docker Buildx and GitHub Actions cache backend (`type=gha`).
   - Pull request verification: builds the Docker image without pushing to validate Dockerfile integrity.
   - Main branch deployment: logs in to GitHub Container Registry (`ghcr.io`) using `GITHUB_TOKEN` and publishes the self-contained container image tagged with short commit SHA (`sha-<hash>`) and `latest`.
5. **Self-Contained Production Docker Packaging**:
   - Updated `Dockerfile` to bundle application source code into `/var/www/html`.
   - Pre-creates writeable storage and bootstrap cache directories before composer post-autoload scripts run.
   - Pre-installs optimized production dependencies (`composer install --no-dev --optimize-autoloader`).
   - Updated `.dockerignore` to cleanly omit vendor, git, agents, local secrets, and CI files from container image context.

## Verification
- Verified ESLint passes with 0 errors (`npm run lint`).
- Verified TypeScript typecheck passes with 0 errors (`npm run typecheck`).
- Verified local Docker build creates self-contained image successfully.
- Verified container runtime: `docker run --rm northwind-test php artisan --version` executes cleanly.

## Related Files
- `.github/workflows/ci.yml`
- `Dockerfile`
- `.dockerignore`
- `.eslintignore`
- `package.json`
- `tsconfig.json`
- `scripts/sync-kanban.js`
- `Vault/Northline Supply Vault/11 - Specs/IMPLEMENTATION_PLAN.md` (S0-T4)
- `Vault/Northline Supply Vault/11 - Specs/TRD.md` (Hosting & CI/CD)
