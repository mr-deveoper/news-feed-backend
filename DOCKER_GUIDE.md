# 🐳 Docker Setup Guide - News Aggregator Backend

## Complete Docker Deployment Instructions

This project is **fully containerized** with Docker. Everything you need runs in containers - no local PHP, MySQL, or Redis required!

---

## 📋 Prerequisites

**Required:**
- Docker Desktop (Windows/Mac) or Docker Engine + Docker Compose (Linux)
- That's it! Everything else is containerized.

**Check if Docker is installed:**
```bash
docker --version
docker-compose --version
```

---

## 🚀 Quick Start (5 Minutes)

### Step 1: Create Environment File

Create `.env` in project root:

```env
# Application
APP_NAME="News Aggregator API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Docker Database (use these exact values)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=news_user
DB_PASSWORD=secret

# Docker Redis (use these exact values)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# News API Keys (get free from these URLs)
NEWS_API_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_key_here
NYTIMES_API_KEY=your_nytimes_key_here

# Frontend URL for CORS
FRONTEND_URL=http://localhost:3000
```

**Important**: Use `DB_HOST=mysql` and `REDIS_HOST=redis` (container names, not localhost)!

### Step 2: Get API Keys (5 minutes - all free)

**NewsAPI** (covers NewsAPI and BBC News):
- https://newsapi.org/ → Sign up → Get key → Add to .env

**The Guardian**:
- https://open-platform.theguardian.com/access/ → Register → Get key → Add to .env

**NY Times**:
- https://developer.nytimes.com/get-started → Create app → Get key → Add to .env

### Step 3: Start Docker

```bash
# Start all 6 containers
docker-compose up -d

# Wait for MySQL to be ready (~30 seconds)
docker-compose logs -f mysql
# Press Ctrl+C when you see "ready for connections"
```

### Step 4: Setup Application

```bash
# Install PHP dependencies
docker-compose exec app composer install

# Generate application key (updates .env)
docker-compose exec app php artisan key:generate

# Create database tables and seed data
docker-compose exec app php artisan migrate:fresh --seed

# Fetch news articles from all 5 sources
docker-compose exec app php artisan news:fetch

# Run tests to verify everything works
docker-compose exec app php artisan test
```

Expected output: ✅ **Tests: 21 passed**

### Step 5: Access Your API

✅ **API Base**: http://localhost:8000  
✅ **Articles**: http://localhost:8000/api/articles  
✅ **Health**: http://localhost:8000/up  

---

## 🎯 Docker Services Explained

Your `docker-compose.yml` creates 6 services:

### 1. **app** - Laravel Application
- Image: Custom (PHP 8.3-FPM)
- Purpose: Runs Laravel application
- Dependencies: mysql, redis

### 2. **nginx** - Web Server
- Image: nginx:alpine
- Port: 8000 → 80
- Purpose: Serves HTTP requests
- Depends on: app

### 3. **mysql** - Database
- Image: mysql:8.0
- Port: 3306
- Purpose: Stores all data
- Volume: Persistent storage

### 4. **redis** - Cache & Queue
- Image: redis:alpine
- Port: 6379
- Purpose: Caching and queue backend

### 5. **queue** - Queue Worker
- Image: Same as app
- Purpose: Processes background jobs
- Command: `php artisan queue:work --tries=3`

### 6. **scheduler** - Task Scheduler
- Image: Same as app
- Purpose: Runs scheduled tasks (news fetching every hour)
- Command: `php artisan schedule:work`

---

## 📊 Container Status

Check all containers are running:

```bash
docker-compose ps
```

Expected output:
```
news-feed-app        running
news-feed-nginx      running
news-feed-mysql      running
news-feed-redis      running
news-feed-queue      running
news-feed-scheduler  running
```

---

## 🔧 Common Docker Commands

### Container Management

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# Restart containers
docker-compose restart

# View logs (all containers)
docker-compose logs -f

# View logs (specific container)
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f mysql

# Rebuild containers
docker-compose up -d --build
```

### Application Commands

```bash
# Run any artisan command
docker-compose exec app php artisan [command]

# Examples:
docker-compose exec app php artisan migrate
docker-compose exec app php artisan news:fetch
docker-compose exec app php artisan test
docker-compose exec app php artisan route:list

# Access Tinker (Laravel REPL)
docker-compose exec app php artisan tinker

# Run Composer commands
docker-compose exec app composer install
docker-compose exec app composer update
```

### Database Commands

```bash
# Access MySQL shell
docker-compose exec mysql mysql -u news_user -psecret news_feed

