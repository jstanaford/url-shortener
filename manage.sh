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
  *)
    echo "Usage: $0 {start|stop|restart}"
    exit 1
    ;;
esac 