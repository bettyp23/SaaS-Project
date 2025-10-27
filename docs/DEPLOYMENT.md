# Deployment Guide - Todo Tracker SaaS

## Production Deployment for MAMP/Apache (port 8888)

This guide walks you through deploying the Todo Tracker SaaS application to production using MAMP's Apache server.

## Prerequisites

- MAMP installed and running
- MySQL server on port 8889
- PHP 8.1 or higher
- Composer installed
- Node.js and npm installed
- Git repository cloned

## Step 1: Environment Setup

### 1.1 Create Production `.env` File

```bash
cp .env.example .env
```

Update `.env` with production values:

```env
APP_NAME="Todo Tracker SaaS"
APP_ENV=production
APP_KEY=base64:YOUR_PRODUCTION_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=todo_tracker_saas
DB_USERNAME=vibe_templates
DB_PASSWORD=vibe_templates_password

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Stripe (for subscriptions)
STRIPE_KEY=your-stripe-key
STRIPE_SECRET=your-stripe-secret
STRIPE_WEBHOOK_SECRET=your-webhook-secret

# Security
SESSION_SECURE_COOKIE=true
COOKIE_HTTPONLY=true
COOKIE_SAMESITE=strict

# Application Settings
LOG_CHANNEL=stack
LOG_LEVEL=error

# Force HTTPS
FORCE_HTTPS=true
```

### 1.2 Generate Application Key

```bash
php artisan key:generate
```

## Step 2: Database Setup

### 2.1 Create Production Database

```bash
mysql -u vibe_templates -p -h 127.0.0.1 -P 8889
```

```sql
CREATE DATABASE IF NOT EXISTS todo_tracker_saas_production;
USE todo_tracker_saas_production;
```

### 2.2 Import Schema

```bash
mysql -u vibe_templates -p -h 127.0.0.1 -P 8889 todo_tracker_saas_production < database-schema.sql
```

### 2.3 Update .env Database Name

```env
DB_DATABASE=todo_tracker_saas_production
```

## Step 3: Install Dependencies

### 3.1 Install PHP Dependencies

```bash
composer install --optimize-autoloader --no-dev
```

### 3.2 Install Node Dependencies

```bash
npm ci
```

## Step 4: Build Frontend Assets

### 4.1 Build for Production

```bash
npm run build
```

This creates optimized production assets in `public/build/`.

## Step 5: Laravel Configuration

### 5.1 Optimize Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5.2 Set Permissions

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

## Step 6: MAMP Configuration

### 6.1 Set Document Root

1. Open MAMP
2. Go to **Preferences > Web Server**
3. Set Document Root to: `/Users/bettyphipps/SaaS-Project/html`
4. Click **OK**

### 6.2 Configure Apache

Edit `/Applications/MAMP/conf/apache/httpd.conf`:

```apache
# Ensure mod_rewrite is enabled
LoadModule rewrite_module modules/mod_rewrite.so

# Set AllowOverride to enable .htaccess
<Directory "/Users/bettyphipps/SaaS-Project/html">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### 6.3 Restart MAMP

1. Stop all services in MAMP
2. Start services again

## Step 7: Apache Security Headers

### 7.1 Create Security Headers File

Create `html/.htaccess` with security headers:

```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security Headers
<IfModule mod_headers.c>
    # Prevent clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"
    
    # Prevent MIME sniffing
    Header always set X-Content-Type-Options "nosniff"
    
    # Enable XSS protection
    Header always set X-XSS-Protection "1; mode=block"
    
    # Enforce HTTPS
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    
    # Content Security Policy
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';"
    
    # Referrer Policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Permissions Policy
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>

# Disable Directory Browsing
Options -Indexes

# Protect sensitive files
<FilesMatch "^\.">
    Require all denied
</FilesMatch>

# Prevent access to .env
<Files .env>
    Require all denied
</Files>
```

## Step 8: SSL Certificate (Optional but Recommended)

### 8.1 Generate Self-Signed Certificate (Development)

```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /Applications/MAMP/conf/apache/server.key \
  -out /Applications/MAMP/conf/apache/server.crt