# Run SQL queries
docker-compose exec mysql mysql -u news_user -psecret news_feed -e "SELECT COUNT(*) FROM articles;"

# Backup database
docker-compose exec mysql mysqldump -u news_user -psecret news_feed > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u news_user -psecret news_feed < backup.sql
```

### Redis Commands

```bash
# Access Redis CLI
docker-compose exec redis redis-cli

# Check cache keys
docker-compose exec redis redis-cli KEYS '*'

# Clear all cache
docker-compose exec redis redis-cli FLUSHALL
```

---

## 🔄 Development Workflow

### Daily Development

**Start your day:**
```bash
# Start all containers
docker-compose up -d

# Check status
docker-compose ps

# View application logs
docker-compose logs -f app
```

**During development:**
```bash
# Run migrations after database changes
docker-compose exec app php artisan migrate

# Fetch latest news
docker-compose exec app php artisan news:fetch

# Run tests after code changes
docker-compose exec app php artisan test

# Format code
docker-compose exec app vendor/bin/pint
```

**End of day:**
```bash
# Stop containers (keeps data)
docker-compose stop

# Or remove containers (keeps volumes)
docker-compose down
```

---

## 🐛 Troubleshooting Docker

### Problem: Containers won't start

**Solution**: Check Docker Desktop is running
```bash
docker ps
```

### Problem: "port is already allocated"

**Solution**: Stop conflicting services or change ports in `docker-compose.yml`
```bash
# Check what's using port 8000
netstat -ano | findstr :8000  # Windows
lsof -i :8000                # Mac/Linux

# Change nginx port in docker-compose.yml
ports:
  - "8080:80"  # Use 8080 instead
```

### Problem: MySQL container keeps restarting

**Solution**: Check MySQL logs
```bash
docker-compose logs mysql

# Reset MySQL volume if corrupted
docker-compose down -v
docker-compose up -d
```

### Problem: "No such file or directory" errors

**Solution**: Ensure you're in the project directory
```bash
# Should be in: C:\wamp64\www\news-feed-backend
pwd
ls docker-compose.yml  # Should exist
```

### Problem: Permission errors on Linux

**Solution**: Fix file ownership
```bash
sudo chown -R $USER:$USER .
docker-compose down
docker-compose up -d
```

### Problem: App container exits immediately

**Solution**: Check application logs
```bash
docker-compose logs app

# Common fixes:
# 1. .env file exists
# 2. APP_KEY is generated
# 3. Database credentials correct
```

### Problem: News fetching returns 0 articles

**Solution**: Check API keys
```bash
docker-compose exec app php artisan tinker
>>> config('services.newsapi.api_key')
>>> config('services.guardian.api_key')
>>> config('services.nytimes.api_key')
# Should not be null or empty
```

---

## 🔍 Inspecting Containers

### View Container Details

```bash
# List all containers
docker-compose ps

# Inspect specific container
docker inspect news-feed-app

# Check resource usage
docker stats

# View container processes
docker-compose top
```

### Execute Commands Inside Containers

```bash
# Get shell access to app container
docker-compose exec app bash

# Once inside, you can run commands directly:
php artisan about
php artisan route:list
php artisan tinker
exit
```

### File System Access

```bash
# View files in container
docker-compose exec app ls -la /var/www/html

# Copy files from container
docker cp news-feed-app:/var/www/html/storage/logs/laravel.log ./local-log.txt

# Copy files to container
docker cp ./some-file.txt news-feed-app:/var/www/html/
```

---

## 🧹 Cleanup Commands

### Stop and Remove Containers

```bash
# Stop containers (keeps volumes)
docker-compose down

# Stop and remove volumes (fresh start)
docker-compose down -v

# Stop, remove containers and images
docker-compose down --rmi all

# Remove unused Docker resources
docker system prune -a
```

### Fresh Start

```bash
# Complete reset
docker-compose down -v
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate:fresh --seed
docker-compose exec app php artisan news:fetch
```

---

## 🚀 Production Docker Deployment

### Update docker-compose for Production

Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    restart: always
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    volumes:
      - ./storage:/var/www/html/storage

  nginx:
    image: nginx:alpine
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html:ro
      - ./docker/nginx/conf.d:/etc/nginx/conf.d:ro
      - /etc/letsencrypt:/etc/letsencrypt:ro

  mysql:
    image: mysql:8.0
    restart: always
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:alpine
    restart: always
    command: redis-server --requirepass ${REDIS_PASSWORD}

volumes:
  mysql_data:
```

