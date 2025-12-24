# Deployment Guide - News Aggregator Backend

## Production Deployment Checklist

### Pre-Deployment

- [ ] All API keys obtained (NewsAPI, Guardian, NY Times)
- [ ] Production database created
- [ ] Redis installed (recommended)
- [ ] Domain/subdomain configured
- [ ] SSL certificate obtained
- [ ] Mail service configured (for password resets)

### Environment Configuration

Update `.env` for production:

```env
APP_NAME="News Aggregator API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=news_feed_prod
DB_USERNAME=news_user
DB_PASSWORD=strong_password_here

# Cache & Queue (Redis recommended)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@yourdomain.com

# News APIs
NEWS_API_KEY=production_key
GUARDIAN_API_KEY=production_key
NYTIMES_API_KEY=production_key

# Frontend
FRONTEND_URL=https://yourdomain.com
```

---

## Option 1: Traditional Server Deployment

### Requirements
- Ubuntu 20.04/22.04 LTS
- PHP 8.3+ with extensions
- MySQL 8.0+
- Nginx
- Redis (optional but recommended)
- Supervisor (for queue workers)

### Step 1: Install Dependencies

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3
sudo add-apt-repository ppa:ondrej/php
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-redis

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Nginx
sudo apt install -y nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Supervisor
sudo apt install -y supervisor
```

### Step 2: Setup Application

```bash
# Clone repository
cd /var/www
git clone your-repo-url news-feed-backend
cd news-feed-backend

# Install dependencies
composer install --no-dev --optimize-autoloader

# Setup environment
cp .env.example .env
nano .env  # Configure production settings

# Generate key
php artisan key:generate

# Set permissions
sudo chown -R www-data:www-data /var/www/news-feed-backend
sudo chmod -R 755 /var/www/news-feed-backend
sudo chmod -R 775 /var/www/news-feed-backend/storage
sudo chmod -R 775 /var/www/news-feed-backend/bootstrap/cache

# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 3: Configure Nginx

Create `/etc/nginx/sites-available/news-feed-api`:

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    root /var/www/news-feed-backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/news-feed-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 4: Setup SSL with Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d api.yourdomain.com
```

### Step 5: Configure Supervisor for Queue Workers

Create `/etc/supervisor/conf.d/news-feed-worker.conf`:

```ini
[program:news-feed-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/news-feed-backend/artisan queue:work --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/news-feed-backend/storage/logs/worker.log
```

Create `/etc/supervisor/conf.d/news-feed-scheduler.conf`:

```ini
[program:news-feed-scheduler]
command=php /var/www/news-feed-backend/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/news-feed-backend/storage/logs/scheduler.log
```

Start Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### Step 6: Configure Firewall

```bash
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

---

## Option 2: Docker Deployment

### Production Docker Setup

Update `docker-compose.yml` for production:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    volumes:
      - ./storage:/var/www/html/storage
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html:ro
      - ./docker/nginx/conf.d:/etc/nginx/conf.d:ro
      - ./ssl:/etc/nginx/ssl:ro

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: news_feed
      MYSQL_ROOT_PASSWORD: strong_password
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:alpine
    command: redis-server --requirepass your_redis_password
```

Deploy:
```bash
# Build and start
docker-compose -f docker-compose.yml up -d --build

# Run migrations
docker-compose exec app php artisan migrate --force

# Seed database
docker-compose exec app php artisan db:seed --force

# Optimize
docker-compose exec app php artisan optimize
```

---

## Monitoring & Maintenance

### Log Files
```bash
# Application logs
tail -f storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log

# Worker logs
sudo tail -f /var/www/news-feed-backend/storage/logs/worker.log
```

### Database Backup
```bash
# Backup
mysqldump -u root -p news_feed_prod > backup_$(date +%Y%m%d).sql

# Restore
mysql -u root -p news_feed_prod < backup_20241224.sql
```

### Clearing Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Monitoring Queue
```bash
# Check queue status
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

## Performance Tuning

### 1. Enable OPcache

In `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 2. Optimize MySQL

```sql
-- Increase buffer pool for InnoDB
SET GLOBAL innodb_buffer_pool_size = 1G;

-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
```

### 3. Redis Configuration

In `redis.conf`:
```
maxmemory 256mb
maxmemory-policy allkeys-lru
```

### 4. Laravel Optimization

```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Use OPcache
composer install --optimize-autoloader --no-dev
```

---

## Security Best Practices

### 1. Environment Variables
- Never commit `.env` to version control
- Use strong, unique passwords
- Rotate API keys regularly

### 2. Database Security
- Use non-root database user
- Grant only necessary privileges
- Regular backups

### 3. Server Security
- Keep system updated
- Configure firewall
- Disable unnecessary services
- Use SSH keys, disable password auth

### 4. Application Security
- Enable HTTPS only
- Set `APP_DEBUG=false` in production
- Configure CORS properly
- Regular security updates

---

## Troubleshooting

### Issue: Queue not processing
```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart news-feed-worker:*
```

### Issue: News not fetching
```bash
# Test manually
php artisan news:fetch

# Check logs
tail -f storage/logs/laravel.log

# Verify API keys
php artisan tinker
>>> config('services.newsapi.api_key')
```

### Issue: High memory usage
```bash
# Clear cache
php artisan cache:clear

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Check Redis memory
redis-cli info memory
```

---

## Scaling Considerations

### Horizontal Scaling
- Use load balancer (Nginx/HAProxy)
- Separate database server
- Redis for sessions (sticky sessions or centralized)
- Separate queue workers on different servers

### Vertical Scaling
- Increase PHP-FPM workers
- Increase MySQL buffer pool
- More Redis memory
- SSD storage for database

---

## Update Procedure

```bash
# 1. Backup database
mysqldump -u root -p news_feed_prod > backup_$(date +%Y%m%d).sql

# 2. Enable maintenance mode
php artisan down

# 3. Pull latest code
git pull origin main

# 4. Install dependencies
composer install --no-dev --optimize-autoloader

# 5. Run migrations
php artisan migrate --force

# 6. Clear and cache
php artisan optimize:clear
php artisan optimize

# 7. Restart services
sudo supervisorctl restart all
sudo systemctl restart php8.3-fpm

# 8. Disable maintenance mode
php artisan up
```

---

## Production Checklist

- [ ] `.env` configured for production
- [ ] `APP_DEBUG=false`
- [ ] Database credentials secure
- [ ] All API keys added
- [ ] HTTPS enabled
- [ ] CORS configured
- [ ] Queue workers running
- [ ] Scheduler running (cron configured)
- [ ] Logs being monitored
- [ ] Backups automated
- [ ] Firewall configured
- [ ] Rate limiting configured
- [ ] Error tracking setup (optional: Sentry, Bugsnag)
- [ ] Application monitoring (optional: New Relic, DataDog)

---

**Deployment Status**: Ready for Production ✅

