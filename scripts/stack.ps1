param(
    [Parameter(Position=0)]
    [ValidateSet("setup", "build", "up", "down", "restart", "logs", "migrate", "test", "shell", "tinker", "clean", "help")]
    [string]$Action = "help"
)

function Show-Help {
    Write-Host "==========================================================" -ForegroundColor Cyan
    Write-Host " Northwind Ceramics - Docker Stack Manager" -ForegroundColor Cyan
    Write-Host "==========================================================" -ForegroundColor Cyan
    Write-Host "Usage: .\scripts\stack.ps1 <command>" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Commands:"
    Write-Host "  setup    - First-time setup: build, launch, install packages, migrate" -ForegroundColor Green
    Write-Host "  build    - Build Docker images"
    Write-Host "  up       - Start containers in background"
    Write-Host "  down     - Stop and remove containers"
    Write-Host "  restart  - Restart containers"
    Write-Host "  logs     - Tail container logs"
    Write-Host "  migrate  - Run database migrations"
    Write-Host "  test     - Run automated PHPUnit tests inside container"
    Write-Host "  shell    - Open interactive shell in app container"
    Write-Host "  tinker   - Open Laravel Tinker shell"
    Write-Host "  clean    - Stop containers and remove volumes"
}

switch ($Action) {
    "setup" {
        Write-Host "==> Initializing Northwind Ceramics Docker Stack..." -ForegroundColor Cyan
        if (-not (Test-Path ".env")) {
            Copy-Item ".env.example" ".env"
            Write-Host "==> Created .env from .env.example" -ForegroundColor Green
        }
        Write-Host "==> Building Docker images..." -ForegroundColor Cyan
        docker compose build
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

        Write-Host "==> Starting containers..." -ForegroundColor Cyan
        docker compose up -d
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

        Write-Host "==> Running Composer install inside container..." -ForegroundColor Cyan
        docker compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader

        Write-Host "==> Generating application key..." -ForegroundColor Cyan
        docker compose exec app php artisan key:generate --force

        Write-Host "==> Running database migrations..." -ForegroundColor Cyan
        docker compose exec app php artisan migrate --force

        Write-Host "==> Stack ready! Web app available at: http://localhost:8000" -ForegroundColor Green
    }
    "build" {
        docker compose build
    }
    "up" {
        docker compose up -d
    }
    "down" {
        docker compose down
    }
    "restart" {
        docker compose down
        docker compose up -d
    }
    "logs" {
        docker compose logs -f
    }
    "migrate" {
        docker compose exec app php artisan migrate
    }
    "test" {
        docker compose exec app php artisan test
    }
    "shell" {
        docker compose exec app sh
    }
    "tinker" {
        docker compose exec app php artisan tinker
    }
    "clean" {
        docker compose down -v
    }
    default {
        Show-Help
    }
}
