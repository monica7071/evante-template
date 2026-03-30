#!/bin/bash
set -e

cd "$(dirname "$0")"

echo "=== Deploy started at $(date) ==="

git pull origin main

composer install --no-dev --no-interaction --optimize-autoloader

php artisan migrate --force

php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

npm run build 2>/dev/null || true

echo "=== Deploy finished at $(date) ==="
