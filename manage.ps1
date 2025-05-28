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

switch ($Command) {
    "start" {
        Ensure-DB-File
        docker compose up -d
        Write-Host "Waiting for services to start..."
        Start-Sleep -Seconds 5
        # Run migrations after containers are up
        docker exec url_shortener_app php artisan migrate
        Write-Host "Starting queue workers..."
        docker exec url_shortener_queue php artisan queue:restart
        Write-Host "System is ready!"
    }
    "stop" {
        docker compose down
    }
    "restart" {
        docker compose down
        Ensure-DB-File
        docker compose up -d
        Write-Host "Waiting for services to start..."
        Start-Sleep -Seconds 5
        # Reset database and run migrations
        docker exec url_shortener_app php artisan migrate:fresh
        Write-Host "Restarting queue workers..."
        docker exec url_shortener_queue php artisan queue:restart
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
        Write-Host "Restarting queue workers..."
        docker exec url_shortener_queue php artisan queue:restart
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
        $queueExists = docker ps | Select-String url_shortener_queue
        if ($queueExists) {
            docker exec url_shortener_queue php artisan queue:restart
        } else {
            Write-Host "Warning: Queue worker not found, please restart with .\manage.ps1 restart"
        }
        
        Write-Host "`nTroubleshooting complete! Try your tests again."
    }
    "test" {
        # Clear cache before testing
        Write-Host "Preparing test environment..."
        docker exec url_shortener_app php artisan cache:clear
        docker exec url_shortener_redis redis-cli FLUSHALL
        $queueExists = docker ps | Select-String url_shortener_queue
        if ($queueExists) {
            docker exec url_shortener_queue php artisan queue:restart
        }
        Start-Sleep -Seconds 2
        & .\test.ps1
    }
    default {
        Write-Host "Usage: .\manage.ps1 {start|stop|restart|migrate|clear-cache|queue-status|troubleshoot|test}"
        exit 1
    }
} 