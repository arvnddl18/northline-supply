.PHONY: help setup build up down restart logs migrate test shell tinker clean staging-up staging-down staging-logs staging-deploy

help: ## Show this help message
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

setup: ## First-time setup: copies .env, builds images, starts containers, installs dependencies, runs migrations
	@if [ ! -f .env ]; then cp .env.example .env; echo "Created .env from .env.example"; fi
	docker compose build
	docker compose up -d
	docker compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader
	docker compose exec app php artisan key:generate --force
	docker compose exec app php artisan migrate --force
	@echo "Setup complete! Application running at http://localhost:8000"

build: ## Build or rebuild Docker images
	docker compose build

up: ## Start containers in the background
	docker compose up -d

down: ## Stop and remove containers, networks
	docker compose down

restart: down up ## Restart all containers

logs: ## Follow logs from all containers
	docker compose logs -f

migrate: ## Run database migrations
	docker compose exec app php artisan migrate

test: ## Run automated PHPUnit tests
	docker compose exec app php artisan test

shell: ## Open a shell inside the app container
	docker compose exec app sh

tinker: ## Open Laravel Tinker interactive shell
	docker compose exec app php artisan tinker

clean: ## Stop containers and remove volumes
	docker compose down -v

staging-up: ## Start staging containers in the background with Caddy HTTPS
	docker compose -f docker-compose.staging.yml up -d

staging-down: ## Stop staging containers
	docker compose -f docker-compose.staging.yml down

staging-logs: ## Follow logs from all staging containers
	docker compose -f docker-compose.staging.yml logs -f

staging-deploy: ## Run local or VPS staging deployment script
	bash scripts/deploy-staging.sh

monitoring-up: ## Start optional self-hosted Uptime Kuma monitoring container
	docker compose -f docker-compose.monitoring.yml up -d

monitoring-down: ## Stop self-hosted Uptime Kuma monitoring container
	docker compose -f docker-compose.monitoring.yml down
