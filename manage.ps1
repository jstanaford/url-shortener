# PowerShell equivalent of manage.sh
param (
    [Parameter(Position=0, Mandatory=$true)]
    [string]$Command
)

$DB_FILE = ".\database\main.sql"
$DB_DIR = ".\database"

function Ensure-DB-File {
    if (-not (Test-Path $DB_DIR)) {
        New-Item -ItemType Directory -Path $DB_DIR -Force | Out-Null
    }
    if (-not (Test-Path $DB_FILE)) {
        "-- MariaDB init file created" | Set-Content $DB_FILE
    }
}

function Ensure-Dependencies {
    if (-not (Test-Path "src\vendor")) {
        Write-Host "Installing Laravel dependencies..."
        Push-Location src
        composer install
        Pop-Location
    }
}

function Ensure-App-Key {
    Write-Host "Checking for Laravel application key..."
    $hasKey = docker exec url_shortener_app sh -c "grep -q 'APP_KEY=' .env"
    $emptyKey = docker exec url_shortener_app sh -c "grep -q 'APP_KEY=$' .env"
    
    if (-not $hasKey -or $emptyKey) {
        Write-Host "Generating Laravel application key..."
        docker exec url_shortener_app php artisan key:generate --force
    } else {
        Write-Host "Laravel application key exists."
    }
}

function Restart-Queue {
    Write-Host "Starting queue workers..."
    # Check if container is running before restarting queue
    $queueRunning = docker ps --filter "name=url_shortener_queue" --filter "status=running" --format "{{.Names}}"
    if ($queueRunning -match "url_shortener_queue") {
        docker exec url_shortener_queue php artisan queue:restart
    } else {
        Write-Host "Queue container is not running yet. Waiting..."
        Start-Sleep -Seconds 5
        $queueRunning = docker ps --filter "name=url_shortener_queue" --filter "status=running" --format "{{.Names}}"
        if ($queueRunning -match "url_shortener_queue") {
            docker exec url_shortener_queue php artisan queue:restart
        } else {
            Write-Host "Warning: Queue container not running. Please check docker logs for details."
        }
    }
}

switch ($Command) {
    "start" {
        Ensure-DB-File
        Ensure-Dependencies
        docker compose up -d
        Write-Host "Waiting for services to start..."
        Start-Sleep -Seconds 5
        # Run migrations after containers are up
        Ensure-App-Key
        docker exec url_shortener_app php artisan migrate --force
        Restart-Queue
        Write-Host "System is ready!"
    }
    "stop" {
        docker compose down
    }
    "restart" {
        docker compose down
        Ensure-DB-File
        Ensure-Dependencies
        docker compose up -d
        Write-Host "Waiting for services to start..."
        Start-Sleep -Seconds 5
        # Reset database and run migrations
        Ensure-App-Key
        docker exec url_shortener_app php artisan migrate:fresh --force
        Restart-Queue
        Write-Host "System is ready!"
    }
    "migrate" {
        docker exec url_shortener_app php artisan migrate
    }
    "clear-cache" {
        Write-Host "Clearing Laravel cache..."
        docker exec url_shortener_app php artisan cache:clear
        docker exec url_shortener_app php artisan config:clear
        docker exec url_shortener_app php artisan route:clear
        docker exec url_shortener_app php artisan view:clear
        # Also clear Redis cache
        docker exec url_shortener_redis redis-cli FLUSHALL
        Restart-Queue
        Write-Host "Cache cleared successfully!"
    }
    "queue-status" {
        docker exec url_shortener_app php artisan queue:monitor
    }
    "troubleshoot" {
        Write-Host "===== URL Shortener Troubleshooter ====="
        Write-Host "Checking Docker containers..."
        docker ps | Select-String url_shortener
        
        Write-Host "`nChecking Redis connection..."
        docker exec url_shortener_redis redis-cli PING
        
        Write-Host "`nChecking Redis data..."
        docker exec url_shortener_redis redis-cli INFO | Select-String used_memory_human
        docker exec url_shortener_redis redis-cli DBSIZE
        
        Write-Host "`nChecking queue configuration..."
        docker exec url_shortener_app sh -c "cat .env | grep QUEUE_CONNECTION"
        docker exec url_shortener_app sh -c "cat .env | grep REDIS_HOST"
        docker exec url_shortener_app sh -c "cat .env | grep CACHE_STORE"
        
        Write-Host "`nFixing potential issues..."
        docker exec url_shortener_redis redis-cli FLUSHALL
        
        Write-Host "`nRestoring .env settings..."
        docker exec url_shortener_app sh -c "sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=redis/g' .env"
        docker exec url_shortener_app sh -c "sed -i 's/QUEUE_CONNECTION=database/QUEUE_CONNECTION=redis/g' .env"
        docker exec url_shortener_app sh -c "sed -i 's/CACHE_STORE=database/CACHE_STORE=redis/g' .env"
        
        Write-Host "`nChecking application key..."
        Ensure-App-Key
        
        Write-Host "`nRestarting services..."
        docker restart url_shortener_app

        Start-Sleep -Seconds 2
        
        # Check if queue worker exists
        $queueExists = docker ps | Select-String url_shortener_queue
        if ($queueExists) {
            docker restart url_shortener_queue
        } else {
            Write-Host "Queue worker container not found, starting docker-compose again..."
            docker compose up -d
        }
        
        Write-Host "`nClearing Laravel config cache..."
        docker exec url_shortener_app php artisan config:clear
        docker exec url_shortener_app php artisan cache:clear
        
        Write-Host "`nRestarting queue workers..."
        Restart-Queue
        
        Write-Host "`nTroubleshooting complete! Try your tests again."
    }
    "test" {
        # Clear cache before testing
        Write-Host "Preparing test environment..."
        docker exec url_shortener_app php artisan cache:clear
        docker exec url_shortener_redis redis-cli FLUSHALL
        Restart-Queue
        Start-Sleep -Seconds 2
        & .\test.ps1
    }
    default {
        Write-Host "Usage: .\manage.ps1 {start|stop|restart|migrate|clear-cache|queue-status|troubleshoot|test}"
        exit 1
    }
} 