```

### 8.2 Configure Apache SSL

Edit `/Applications/MAMP/conf/apache/extra/httpd-ssl.conf`:

```apache
SSLEngine on
SSLCertificateFile /Applications/MAMP/conf/apache/server.crt
SSLCertificateKeyFile /Applications/MAMP/conf/apache/server.key
```

## Step 9: Queue Workers

### 9.1 Create Supervisor Configuration

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /Users/bettyphipps/SaaS-Project/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/Users/bettyphipps/SaaS-Project/storage/logs/worker.log
stopwaitsecs=3600
```

### 9.2 Start Queue Worker

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## Step 10: Scheduled Tasks (Cron Jobs)

### 10.1 Add Laravel Scheduler

Edit crontab:

```bash
crontab -e
```

Add:

```bash
* * * * * cd /Users/bettyphipps/SaaS-Project && php artisan schedule:run >> /dev/null 2>&1
```

## Step 11: Monitoring & Logging

### 11.1 Set Up Log Rotation

Create `/etc/logrotate.d/laravel`:

```
/Users/bettyphipps/SaaS-Project/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### 11.2 Monitor Application Logs

```bash
tail -f storage/logs/laravel.log
```

## Step 12: Performance Optimization

### 12.1 Enable OPcache

Edit `php.ini`:

```ini
[opcache]
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.fast_shutdown=1
```

### 12.2 Enable Gzip Compression

Add to `html/.htaccess`:

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

## Step 13: Backup Strategy

### 13.1 Database Backup Script

Create `scripts/backup-db.sh`:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/Users/bettyphipps/backups"
DB_NAME="todo_tracker_saas_production"
DB_USER="vibe_templates"
DB_PASS="vibe_templates_password"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS -h 127.0.0.1 -P 8889 $DB_NAME > $BACKUP_DIR/backup_$DATE.sql
gzip $BACKUP_DIR/backup_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete
```

### 13.2 Schedule Backups

```bash
chmod +x scripts/backup-db.sh
crontab -e
```

Add:

```bash
0 2 * * * /Users/bettyphipps/SaaS-Project/scripts/backup-db.sh
```

## Step 14: Health Check Endpoints

### 14.1 Test Health Endpoint

```bash
curl https://yourdomain.com/health
```

Expected response:

```json
{
  "status": "ok",
  "database": "connected",
  "cache": "operational",
  "queue": "running",
  "timestamp": "2025-01-26T12:00:00Z"
}
```

## Step 15: Final Verification

### 15.1 Test Application

1. Visit: `https://yourdomain.com`
2. Test user registration
3. Test login
4. Create a todo
5. Test API endpoints

### 15.2 Check Logs

```bash
tail -f storage/logs/laravel.log
```

## Troubleshooting

### Issue: 500 Internal Server Error

**Solution:**
```bash
chmod -R 755 storage bootstrap/cache
php artisan config:clear
php artisan cache:clear
```

### Issue: Assets Not Loading

**Solution:**
```bash
npm run build
php artisan view:clear
```

### Issue: Database Connection Failed

**Solution:**
1. Check MySQL is running
2. Verify credentials in `.env`
3. Test connection: `php test-db.php`

### Issue: Queue Not Processing

**Solution:**
```bash
php artisan queue:restart
php artisan horizon:restart
```

## Production Checklist

- [ ] Environment variables configured
- [ ] Application key generated
- [ ] Database created and migrated
- [ ] Frontend assets built
- [ ] Configuration cached
- [ ] Storage permissions set
- [ ] Queue workers running
- [ ] Cron jobs configured
- [ ] SSL certificate installed
- [ ] Security headers configured
- [ ] Monitoring set up
- [ ] Backup strategy implemented
- [ ] Health checks passing
- [ ] Application tested

## Support

For issues or questions, please contact the development team or refer to:
- `INSTALLATION.md` - Installation guide
- `PROJECT-SUMMARY.md` - Project overview
- `README.md` - General information

## License

This project is licensed under the MIT License.
