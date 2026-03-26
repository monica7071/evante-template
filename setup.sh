#!/bin/bash
# ==============================================
# Evante Project Setup Script
# ==============================================
# Prerequisites:
#   - PHP 8.3+ with extensions: mysql, xml, mbstring, curl, zip, gd, bcmath, intl, tokenizer
#   - Composer
#   - MySQL/MariaDB
#   - Node.js & NPM (already installed)
# ==============================================

set -e

echo "=========================================="
echo "  Evante Project Setup"
echo "=========================================="

# Check prerequisites
echo ""
echo "[1/7] Checking prerequisites..."

if ! command -v php &> /dev/null; then
    echo "ERROR: PHP is not installed. Please install PHP 8.3+"
    echo "  macOS:   brew install php"
    echo "  Ubuntu:  sudo apt install php8.3 php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl"
    echo "  Windows: Download from https://windows.php.net/download/"
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo "ERROR: Composer is not installed."
    echo "  Install: curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer"
    exit 1
fi

if ! command -v mysql &> /dev/null; then
    echo "ERROR: MySQL/MariaDB is not installed."
    echo "  macOS:   brew install mysql"
    echo "  Ubuntu:  sudo apt install mysql-server"
    echo "  Windows: Download from https://dev.mysql.com/downloads/"
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "  PHP: $PHP_VERSION"
echo "  Composer: $(composer --version 2>&1 | head -1)"
echo "  MySQL: $(mysql --version 2>&1 | head -1)"

# Install Composer dependencies
echo ""
echo "[2/7] Installing Composer dependencies..."
composer install

# Create database
echo ""
echo "[3/7] Creating database 'floorplan-evente'..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`floorplan-evente\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || \
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS \`floorplan-evente\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import SQL
echo ""
echo "[4/7] Importing database from SQL file..."
mysql -u root \`floorplan-evente\` < database/floorplan-evente.sql 2>/dev/null || \
mysql -u root -p \`floorplan-evente\` < database/floorplan-evente.sql

echo "  Database imported successfully!"

# Generate application key
echo ""
echo "[5/7] Generating application key..."
php artisan key:generate

# Create storage link
echo ""
echo "[6/7] Creating storage link..."
php artisan storage:link 2>/dev/null || true

# Build frontend assets
echo ""
echo "[7/7] Building frontend assets..."
npm run build 2>/dev/null || echo "  (Skipped - run 'npm run dev' for development)"

echo ""
echo "=========================================="
echo "  Setup Complete!"
echo "=========================================="
echo ""
echo "  To start the development server:"
echo "    php artisan serve"
echo ""
echo "  To start Vite dev server (for frontend):"
echo "    npm run dev"
echo ""
echo "  Access the app at: http://localhost:8000"
echo "=========================================="