Deploy:
```bash
docker-compose -f docker-compose.prod.yml up -d
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker-compose -f docker-compose.prod.yml exec app php artisan optimize
```

---

## 📊 Monitoring with Docker

### View Logs in Real-Time

```bash
# All containers
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f mysql
docker-compose logs -f scheduler

# Last 100 lines
docker-compose logs --tail=100 app
```

### Check Resource Usage

```bash
# Monitor CPU, memory, network
docker stats

# Check disk usage
docker system df
```

### Health Checks

```bash
# Check all containers are healthy
docker-compose ps

# Test API
curl http://localhost:8000
curl http://localhost:8000/api/articles

# Check database
docker-compose exec mysql mysql -u news_user -psecret news_feed -e "SHOW TABLES;"
```

---

## 🔄 Updating the Application

### Pull Latest Code

```bash
# Stop containers
docker-compose down

# Pull latest code
git pull

# Rebuild and start
docker-compose up -d --build

# Run migrations
docker-compose exec app php artisan migrate --force

# Clear cache
docker-compose exec app php artisan optimize:clear
```

---

## 💡 Docker Tips & Tricks

### Speed Up Builds

Add `.dockerignore`:
```
vendor/
node_modules/
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
.env
.git/
```

### Optimize Images

```bash
# Build with no cache (fresh build)
docker-compose build --no-cache

# Remove unused images
docker image prune -a
```

### Development vs Production

**Development** (`docker-compose.yml`):
- Mounts local code directory
- Enables debug mode
- Uses local .env
- Hot reload possible

**Production** (`docker-compose.prod.yml`):
- Copies code into image
- Debug disabled
- Optimized autoloader
- Cached config/routes

---

## 🎯 Docker Networking

### Access Between Containers

Containers communicate using service names:
- App connects to **mysql** (not localhost)
- App connects to **redis** (not 127.0.0.1)
- Nginx proxies to **app:9000**

### Access from Host Machine

- API: http://localhost:8000
- MySQL: localhost:3306
- Redis: localhost:6379

### Custom Network

The `news-feed-network` is created automatically and allows all services to communicate.

---

## 📦 Docker Volumes

### Persistent Data

```bash
# List volumes
docker volume ls

# Inspect MySQL volume
docker volume inspect news-feed-backend_mysql_data

# Backup volume
docker run --rm -v news-feed-backend_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql_backup.tar.gz /data

# Remove all volumes (WARNING: deletes data)
docker-compose down -v
```

---

## 🔧 Advanced Docker Usage

### Run One-Off Commands

```bash
# Run migration in new container
docker-compose run --rm app php artisan migrate

# Run seeder
docker-compose run --rm app php artisan db:seed

# Create new migration
docker-compose exec app php artisan make:migration create_new_table
```

### Scale Services

```bash
# Run multiple queue workers
docker-compose up -d --scale queue=3

# Check scaled services
docker-compose ps
```

### Use Different Compose File

```bash
# Use custom compose file
docker-compose -f docker-compose.dev.yml up -d

# Use multiple compose files
docker-compose -f docker-compose.yml -f docker-compose.override.yml up -d
```

---

## 🛠️ Docker Troubleshooting Checklist

- [ ] Docker Desktop is running
- [ ] No port conflicts (8000, 3306, 6379)
- [ ] `.env` file exists with correct values
- [ ] `DB_HOST=mysql` (not localhost)
- [ ] `REDIS_HOST=redis` (not 127.0.0.1)
- [ ] API keys are added to `.env`
- [ ] Containers are running: `docker-compose ps`
- [ ] No error logs: `docker-compose logs`

---

## 📊 Useful Docker Commands Reference

```bash
# == Container Management ==
docker-compose up -d              # Start detached
docker-compose down               # Stop and remove
docker-compose restart            # Restart all
docker-compose ps                 # List containers
docker-compose stop               # Stop (keep containers)
docker-compose start              # Start stopped containers

# == Logs ==
docker-compose logs -f            # Follow all logs
docker-compose logs -f app        # Follow app logs
docker-compose logs --tail=100    # Last 100 lines

# == Execute Commands ==
docker-compose exec app bash      # Shell access
docker-compose exec app php artisan test
docker-compose run --rm app [cmd] # One-off command

# == Database ==
docker-compose exec mysql mysql -u news_user -psecret news_feed
docker-compose exec mysql mysqldump -u news_user -psecret news_feed

# == Redis ==
docker-compose exec redis redis-cli
docker-compose exec redis redis-cli FLUSHALL

# == Rebuild ==
docker-compose build              # Rebuild images
docker-compose up -d --build      # Rebuild and start
docker-compose build --no-cache   # Fresh rebuild

# == Cleanup ==
docker-compose down -v            # Remove volumes
docker system prune -a            # Clean everything
docker volume prune               # Remove unused volumes
```

