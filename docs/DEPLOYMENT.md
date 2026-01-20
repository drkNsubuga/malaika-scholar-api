# Deployment Guide

This guide covers deploying the Malaika Backend API to production environments.

## Pre-Deployment Checklist

### Environment Setup
- [ ] Set `APP_ENV=production`
- [ ] Configure production database (MySQL/PostgreSQL)
- [ ] Set up Redis for caching and queues
- [ ] Configure AWS S3 for file storage
- [ ] Set up Elasticsearch for search (optional)
- [ ] Configure email service (SMTP/SES)
- [ ] Set up SSL certificates

### Security Configuration
- [ ] Generate strong `APP_KEY`
- [ ] Configure Pesapal production credentials
- [ ] Set up proper CORS policies
- [ ] Configure rate limiting
- [ ] Enable security headers
- [ ] Set up monitoring and logging

### Performance Optimization
- [ ] Enable OPcache
- [ ] Configure Redis caching
- [ ] Set up queue workers
- [ ] Optimize database indexes
- [ ] Configure CDN for static assets

## Server Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **Memory**: 512MB RAM minimum, 2GB recommended
- **Storage**: 10GB minimum, SSD recommended
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx or Apache with mod_rewrite

### Recommended Stack
- **OS**: Ubuntu 22.04 LTS
- **Web Server**: Nginx 1.20+
- **PHP**: PHP 8.2 with FPM
- **Database**: MySQL 8.0 or PostgreSQL 14
- **Cache**: Redis 6.0+
- **Process Manager**: Supervisor

## Deployment Steps

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 and extensions
sudo apt install php8.2-fpm php8.2-mysql php8.2-redis php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-gd

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js (for admin interface)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs
```

### 2. Application Deployment

```bash
# Clone repository
git clone https://github.com/your-org/malaika-backend.git
cd malaika-backend

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --production

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --force
php artisan db:seed --force

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 3. Web Server Configuration

#### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com;
    root /var/www/malaika-backend/public;

    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    index index.php;

    charset utf-8;

    # Handle API routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Security
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # File upload limits
    client_max_body_size 10M;
}
```

### 4. Process Management

#### Supervisor Configuration

```ini
# /etc/supervisor/conf.d/malaika-worker.conf
[program:malaika-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/malaika-backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/malaika-backend/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start malaika-worker:*
```

### 5. Cron Jobs

```bash
# Add to crontab (crontab -e)
* * * * * cd /var/www/malaika-backend && php artisan schedule:run >> /dev/null 2>&1
```

## Environment Variables

### Production .env Template

```env
APP_NAME="Malaika Backend API"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://api.your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=malaika_production
DB_USERNAME=malaika_user
DB_PASSWORD=secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# AWS S3 Configuration
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=malaika-production-files

# Pesapal Production
PESAPAL_ENVIRONMENT=live
PESAPAL_CONSUMER_KEY=your_production_key
PESAPAL_CONSUMER_SECRET=your_production_secret
PESAPAL_CALLBACK_URL=https://api.your-domain.com/api/payments/pesapal/callback
PESAPAL_IPN_URL=https://api.your-domain.com/api/payments/pesapal/ipn

# Email Configuration
MAIL_MAILER=ses
MAIL_HOST=email-smtp.us-east-1.amazonaws.com
MAIL_PORT=587
MAIL_USERNAME=your_ses_username
MAIL_PASSWORD=your_ses_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Security
SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com
SESSION_SECURE_COOKIE=true
```

## Monitoring & Maintenance

### Health Checks

```bash
# Application health
curl https://api.your-domain.com/up

# Database connectivity
php artisan tinker --execute="DB::connection()->getPdo();"

# Queue status
php artisan queue:monitor redis:default --max=100

# Cache status
php artisan cache:table
```

### Log Management

```bash
# Rotate logs daily
sudo logrotate -f /etc/logrotate.d/malaika

# Monitor error logs
tail -f storage/logs/laravel.log

# Monitor web server logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### Backup Strategy

```bash
# Database backup
mysqldump -u username -p malaika_production > backup_$(date +%Y%m%d).sql

# File backup
aws s3 sync s3://malaika-production-files s3://malaika-backups/files/$(date +%Y%m%d)/

# Application backup
tar -czf malaika_app_$(date +%Y%m%d).tar.gz /var/www/malaika-backend
```

## Scaling Considerations

### Horizontal Scaling
- Load balancer configuration
- Session storage in Redis
- File storage on S3
- Database read replicas

### Performance Optimization
- Redis caching strategy
- Database query optimization
- CDN for static assets
- Image optimization

### Security Hardening
- Firewall configuration
- Intrusion detection
- Regular security updates
- SSL/TLS optimization

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check PHP error logs
   - Verify file permissions
   - Check .env configuration

2. **Queue Jobs Not Processing**
   - Restart supervisor workers
   - Check Redis connectivity
   - Verify queue configuration

3. **File Upload Issues**
   - Check storage permissions
   - Verify S3 credentials
   - Check upload size limits

4. **Payment Processing Errors**
   - Verify Pesapal credentials
   - Check IPN URL accessibility
   - Review payment logs

For additional support, contact the development team.