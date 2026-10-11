.PHONY: help build up down restart logs migrate test shell tinker clean

help: ## Show this help message
	@echo "Usage: make [target]"
	@echo ""
	@echo "Targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Build Docker application image
	docker build -t northwind-payment-app:latest .

up: ## Start containers in background using pure Docker
	@powershell -ExecutionPolicy Bypass -File scripts/stack.ps1 up

down: ## Stop and remove containers
	@powershell -ExecutionPolicy Bypass -File scripts/stack.ps1 down

restart: down up ## Restart containers

logs: ## Tail web container logs
	docker logs -f northwind-payment-web

migrate: ## Run database migrations
	docker exec -it northwind-payment-web php artisan migrate

test: ## Run automated PHPUnit tests inside container
	docker exec -it northwind-payment-web php artisan test

shell: ## Open shell inside app container
	docker exec -it northwind-payment-web sh

tinker: ## Open Laravel Tinker shell
	docker exec -it northwind-payment-web php artisan tinker

clean: down ## Stop containers and clean data volumes
	@powershell -ExecutionPolicy Bypass -File scripts/stack.ps1 clean
