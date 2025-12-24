# 🐳 HOW TO RUN - News Aggregator Backend (Docker)

## ⚡ Quick Start Guide (5 Minutes)

This is the **simplest, fastest way** to get your backend running using **Docker**.

**Why Docker?**
- ✅ No local PHP/MySQL/Redis installation needed
- ✅ Consistent environment across all machines
- ✅ Everything configured and ready to go
- ✅ Production-ready setup
- ✅ Easy to deploy

---

## Prerequisites Check

Make sure you have:
- ✅ **Docker Desktop** installed (Windows/Mac) or Docker Engine (Linux)
- ✅ **That's it!** Everything else is in containers.

Check Docker is running:
```bash
docker --version          # Should show Docker version
docker-compose --version  # Should show docker-compose version
docker ps                 # Should show running containers (if any)
```

**Download Docker**: https://www.docker.com/products/docker-desktop

---

## 🎯 Step-by-Step Instructions

### STEP 1: Configure Environment (2 minutes)

Create a `.env` file in the project root:

**Option A - Quick Copy:**
```bash
# Copy template
cp ENV_TEMPLATE.md .env
# Then edit .env and add your API keys
```

**Option B - Manual Creation:**

Create `.env` with these **Docker-specific** settings:

```env
APP_NAME="News Aggregator API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# DOCKER DATABASE SETTINGS (use these exact values)
DB_CONNECTION=mysql
DB_HOST=mysql              # Container name, not localhost!
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=news_user
DB_PASSWORD=secret

# DOCKER REDIS SETTINGS (use these exact values)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis          # Container name, not localhost!
REDIS_PASSWORD=null
REDIS_PORT=6379

# NEWS API KEYS (get free keys - see step 2)
NEWS_API_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_key_here
NYTIMES_API_KEY=your_nytimes_key_here

# FRONTEND URL
FRONTEND_URL=http://localhost:3000
```

**Important for Docker**:
- Use `DB_HOST=mysql` (container name, not localhost)
- Use `REDIS_HOST=redis` (container name, not 127.0.0.1)
- Use `CACHE_STORE=redis` and `QUEUE_CONNECTION=redis`

### STEP 2: Get Free API Keys (5 minutes - ALL FREE)

**NewsAPI** (1 minute - Powers 3 sources):
1. Visit https://newsapi.org/
2. Click "Get API Key" → Enter name/email
3. Get instant key!
4. Add to `.env`: `NEWS_API_KEY=your_key`
5. **This key powers**: NewsAPI, BBC News, OpenNews (3 sources!)

**The Guardian** (2 minutes):
1. Visit https://open-platform.theguardian.com/access/
2. Fill the developer key form
3. Get instant key!
4. Add to `.env`: `GUARDIAN_API_KEY=your_key`
5. **This key powers**: The Guardian (1 source)

**NY Times** (2 minutes):
1. Visit https://developer.nytimes.com/get-started
2. Create account → Create app → Enable "Article Search API"
3. Get API key
4. Add to `.env`: `NYTIMES_API_KEY=your_key`
5. **This key powers**: New York Times (1 source)

**Total**: 3 API keys → 5 news sources! 🎯

### STEP 3: Start Docker Containers (1 minute)

```bash
# Start all 6 Docker services
docker-compose up -d

# Check all containers are running
docker-compose ps
```

You should see 6 containers running:
- news-feed-app
- news-feed-nginx
- news-feed-mysql
- news-feed-redis
- news-feed-queue
- news-feed-scheduler

### STEP 4: Setup Application in Docker (2 minutes)

```bash
# Install PHP dependencies
docker-compose exec app composer install

# Generate Laravel application key
docker-compose exec app php artisan key:generate

# Create database tables and seed initial data
docker-compose exec app php artisan migrate:fresh --seed

# Fetch news from all 5 sources
docker-compose exec app php artisan news:fetch
```

Expected output:
```
Fetched: 250+ articles
Saved: 250+ new articles
Skipped: 0 duplicates
```

### STEP 5: Verify Everything Works (30 seconds)

