#!/bin/bash

# Todo Tracker SaaS - Database Backup Script
# Automatically backs up the database with compression

set -e

# Configuration
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/Users/bettyphipps/SaaS-Project/backups/database"
DB_NAME="todo_tracker_saas"
DB_USER="vibe_templates"
DB_PASS="vibe_templates_password"
DB_HOST="127.0.0.1"
DB_PORT="8889"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "🗄️  Starting database backup..."
echo "=============================================="

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Backup database
echo -e "${GREEN}📦 Creating backup...${NC}"
BACKUP_FILE="$BACKUP_DIR/backup_${DATE}.sql"
mysqldump -u $DB_USER -p$DB_PASS -h $DB_HOST -P $DB_PORT $DB_NAME > $BACKUP_FILE

# Compress backup
echo -e "${GREEN}📦 Compressing backup...${NC}"
gzip $BACKUP_FILE
COMPRESSED_FILE="${BACKUP_FILE}.gz"

# Get file size
FILE_SIZE=$(du -h "$COMPRESSED_FILE" | cut -f1)

echo ""
echo -e "${GREEN}✅ Backup completed successfully!${NC}"
echo "=============================================="
echo "📁 Backup file: $COMPRESSED_FILE"
echo "📊 File size: $FILE_SIZE"
echo ""

# Keep only last 30 days of backups
echo -e "${YELLOW}🧹 Cleaning old backups (keeping last 30 days)...${NC}"
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete

# Count remaining backups
BACKUP_COUNT=$(ls -1 $BACKUP_DIR/backup_*.sql.gz 2>/dev/null | wc -l)
echo "📊 Total backups retained: $BACKUP_COUNT"
echo ""

# List recent backups
echo "Recent backups:"
ls -lh $BACKUP_DIR/backup_*.sql.gz | tail -5 | awk '{print "  - " $9 " (" $5 ")"}'
echo ""

exit 0
