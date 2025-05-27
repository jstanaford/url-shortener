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

case "$1" in
  start)
    ensure_db_file
    docker compose up -d
    ;;
  stop)
    docker compose down
    ;;
  restart)
    docker compose down
    ensure_db_file
    docker compose up -d
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
    echo "Cache cleared successfully!"
    ;;
  test)
    ./test.sh
    ;;
  *)
    echo "Usage: $0 {start|stop|restart|migrate|clear-cache|test}"
    exit 1
    ;;
esac 