```bash
# Run all 21 tests
docker-compose exec app php artisan test
```

Expected: ✅ **Tests: 21 passed (117 assertions)**

---

## ✅ Verify It's Working (Docker)

### Test 1: Check API Root
```bash
curl http://localhost:8000
```

Should return JSON with API info ✅

### Test 2: Get Articles
```bash
curl http://localhost:8000/api/articles?per_page=5
```

Should return 5 articles with full details ✅

### Test 3: Get Sources
```bash
curl http://localhost:8000/api/sources
```

Should return **5 sources**: NewsAPI, The Guardian, NY Times, BBC News, OpenNews ✅

### Test 4: Run Tests in Docker
```bash
docker-compose exec app php artisan test
```

Should show: **Tests: 21 passed (117 assertions)** ✅

### Test 5: Check Database in Docker
```bash
docker-compose exec app php artisan tinker
```

Then type:
```php
Article::count();  // Should show 100+ articles
Source::count();   // Should show 5 sources
User::count();     // Should show 6 users
exit
```

### Test 6: View Docker Logs
```bash
# View all logs
docker-compose logs -f

# View app logs only
docker-compose logs -f app

# View MySQL logs
docker-compose logs -f mysql
```

### Test 7: Check All Containers Running
```bash
docker-compose ps
```

Should show 6 containers all "Up" ✅

---

## 📮 Test with Postman (Recommended)

1. Open Postman
2. Click "Import"
3. Select `postman_collection.json` from the project root
4. Set environment variable:
   - Variable: `base_url`
   - Value: `http://localhost:8000`
5. Test endpoints in this order:
   - ✅ Register (creates user & token)
   - ✅ Login (saves token automatically)
   - ✅ Get Articles (see articles from all 5 sources)
   - ✅ Get Personalized Feed
   - ✅ Update Preferences

---

## 🐳 Useful Docker Commands

### Daily Operations

```bash
# View all container logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f scheduler

# Restart all containers
docker-compose restart

# Restart specific service
docker-compose restart app

# Stop all containers (keeps data)
docker-compose stop

# Start stopped containers
docker-compose start
```

### Application Commands

```bash
# Run any artisan command
docker-compose exec app php artisan [command]

# Common commands:
docker-compose exec app php artisan news:fetch    # Fetch news
docker-compose exec app php artisan route:list     # List routes
docker-compose exec app php artisan migrate       # Run migrations
docker-compose exec app php artisan test          # Run tests
docker-compose exec app php artisan cache:clear   # Clear cache
docker-compose exec app php artisan tinker        # Interactive shell
```

### Database Operations

```bash
# Access MySQL in Docker
docker-compose exec mysql mysql -u news_user -psecret news_feed

# Backup database
docker-compose exec mysql mysqldump -u news_user -psecret news_feed > backup.sql

# Check articles
docker-compose exec app php artisan tinker --execute="echo App\Models\Article::count();"
```

### Rebuild Containers

```bash
# Rebuild all containers
docker-compose down
docker-compose up -d --build

# Fresh database
docker-compose exec app php artisan migrate:fresh --seed
```

---

## 🔧 Troubleshooting

### Problem: "SQLSTATE[HY000] [1045] Access denied"
**Solution**: Check MySQL username/password in `.env`

```bash
# Test MySQL connection
mysql -u root -p

# If connection works, update .env with correct credentials
```

### Problem: "Base table or view not found"
**Solution**: Run migrations

```bash
php artisan migrate:fresh --seed
```

### Problem: "No articles in database"
**Solution**: Fetch news articles

```bash
php artisan news:fetch
```

### Problem: "Class not found" errors
**Solution**: Regenerate autoload

```bash
composer dump-autoload
```

### Problem: "Permission denied" on storage
**Solution**: Fix permissions

```bash
chmod -R 775 storage bootstrap/cache
```

### Problem: News fetching returns 0 articles
**Solution**: Check API keys

```bash
php artisan tinker
>>> config('services.newsapi.api_key')
>>> config('services.guardian.api_key')
>>> config('services.nytimes.api_key')
```

