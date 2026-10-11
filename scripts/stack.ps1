param(
    [Parameter(Position=0)]
    [ValidateSet("setup", "build", "up", "down", "restart", "logs", "migrate", "test", "shell", "tinker", "clean", "help")]
    [string]$Action = "help"
)

$network = "northwind-net"
$web = "northwind-payment-web"
$worker = "northwind-payment-worker"
$scheduler = "northwind-payment-scheduler"
$postgres = "northwind-postgres"
$redis = "northwind-redis"

function Ensure-Network {
    $exists = docker network ls --filter name=^$network$ --format "{{.Name}}"
    if (-not $exists) {
        docker network create $network | Out-Null
    }
}

function Show-Help {
    Write-Host "==========================================================" -ForegroundColor Cyan
    Write-Host " Northwind Ceramics - Docker Stack Manager (Pure Docker)" -ForegroundColor Cyan
    Write-Host "==========================================================" -ForegroundColor Cyan
    Write-Host "Usage: .\scripts\stack.ps1 <command>" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Commands:"
    Write-Host "  up       - Start containers in background using pure Docker" -ForegroundColor Green
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
    "up" {
        Ensure-Network
        $root = (Get-Item -Path ".").FullName.Replace("\", "/")

        # Postgres
        if (-not (docker ps -q -f name=^/$postgres$)) {
            docker rm -f $postgres 2>$null | Out-Null
            docker run -d --name $postgres --network $network -e POSTGRES_DB=northwind_payment -e POSTGRES_USER=postgres -e POSTGRES_PASSWORD=postgres -v northline-supply_postgres_data:/var/lib/postgresql/data -p 5432:5432 postgres:16-alpine | Out-Null
        }

        # Redis
        if (-not (docker ps -q -f name=^/$redis$)) {
            docker rm -f $redis 2>$null | Out-Null
            docker run -d --name $redis --network $network -v northline-supply_redis_data:/data -p 6379:6379 redis:7-alpine | Out-Null
        }

        # Web
        if (-not (docker ps -q -f name=^/$web$)) {
            docker rm -f $web 2>$null | Out-Null
            docker run -d --name $web --network $network -p 8000:80 -v "${root}:/var/www/html" -v northline-supply_app_vendor:/var/www/html/vendor -e APP_ENV=local -e DB_HOST=$postgres -e REDIS_HOST=$redis northwind-payment-app:latest | Out-Null
        }

        # Worker
        if (-not (docker ps -q -f name=^/$worker$)) {
            docker rm -f $worker 2>$null | Out-Null
            docker run -d --name $worker --network $network -v "${root}:/var/www/html" -v northline-supply_app_vendor:/var/www/html/vendor -e APP_ENV=local -e DB_HOST=$postgres -e REDIS_HOST=$redis northwind-payment-app:latest php artisan queue:work --tries=3 --timeout=90 | Out-Null
        }

        # Scheduler
        if (-not (docker ps -q -f name=^/$scheduler$)) {
            docker rm -f $scheduler 2>$null | Out-Null
            docker run -d --name $scheduler --network $network -v "${root}:/var/www/html" -v northline-supply_app_vendor:/var/www/html/vendor -e APP_ENV=local -e DB_HOST=$postgres -e REDIS_HOST=$redis northwind-payment-app:latest php artisan schedule:work | Out-Null
        }

        Write-Host "==> Stack online via Docker: http://localhost:8000" -ForegroundColor Green
    }
    "down" {
        docker rm -f $web $worker $scheduler $postgres $redis 2>$null | Out-Null
        Write-Host "==> Stack containers stopped and removed" -ForegroundColor Green
    }
    "restart" {
        & $PSCommandPath down
        & $PSCommandPath up
    }
    "logs" {
        docker logs -f $web
    }
    "migrate" {
        docker exec -it $web php artisan migrate
    }
    "test" {
        docker exec -t $web php artisan test
    }
    "shell" {
        docker exec -it $web sh
    }
    "tinker" {
        docker exec -it $web php artisan tinker
    }
    "clean" {
        & $PSCommandPath down
        docker volume rm northline-supply_postgres_data northline-supply_redis_data 2>$null | Out-Null
        Write-Host "==> Containers and database volumes removed" -ForegroundColor Green
    }
    default {
        Show-Help
    }
}
