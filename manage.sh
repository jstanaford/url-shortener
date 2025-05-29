#!/bin/bash
set -e

DB_FILE="./database/main.sql"
DB_DIR="./database"

function ensure_db_file() {
  if [ ! -d "$DB_DIR" ]; then
    mkdir -p "$DB_DIR"
  fi
  if [ ! -f "$DB_FILE" ]; then
    touch "$DB_FILE"
    echo "-- MariaDB init file created" > "$DB_FILE"
  fi
}

function ensure_dependencies() {
  if [ ! -d "src/vendor/laravel" ]; then
    echo "Installing Laravel dependencies..."
    cd src && composer install
    cd ..
  fi
}

function ensure_app_key() {
  echo "Checking for Laravel application key..."
  if ! docker exec url_shortener_app grep -q "APP_KEY=" .env || docker exec url_shortener_app grep -q "APP_KEY=$" .env; then
    echo "Generating Laravel application key..."
    docker exec url_shortener_app php artisan key:generate --force
  else
    echo "Laravel application key exists."
  fi
}

function restart_queue() {
  echo "Starting queue workers..."
  # Check if container is running before restarting queue
  if docker ps --filter "name=url_shortener_queue" --filter "status=running" --format "{{.Names}}" | grep -q url_shortener_queue; then
    docker exec url_shortener_queue php artisan queue:restart
  else
    echo "Queue container is not running yet. Waiting..."
    sleep 5
    if docker ps --filter "name=url_shortener_queue" --filter "status=running" --format "{{.Names}}" | grep -q url_shortener_queue; then
      docker exec url_shortener_queue php artisan queue:restart
    else
      echo "Warning: Queue container not running. Please check docker logs for details."
    fi
  fi
}

case "$1" in
  start)
    ensure_db_file
    ensure_dependencies
    docker compose up -d
    echo "Waiting for services to start..."
    sleep 5
    # Run migrations after containers are up
    ensure_app_key
    docker exec url_shortener_app php artisan migrate --force
    restart_queue
    echo "System is ready!"
    ;;
  stop)
    docker compose down
    ;;
  restart)
    docker compose down
    ensure_db_file
    ensure_dependencies
    docker compose up -d
    echo "Waiting for services to start..."
    sleep 5
    # Reset database and run migrations
    ensure_app_key
    docker exec url_shortener_app php artisan migrate:fresh --force
    restart_queue
    echo "System is ready!"
    ;;
  migrate)
    docker exec url_shortener_app php artisan migrate
    ;;
  clear-cache)
    echo "Clearing Laravel cache..."
    docker exec url_shortener_app php artisan cache:clear
    docker exec url_shortener_app php artisan config:clear
    docker exec url_shortener_app php artisan route:clear
    docker exec url_shortener_app php artisan view:clear
    # Also clear Redis cache
    docker exec url_shortener_redis redis-cli FLUSHALL
    restart_queue
    echo "Cache cleared successfully!"
    ;;
  queue-status)
    docker exec url_shortener_app php artisan queue:monitor
    ;;
  troubleshoot)
    echo "===== URL Shortener Troubleshooter ====="
    echo "Checking Docker containers..."
    docker ps | grep url_shortener
    
    echo -e "\nChecking Redis connection..."
    docker exec url_shortener_redis redis-cli PING
    
    echo -e "\nChecking Redis data..."
    docker exec url_shortener_redis redis-cli INFO | grep used_memory_human
    docker exec url_shortener_redis redis-cli DBSIZE
    
    echo -e "\nChecking queue configuration..."
    docker exec url_shortener_app cat .env | grep QUEUE_CONNECTION
    docker exec url_shortener_app cat .env | grep REDIS_HOST
    docker exec url_shortener_app cat .env | grep CACHE_STORE
    
    echo -e "\nFixing potential issues..."
    docker exec url_shortener_redis redis-cli FLUSHALL
    
    echo -e "\nRestoring .env settings..."
    docker exec url_shortener_app bash -c "sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=redis/g' .env"
    docker exec url_shortener_app bash -c "sed -i 's/QUEUE_CONNECTION=database/QUEUE_CONNECTION=redis/g' .env"
    docker exec url_shortener_app bash -c "sed -i 's/CACHE_STORE=database/CACHE_STORE=redis/g' .env"
    
    echo -e "\nChecking application key..."
    ensure_app_key
    
    echo -e "\nRestarting services..."
    docker restart url_shortener_app

    sleep 2
    
    # Check if queue worker exists
    if docker ps | grep -q url_shortener_queue; then
        docker restart url_shortener_queue
    else
        echo "Queue worker container not found, starting docker-compose again..."
        docker compose up -d
    fi
    
    echo -e "\nClearing Laravel config cache..."
    docker exec url_shortener_app php artisan config:clear
    docker exec url_shortener_app php artisan cache:clear
    
    echo -e "\nRestarting queue workers..."
    restart_queue
    
    echo -e "\nTroubleshooting complete! Try your tests again."
    ;;
  test)
    # Clear cache before testing
    echo "Preparing test environment..."
    docker exec url_shortener_app php artisan cache:clear
    docker exec url_shortener_redis redis-cli FLUSHALL
    restart_queue
    sleep 2
    ./test.sh
    ;;
  *)
    echo "Usage: $0 {start|stop|restart|migrate|clear-cache|queue-status|troubleshoot|test}"
    exit 1
    ;;
esac 