Make sure they're not null or empty.

---

## 📞 Need Help?

### Check These First:
1. **SETUP_INSTRUCTIONS.md** - Detailed troubleshooting
2. **storage/logs/laravel.log** - Error logs
3. `php artisan about` - System diagnostics
4. `php artisan route:list` - Verify routes loaded

### Common Commands:
```bash
# Clear all caches
php artisan optimize:clear

# View all routes
php artisan route:list

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo()

# View recent logs
tail -n 50 storage/logs/laravel.log
```

---

## ✅ Success Checklist

Your setup is successful if:

- [ ] `php artisan test` shows 21 tests passing
- [ ] `curl http://localhost:8000` returns JSON
- [ ] `curl http://localhost:8000/api/articles` returns articles
- [ ] Database has articles (check with `php artisan tinker` → `Article::count()`)
- [ ] No errors in `storage/logs/laravel.log`
- [ ] `php artisan about` shows all green

If all checked ✅ - **YOU'RE READY TO GO!**

---

## 🎯 What to Do Next

### Option A: Test with Postman
1. Import `postman_collection.json`
2. Test all 20 endpoints
3. Understand the API

### Option B: Start Frontend Development
1. Read `API_DOCUMENTATION.md`
2. Use the provided JavaScript examples
3. Build your React components
4. Integrate with these APIs

### Option C: Explore the Code
1. Read `PROJECT_COMPLETE.md`
2. Explore `app/` directory
3. Understand the architecture
4. Customize as needed

---

## 🎁 What You Can Do Right Now

### Fetch Real News
```bash
php artisan news:fetch
```

Gets latest articles from all 3 sources!

### Browse Articles
```bash
# In browser
http://localhost:8000/api/articles

# Or with curl
curl http://localhost:8000/api/articles?keyword=technology
```

### Create Test User
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@test.com",
    "password": "Password123",
    "password_confirmation": "Password123"
  }'
```

---

## 📊 Quick Stats

**After setup, your database has:**
- 3 News Sources
- 10 Categories  
- 60+ Sample Articles
- 6 Test Users
- 10+ Authors

**Your API has:**
- 20 Endpoints
- Full authentication
- Search & filtering
- Personalized feeds
- **All working!** ✅

---

## 🎊 You're All Set!

Your backend is:
- ✅ Running
- ✅ Tested
- ✅ Ready for frontend
- ✅ Ready for production (when you are)

**Time to build your React frontend!**

---

## 📚 Documentation Navigator

| What You Need | Read This File |
|---------------|----------------|
| **Overview** | 🎉_READ_ME_FIRST.md |
| **Setup** | SETUP_INSTRUCTIONS.md |
| **API Reference** | API_DOCUMENTATION.md |
| **Quick Commands** | QUICKSTART.md |
| **Deployment** | DEPLOYMENT_GUIDE.md |
| **Summary** | 📋_COMPLETE_PROJECT_SUMMARY.md |

---

## 🔄 Daily Development Workflow

### Morning (Start servers):
```bash
# Terminal 1
php artisan serve

# Terminal 2  
php artisan schedule:work
```

### During Development:
```bash
# Test changes
php artisan test

# Fetch fresh news
php artisan news:fetch

# Check routes
php artisan route:list

# Database queries
php artisan tinker
```

### Before Committing:
```bash
# Run tests
php artisan test

# Format code
vendor/bin/pint

# Clear cache
php artisan optimize:clear
```

---

## 🏁 Ready?

**You have everything you need!**

### Your Next Command:
```bash
php artisan serve
```

Then open: `http://localhost:8000/api/articles`

**See articles?** ✅ **YOU'RE RUNNING!**

---

**Questions?** → Check **SETUP_INSTRUCTIONS.md**  
**API Help?** → Check **API_DOCUMENTATION.md**  
**Deployment?** → Check **DEPLOYMENT_GUIDE.md**  

## 🚀 GO BUILD YOUR FRONTEND!

**Happy Coding!** 🎉

