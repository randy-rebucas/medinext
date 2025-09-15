# Medinext EMR - Technical Implementation Guide
## Step-by-Step Technical Deployment

## Table of Contents
1. [System Architecture](#system-architecture)
2. [Environment Setup](#environment-setup)
3. [Database Configuration](#database-configuration)
4. [Application Deployment](#application-deployment)
5. [Security Implementation](#security-implementation)
6. [Performance Optimization](#performance-optimization)
7. [Monitoring Setup](#monitoring-setup)
8. [Backup Configuration](#backup-configuration)
9. [SSL/TLS Configuration](#ssltls-configuration)
10. [Load Balancing](#load-balancing)
11. [Troubleshooting](#troubleshooting)
12. [Maintenance Procedures](#maintenance-procedures)

---

## System Architecture

### High-Level Architecture
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Load Balancer │    │   Web Servers   │    │  Database       │
│   (Nginx)       │────│   (Laravel)     │────│  (MySQL/PostgreSQL)
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   SSL/TLS       │    │   Redis Cache   │    │   File Storage  │
│   Termination   │    │   & Sessions    │    │   (S3/Local)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Component Overview
- **Load Balancer**: Nginx for high availability
- **Web Servers**: Laravel application servers
- **Database**: MySQL/PostgreSQL for data storage
- **Cache**: Redis for caching and sessions
- **Storage**: File storage for documents and images
- **Monitoring**: System and application monitoring

---

## Environment Setup

### 1. Server Requirements

#### Minimum Specifications
```bash
# Production Server
CPU: 4 cores, 2.4GHz
RAM: 8GB (16GB recommended)
Storage: 100GB SSD (500GB recommended)
Network: 100Mbps dedicated connection
OS: Ubuntu 20.04 LTS or CentOS 8+
```

#### Recommended Specifications
```bash
# High-Performance Server
CPU: 8 cores, 3.0GHz
RAM: 32GB
Storage: 1TB SSD
Network: 1Gbps dedicated connection
OS: Ubuntu 22.04 LTS
```

### 2. Operating System Setup

#### Ubuntu 22.04 LTS Installation
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget git unzip software-properties-common

# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 and extensions
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-redis php8.2-intl

# Install Nginx
sudo apt install -y nginx

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 3. User and Directory Setup

#### Create Application User
```bash
# Create medinext user
sudo useradd -m -s /bin/bash medinext
sudo usermod -aG www-data medinext

# Create application directory
sudo mkdir -p /var/www/medinext
sudo chown -R medinext:www-data /var/www/medinext
sudo chmod -R 755 /var/www/medinext
```

#### Directory Structure
```
/var/www/medinext/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
└── composer.json
```

---

## Database Configuration

### 1. MySQL Setup

#### MySQL Configuration
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
-- Create database
CREATE DATABASE medinext_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'medinext_user'@'localhost' IDENTIFIED BY 'secure_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON medinext_production.* TO 'medinext_user'@'localhost';
FLUSH PRIVILEGES;

-- Exit MySQL
EXIT;
```

#### MySQL Optimization
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
# Basic settings
default-storage-engine = InnoDB
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Performance settings
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Connection settings
max_connections = 200
max_connect_errors = 10000

# Query cache
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### 2. PostgreSQL Setup (Alternative)

#### PostgreSQL Installation
```bash
# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Create database and user
sudo -u postgres psql
```

```sql
-- Create database
CREATE DATABASE medinext_production;

-- Create user
CREATE USER medinext_user WITH PASSWORD 'secure_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE medinext_production TO medinext_user;

-- Exit PostgreSQL
\q
```

---

## Application Deployment

### 1. Code Deployment

#### Clone Repository
```bash
# Switch to medinext user
sudo su - medinext

# Clone repository
cd /var/www/medinext
git clone https://github.com/your-org/medinext.git .

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install

# Build frontend assets
npm run production
```

#### Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

```env
# .env Production Configuration
APP_NAME="Medinext EMR"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medinext_production
DB_USERNAME=medinext_user
DB_PASSWORD=secure_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 2. Database Migration

#### Run Migrations
```bash
# Run database migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Run performance optimization migration
php artisan migrate --path=database/migrations/2024_01_15_000000_add_performance_indexes.php
```

#### Verify Database
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit

# Verify tables
php artisan db:show
```

### 3. Application Optimization

#### Cache Optimization
```bash
# Clear existing caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Build optimized caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### File Permissions
```bash
# Set proper permissions
sudo chown -R medinext:www-data /var/www/medinext
sudo chmod -R 755 /var/www/medinext
sudo chmod -R 775 /var/www/medinext/storage
sudo chmod -R 775 /var/www/medinext/bootstrap/cache
```

---

## Security Implementation

### 1. Nginx Configuration

#### Basic Nginx Configuration
```nginx
# /etc/nginx/sites-available/medinext
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/medinext/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com; connect-src 'self' https:; frame-ancestors 'none';";

    # Rate Limiting
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;

    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    location ~ /(storage|bootstrap/cache) {
        deny all;
        access_log off;
        log_not_found off;
    }

    # API rate limiting
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Login rate limiting
    location /api/auth/login {
        limit_req zone=login burst=3 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

#### Enable Site
```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/medinext /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### 2. PHP-FPM Configuration

#### PHP-FPM Pool Configuration
```ini
# /etc/php/8.2/fpm/pool.d/medinext.conf
[medinext]
user = medinext
group = www-data
listen = /var/run/php/php8.2-fpm-medinext.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

; Security
php_admin_value[disable_functions] = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
php_admin_value[open_basedir] = /var/www/medinext:/tmp
php_admin_value[upload_tmp_dir] = /var/www/medinext/storage/app/tmp
php_admin_value[session.save_path] = /var/www/medinext/storage/framework/sessions

; Performance
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[max_input_time] = 300
php_admin_value[post_max_size] = 50M
php_admin_value[upload_max_filesize] = 50M
```

#### Restart PHP-FPM
```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### 3. Firewall Configuration

#### UFW Firewall Setup
```bash
# Enable UFW
sudo ufw enable

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow MySQL (if needed for remote access)
sudo ufw allow 3306/tcp

# Allow Redis (if needed for remote access)
sudo ufw allow 6379/tcp

# Check status
sudo ufw status
```

---

## Performance Optimization

### 1. Redis Configuration

#### Redis Setup
```bash
# Configure Redis
sudo nano /etc/redis/redis.conf
```

```conf
# Redis Configuration
bind 127.0.0.1
port 6379
timeout 0
tcp-keepalive 300

# Memory management
maxmemory 1gb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Logging
loglevel notice
logfile /var/log/redis/redis-server.log

# Security
requirepass your_redis_password_here
```

#### Restart Redis
```bash
# Restart Redis
sudo systemctl restart redis-server

# Test Redis connection
redis-cli ping
```

### 2. Queue Configuration

#### Supervisor Setup
```bash
# Install Supervisor
sudo apt install -y supervisor

# Create configuration
sudo nano /etc/supervisor/conf.d/medinext.conf
```

```ini
[program:medinext-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/medinext/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=medinext
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/medinext/storage/logs/worker.log
stopwaitsecs=3600

[program:medinext-scheduler]
process_name=%(program_name)s
command=php /var/www/medinext/artisan schedule:work
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=medinext
redirect_stderr=true
stdout_logfile=/var/www/medinext/storage/logs/scheduler.log
```

#### Start Supervisor
```bash
# Reload Supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start medinext-worker:*
sudo supervisorctl start medinext-scheduler

# Check status
sudo supervisorctl status
```

### 3. Cron Jobs

#### Setup Cron Jobs
```bash
# Edit crontab
sudo crontab -e
```

```bash
# Laravel scheduler
* * * * * cd /var/www/medinext && php artisan schedule:run >> /dev/null 2>&1

# Daily backups
0 2 * * * cd /var/www/medinext && php artisan backup:run >> /dev/null 2>&1

# Cache cleanup
0 3 * * * cd /var/www/medinext && php artisan cache:clear >> /dev/null 2>&1

# Log cleanup
0 4 * * * cd /var/www/medinext && php artisan logs:clean >> /dev/null 2>&1
```

---

## Monitoring Setup

### 1. System Monitoring

#### Install Monitoring Tools
```bash
# Install htop and iotop
sudo apt install -y htop iotop

# Install monitoring scripts
sudo mkdir -p /opt/monitoring
```

#### System Health Script
```bash
# /opt/monitoring/health-check.sh
#!/bin/bash

# System health check script
LOG_FILE="/var/log/medinext-health.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

# Check disk space
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "$DATE WARNING: Disk usage is $DISK_USAGE%" >> $LOG_FILE
fi

# Check memory usage
MEMORY_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ $MEMORY_USAGE -gt 80 ]; then
    echo "$DATE WARNING: Memory usage is $MEMORY_USAGE%" >> $LOG_FILE
fi

# Check CPU load
CPU_LOAD=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
if (( $(echo "$CPU_LOAD > 2.0" | bc -l) )); then
    echo "$DATE WARNING: CPU load is $CPU_LOAD" >> $LOG_FILE
fi

# Check MySQL
if ! systemctl is-active --quiet mysql; then
    echo "$DATE ERROR: MySQL is not running" >> $LOG_FILE
fi

# Check Redis
if ! systemctl is-active --quiet redis-server; then
    echo "$DATE ERROR: Redis is not running" >> $LOG_FILE
fi

# Check Nginx
if ! systemctl is-active --quiet nginx; then
    echo "$DATE ERROR: Nginx is not running" >> $LOG_FILE
fi

# Check PHP-FPM
if ! systemctl is-active --quiet php8.2-fpm; then
    echo "$DATE ERROR: PHP-FPM is not running" >> $LOG_FILE
fi
```

#### Make Script Executable
```bash
# Make script executable
sudo chmod +x /opt/monitoring/health-check.sh

# Add to crontab
echo "*/5 * * * * /opt/monitoring/health-check.sh" | sudo crontab -
```

### 2. Application Monitoring

#### Laravel Monitoring
```bash
# Install monitoring package
composer require spatie/laravel-health

# Publish configuration
php artisan vendor:publish --provider="Spatie\Health\HealthServiceProvider" --tag="health-config"

# Run health checks
php artisan health:check
```

#### Log Monitoring
```bash
# Install log monitoring
sudo apt install -y logwatch

# Configure logwatch
sudo nano /etc/logwatch/conf/logwatch.conf
```

```conf
# Logwatch Configuration
LogDir = /var/log
TmpDir = /var/cache/logwatch
MailTo = admin@your-domain.com
MailFrom = logwatch@your-domain.com
Print = No
Save = /var/cache/logwatch/logwatch.log
Range = yesterday
Detail = Med
Service = All
Format = text
Encode = none
```

---

## Backup Configuration

### 1. Database Backup

#### Database Backup Script
```bash
# /opt/backup/db-backup.sh
#!/bin/bash

# Database backup script
BACKUP_DIR="/opt/backup/database"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="medinext_production"
DB_USER="medinext_user"
DB_PASS="secure_password_here"

# Create backup directory
mkdir -p $BACKUP_DIR

# Create database backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/medinext_$DATE.sql.gz

# Keep only last 7 days of backups
find $BACKUP_DIR -name "medinext_*.sql.gz" -mtime +7 -delete

# Log backup
echo "$(date): Database backup completed - medinext_$DATE.sql.gz" >> /var/log/backup.log
```

#### Make Script Executable
```bash
# Make script executable
sudo chmod +x /opt/backup/db-backup.sh

# Test backup
sudo /opt/backup/db-backup.sh
```

### 2. File Backup

#### File Backup Script
```bash
# /opt/backup/file-backup.sh
#!/bin/bash

# File backup script
BACKUP_DIR="/opt/backup/files"
DATE=$(date +%Y%m%d_%H%M%S)
SOURCE_DIR="/var/www/medinext"

# Create backup directory
mkdir -p $BACKUP_DIR

# Create file backup
tar -czf $BACKUP_DIR/medinext_files_$DATE.tar.gz -C $SOURCE_DIR .

# Keep only last 7 days of backups
find $BACKUP_DIR -name "medinext_files_*.tar.gz" -mtime +7 -delete

# Log backup
echo "$(date): File backup completed - medinext_files_$DATE.tar.gz" >> /var/log/backup.log
```

#### Make Script Executable
```bash
# Make script executable
sudo chmod +x /opt/backup/file-backup.sh

# Test backup
sudo /opt/backup/file-backup.sh
```

### 3. Automated Backup

#### Backup Cron Job
```bash
# Add to crontab
sudo crontab -e
```

```bash
# Daily database backup at 2 AM
0 2 * * * /opt/backup/db-backup.sh

# Daily file backup at 3 AM
0 3 * * * /opt/backup/file-backup.sh

# Weekly full backup on Sunday at 1 AM
0 1 * * 0 /opt/backup/db-backup.sh && /opt/backup/file-backup.sh
```

---

## SSL/TLS Configuration

### 1. Let's Encrypt SSL

#### Install Certbot
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test automatic renewal
sudo certbot renew --dry-run
```

#### Auto-renewal Setup
```bash
# Add to crontab
echo "0 12 * * * /usr/bin/certbot renew --quiet" | sudo crontab -
```

### 2. Custom SSL Certificate

#### Install Custom Certificate
```bash
# Copy certificate files
sudo cp your-certificate.crt /etc/ssl/certs/
sudo cp your-private-key.key /etc/ssl/private/
sudo cp your-ca-bundle.crt /etc/ssl/certs/

# Set proper permissions
sudo chmod 644 /etc/ssl/certs/your-certificate.crt
sudo chmod 600 /etc/ssl/private/your-private-key.key
sudo chmod 644 /etc/ssl/certs/your-ca-bundle.crt

# Update Nginx configuration
sudo nano /etc/nginx/sites-available/medinext
```

```nginx
# SSL Configuration
ssl_certificate /etc/ssl/certs/your-certificate.crt;
ssl_certificate_key /etc/ssl/private/your-private-key.key;
ssl_trusted_certificate /etc/ssl/certs/your-ca-bundle.crt;
```

---

## Load Balancing

### 1. Nginx Load Balancer

#### Load Balancer Configuration
```nginx
# /etc/nginx/sites-available/load-balancer
upstream medinext_backend {
    least_conn;
    server 192.168.1.10:80 weight=3;
    server 192.168.1.11:80 weight=3;
    server 192.168.1.12:80 weight=2;
    keepalive 32;
}

server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    # Security Headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location / {
        proxy_pass http://medinext_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_connect_timeout 30s;
        proxy_send_timeout 30s;
        proxy_read_timeout 30s;
    }

    # Health check endpoint
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
}
```

### 2. Application Server Configuration

#### Application Server Setup
```nginx
# /etc/nginx/sites-available/medinext-app
server {
    listen 80;
    server_name _;
    root /var/www/medinext/public;
    index index.php index.html;

    # Health check endpoint
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }

    # Main application
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## Troubleshooting

### 1. Common Issues

#### Database Connection Issues
```bash
# Check MySQL status
sudo systemctl status mysql

# Check MySQL logs
sudo tail -f /var/log/mysql/error.log

# Test database connection
mysql -u medinext_user -p -h 127.0.0.1 medinext_production
```

#### Application Issues
```bash
# Check Laravel logs
tail -f /var/www/medinext/storage/logs/laravel.log

# Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log

# Check Nginx logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

#### Performance Issues
```bash
# Check system resources
htop
iotop
df -h
free -h

# Check Redis
redis-cli info memory
redis-cli info stats

# Check queue status
php artisan queue:work --once
```

### 2. Debugging Commands

#### Laravel Debugging
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check configuration
php artisan config:show

# Check routes
php artisan route:list

# Check database
php artisan db:show
php artisan migrate:status
```

#### System Debugging
```bash
# Check services
sudo systemctl status nginx
sudo systemctl status mysql
sudo systemctl status redis-server
sudo systemctl status php8.2-fpm

# Check ports
sudo netstat -tlnp | grep :80
sudo netstat -tlnp | grep :443
sudo netstat -tlnp | grep :3306
sudo netstat -tlnp | grep :6379
```

---

## Maintenance Procedures

### 1. Regular Maintenance

#### Daily Maintenance
```bash
# Check system health
/opt/monitoring/health-check.sh

# Check disk space
df -h

# Check memory usage
free -h

# Check running processes
ps aux | grep -E "(nginx|mysql|redis|php-fpm)"
```

#### Weekly Maintenance
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Clean package cache
sudo apt autoremove -y
sudo apt autoclean

# Rotate logs
sudo logrotate -f /etc/logrotate.conf

# Check backup integrity
sudo /opt/backup/db-backup.sh
sudo /opt/backup/file-backup.sh
```

#### Monthly Maintenance
```bash
# Security updates
sudo apt update && sudo apt upgrade -y

# Database optimization
mysql -u root -p -e "OPTIMIZE TABLE medinext_production.*;"

# Log analysis
sudo logwatch

# Performance review
php artisan system:performance-report
```

### 2. Update Procedures

#### Application Updates
```bash
# Backup before update
sudo /opt/backup/db-backup.sh
sudo /opt/backup/file-backup.sh

# Update code
cd /var/www/medinext
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run production

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

#### System Updates
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Restart services if needed
sudo systemctl restart nginx
sudo systemctl restart mysql
sudo systemctl restart redis-server
sudo systemctl restart php8.2-fpm
```

---

## Conclusion

This technical implementation guide provides comprehensive instructions for deploying and maintaining the Medinext EMR system. By following these procedures, you can ensure a robust, secure, and high-performance deployment.

### Key Success Factors
1. **Proper Planning**: Complete all setup steps in order
2. **Security First**: Implement security measures from the start
3. **Performance Optimization**: Configure for optimal performance
4. **Monitoring**: Implement comprehensive monitoring
5. **Backup Strategy**: Ensure reliable backup and recovery
6. **Regular Maintenance**: Follow maintenance procedures

### Support Resources
- **Documentation**: Technical documentation and guides
- **Community**: Developer community and forums
- **Support**: Technical support team
- **Training**: Technical training and certification

By following this guide, you can successfully deploy and maintain a production-ready Medinext EMR system that meets the highest standards of security, performance, and reliability.

---

**Document Version**: 1.0  
**Last Updated**: January 2024  
**Next Review**: July 2024