---

## 🎯 Testing with Docker

### Run Tests

```bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test
docker-compose exec app php artisan test --filter=AuthenticationTest

# Run with coverage
docker-compose exec app php artisan test --coverage
```

### Test Database

```bash
# Access test database
docker-compose exec app php artisan tinker

# Inside tinker:
>>> App\Models\Article::count();
>>> App\Models\Source::all();
>>> App\Models\User::first();
```

---

## 🚀 Deploying to Production with Docker

### Step 1: Prepare Production Environment

Create `.env.production`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

DB_HOST=mysql
DB_DATABASE=news_feed
DB_USERNAME=news_user
DB_PASSWORD=very_strong_password_here

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_PASSWORD=strong_redis_password

# Production API keys
NEWS_API_KEY=prod_key
GUARDIAN_API_KEY=prod_key
NYTIMES_API_KEY=prod_key

FRONTEND_URL=https://yourdomain.com
```

### Step 2: Deploy

```bash
# Build for production
docker-compose -f docker-compose.prod.yml build

# Start production containers
docker-compose -f docker-compose.prod.yml up -d

# Setup application
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker-compose -f docker-compose.prod.yml exec app php artisan db:seed --force
docker-compose -f docker-compose.prod.yml exec app php artisan optimize
docker-compose -f docker-compose.prod.yml exec app php artisan news:fetch
```

### Step 3: Setup SSL (Optional)

Use Let's Encrypt with Certbot:
```bash
# Get SSL certificate
docker run -it --rm \
  -v /etc/letsencrypt:/etc/letsencrypt \
  -v /var/lib/letsencrypt:/var/lib/letsencrypt \
  certbot/certbot certonly --standalone \
  -d api.yourdomain.com

# Update nginx config to use SSL
# Then restart nginx
docker-compose restart nginx
```

---

## 📈 Scaling with Docker

### Horizontal Scaling

```bash
# Run multiple app instances
docker-compose up -d --scale app=3

# Run multiple queue workers
docker-compose up -d --scale queue=5

# Use load balancer (nginx)
# Update nginx config for upstream
```

### Resource Limits

Add to `docker-compose.yml`:
```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          memory: 512M
```

---

## 🎯 Docker Best Practices

### Development
✅ Use volume mounts for code (hot reload)  
✅ Enable debug mode  
✅ Use docker-compose.yml  
✅ Keep containers running  

### Production
✅ Copy code into image (no volumes)  
✅ Disable debug mode  
✅ Use environment variables  
✅ Set resource limits  
✅ Use restart policies  
✅ Enable SSL/TLS  
✅ Use secrets for sensitive data  

---

## 🎊 Success Indicators

Your Docker setup is working if:
- ✅ `docker-compose ps` shows all containers running
- ✅ `curl http://localhost:8000` returns JSON
- ✅ `curl http://localhost:8000/api/articles` returns articles
- ✅ `docker-compose exec app php artisan test` shows 21 passing
- ✅ Logs show no errors: `docker-compose logs`

---

## 📚 Next Steps

1. ✅ Docker setup complete
2. 📮 Import `postman_collection.json`
3. 🧪 Test all endpoints
4. 🎨 Build React frontend
5. 🔗 Connect frontend to API

---

## 🆘 Getting Help

### Check These First:
```bash
# 1. Container status
docker-compose ps

# 2. Application logs
docker-compose logs -f app

# 3. Laravel diagnostics
docker-compose exec app php artisan about

# 4. Database connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo()
```

### Common Issues:
- **Port conflicts**: Change ports in docker-compose.yml
- **Container exits**: Check logs with `docker-compose logs [service]`
- **Database errors**: Verify DB_HOST=mysql in .env
- **Cache issues**: `docker-compose exec app php artisan cache:clear`

---

## 🎉 You're Running on Docker!

**Everything is containerized and working!**

- ✅ No local PHP installation needed
- ✅ No local MySQL needed
- ✅ No local Redis needed
- ✅ Consistent environment
- ✅ Easy to deploy
- ✅ Easy to scale

**Your API**: http://localhost:8000  
**Status**: Ready for development! 🚀

---

**Next**: Import `postman_collection.json` and start testing your API!

