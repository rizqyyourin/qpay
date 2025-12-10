#!/bin/bash
# QPAY Deployment Script
# Usage: ./deploy.sh

set -e

echo "======================================"
echo "QPAY Production Deployment Script"
echo "======================================"

# Configuration
DOMAIN="qpay.yourin.my.id"
APP_PATH="/var/www/qpay"
APP_USER="www-data"
GIT_BRANCH="main"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   log_error "This script must be run as root"
   exit 1
fi

# Step 1: Pull latest code
log_info "Pulling latest code from Git repository..."
cd $APP_PATH
git pull origin $GIT_BRANCH

# Step 2: Install dependencies
log_info "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Step 3: Install npm dependencies
log_info "Installing Node.js dependencies..."
npm install

# Step 4: Build assets
log_info "Building frontend assets..."
npm run build

# Step 5: Run migrations
log_info "Running database migrations..."
php artisan migrate --force

# Step 6: Optimize application
log_info "Optimizing application configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 7: Clear caches
log_info "Clearing application caches..."
php artisan cache:clear
php artisan queue:restart

# Step 8: Fix permissions
log_info "Setting correct file permissions..."
chown -R $APP_USER:$APP_USER $APP_PATH
chmod -R 755 $APP_PATH
chmod -R 775 $APP_PATH/storage
chmod -R 775 $APP_PATH/bootstrap/cache
chmod 600 $APP_PATH/.env

# Step 9: Restart PHP-FPM
log_info "Restarting PHP-FPM..."
systemctl restart php8.4-fpm

# Step 10: Reload web server
log_info "Reloading Nginx..."
systemctl reload nginx
# OR for Apache:
# systemctl reload apache2

# Step 11: Verify deployment
log_info "Verifying deployment..."
if curl -I https://$DOMAIN 2>/dev/null | grep -q "200"; then
    log_info "Application is successfully deployed!"
else
    log_error "Application health check failed!"
    exit 1
fi

log_info "Deployment completed successfully!"
log_info "URL: https://$DOMAIN"
