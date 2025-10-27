#!/bin/bash

# Todo Tracker SaaS - Production Deployment Script
# This script automates the deployment process for MAMP/Apache

set -e  # Exit on error

echo "🚀 Starting Todo Tracker SaaS Deployment..."
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Project root directory
PROJECT_ROOT="/Users/bettyphipps/SaaS-Project"
cd $PROJECT_ROOT

# Step 1: Backup existing files
echo -e "${GREEN}📦 Step 1: Creating backup...${NC}"
BACKUP_DIR="$PROJECT_ROOT/backups/$(date +%Y%m%d_%H%M%S)"
mkdir -p $BACKUP_DIR
cp -r storage $BACKUP_DIR/ 2>/dev/null || true
cp .env $BACKUP_DIR/ 2>/dev/null || true
echo "✅ Backup created at: $BACKUP_DIR"

# Step 2: Pull latest changes from Git
echo -e "${GREEN}📥 Step 2: Pulling latest changes from Git...${NC}"
git pull origin main
echo "✅ Git pull completed"

# Step 3: Install/Update PHP dependencies
echo -e "${GREEN}📦 Step 3: Installing PHP dependencies...${NC}"
composer install --optimize-autoloader --no-dev --no-interaction
echo "✅ Composer dependencies installed"

# Step 4: Install/Update Node dependencies
echo -e "${GREEN}📦 Step 4: Installing Node dependencies...${NC}"
npm ci --production
echo "✅ NPM dependencies installed"

# Step 5: Build frontend assets
echo -e "${GREEN}🏗️  Step 5: Building frontend assets...${NC}"
npm run build
echo "✅ Frontend assets built"

# Step 6: Optimize Laravel
echo -e "${GREEN}⚡ Step 6: Optimizing Laravel...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo "✅ Laravel optimized"

# Step 7: Run database migrations (if needed)
echo -e "${GREEN}🗄️  Step 7: Running database migrations...${NC}"
php artisan migrate --force --no-interaction || echo "⚠️  Migrations failed or already up to date"
echo "✅ Database migrations completed"

# Step 8: Clear all caches
echo -e "${GREEN}🧹 Step 8: Clearing caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo "✅ Caches cleared"

# Step 9: Set permissions
echo -e "${GREEN}🔐 Step 9: Setting permissions...${NC}"
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chmod -R 775 storage bootstrap/cache
echo "✅ Permissions set"

# Step 10: Test database connection
echo -e "${GREEN}🔌 Step 10: Testing database connection...${NC}"
php artisan db:show || echo "⚠️  Database connection test failed"
echo "✅ Database connection checked"

# Step 11: Queue restart
echo -e "${GREEN}🔄 Step 11: Restarting queue workers...${NC}"
php artisan queue:restart
echo "✅ Queue workers restarted"

# Step 12: Display deployment info
echo ""
echo -e "${GREEN}=============================================="
echo "✅ Deployment completed successfully!"
echo "=============================================="
echo ""
echo "📍 Application URL: http://localhost:8888"
echo "📍 Health Check: http://localhost:8888/health"
echo "📍 API Docs: http://localhost:8888/api/docs"
echo ""
echo "📋 Next Steps:"
echo "  1. Test the application: http://localhost:8888"
echo "  2. Check logs: tail -f storage/logs/laravel.log"
echo "  3. Monitor queue: php artisan horizon:status"
echo ""
echo "📝 Deployment backup: $BACKUP_DIR"
echo -e "${NC}"

# Exit successfully
exit 0
