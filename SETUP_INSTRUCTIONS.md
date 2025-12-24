# 📋 Setup Instructions - News Aggregator Backend

## Prerequisites

Before you begin, ensure you have:
- ✅ PHP 8.3 or higher
- ✅ Composer
- ✅ MySQL 8.0 or higher
- ✅ (Optional) Redis for caching
- ✅ (Optional) Docker & Docker Compose

---

## 🚀 Quick Setup (5 Minutes)

### Step 1: Configure Environment

Create a `.env` file in the project root with the following content:

```env
APP_NAME="News Aggregator API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=root
DB_PASSWORD=your_mysql_password_here

# Optional: Use Redis for better performance
CACHE_STORE=file
QUEUE_CONNECTION=database

# News API Keys (Get free keys from the links below)
NEWS_API_KEY=get_from_https://newsapi.org
GUARDIAN_API_KEY=get_from_https://open-platform.theguardian.com
NYTIMES_API_KEY=get_from_https://developer.nytimes.com

# Frontend URL for CORS
FRONTEND_URL=http://localhost:3000
```

### Step 2: Create Database

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE news_feed CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 3: Install & Setup

```bash
# Install PHP dependencies
composer install

# Generate application key
php artisan key:generate

# Run migrations and seed data
php artisan migrate:fresh --seed

# Fetch initial news articles
php artisan news:fetch
```

### Step 4: Start the Server

```bash
# Start Laravel development server
php artisan serve

# In a separate terminal, start the scheduler
php artisan schedule:work
```

Your API is now running at **http://localhost:8000** 🎉

---

## 🐳 Docker Setup (Alternative)

If you prefer Docker:

### Step 1: Configure Environment

Create `.env` with Docker-specific settings:

```env
DB_HOST=mysql
DB_DATABASE=news_feed
DB_USERNAME=news_user
DB_PASSWORD=secret

REDIS_HOST=redis

# News API keys
NEWS_API_KEY=your_key
GUARDIAN_API_KEY=your_key
NYTIMES_API_KEY=your_key
```

### Step 2: Start Docker

```bash
# Start all containers
docker-compose up -d

# Install dependencies
docker-compose exec app composer install

# Generate key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate:fresh --seed

# Fetch news
docker-compose exec app php artisan news:fetch

# View logs
docker-compose logs -f app
```

Your API is running at **http://localhost:8000** 🎉

---

## 🔑 Getting Free API Keys

### NewsAPI.org (Free)
1. Visit: https://newsapi.org/
2. Click "Get API Key"
3. Sign up for free account
4. Copy your API key
5. Add to `.env`: `NEWS_API_KEY=your_key_here`

### The Guardian (Free)
1. Visit: https://open-platform.theguardian.com/access/
2. Click "Register a developer key"
3. Fill in the form
4. You'll receive your key instantly
5. Add to `.env`: `GUARDIAN_API_KEY=your_key_here`

### New York Times (Free Tier)
1. Visit: https://developer.nytimes.com/get-started
2. Create an account
3. Create a new app
4. Enable "Article Search API"
5. Copy your API key
6. Add to `.env`: `NYTIMES_API_KEY=your_key_here`

---

## ✅ Verification Steps

### 1. Test Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

### 2. Check Migrations
```bash
php artisan migrate:status
```

Should show all migrations as "Ran".

### 3. Test News Fetching
```bash
php artisan news:fetch
```

Should show statistics of articles fetched and saved.

### 4. Run Tests
```bash
php artisan test
```

Should show all 21 tests passing.

### 5. Test API Endpoints

**Test Registration:**
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

**Test Article Listing:**
```bash
curl http://localhost:8000/api/articles?per_page=5
```

**Test Root Endpoint:**
```bash
curl http://localhost:8000
```

---

## 🎨 Import Postman Collection

1. Open Postman
2. Click "Import"
3. Select `postman_collection.json`
4. Collection will appear in your sidebar
5. Set environment variable `base_url` to `http://localhost:8000`
6. Use "Login" request and token will be auto-saved for protected requests

---

## 🔄 Development Workflow

### Daily Development
```bash
# Start server
php artisan serve

# Start scheduler (for auto news fetching)
php artisan schedule:work

# Watch logs
tail -f storage/logs/laravel.log
```

### Making Changes
```bash
# Create new migration
php artisan make:migration create_new_table

# Run migrations
php artisan migrate

# Create new model
php artisan make:model ModelName -mfs

# Run tests after changes
php artisan test

# Format code
vendor/bin/pint
```

### Fetching News Manually
```bash
# Fetch from all sources
php artisan news:fetch

# Check articles in database
php artisan tinker
>>> \App\Models\Article::count();
>>> \App\Models\Article::latest()->first();
```

---

## 🐛 Troubleshooting

### Issue: Database connection error
**Solution**: Check your `.env` database credentials and ensure MySQL is running:
```bash
# Check MySQL status
sudo systemctl status mysql

# Start MySQL
sudo systemctl start mysql
```

### Issue: News fetching fails
**Solution**: Verify API keys in `.env` and check internet connection:
```bash
# Test API keys
php artisan tinker
>>> config('services.newsapi.api_key')
>>> config('services.guardian.api_key')
>>> config('services.nytimes.api_key')
```

### Issue: Permission denied errors
**Solution**: Fix storage permissions:
```bash
chmod -R 775 storage bootstrap/cache
```

### Issue: Tests failing
**Solution**: The tests use SQLite by default. Ensure you have migrations run:
```bash
php artisan config:clear
php artisan test
```

### Issue: CORS errors from frontend
**Solution**: Add your frontend URL to `config/cors.php`:
```php
'allowed_origins' => [
    'http://localhost:3000',  // Your React app URL
],
```

### Issue: Routes not found (404)
**Solution**: Clear route cache:
```bash
php artisan route:clear
php artisan route:list  # View all routes
```

---

## 📱 Frontend Integration

### React/Next.js Example

```javascript
// api.js
import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Add auth token to requests
api.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Auth functions
export const auth = {
    register: (data) => api.post('/auth/register', data),
    login: (data) => api.post('/auth/login', data),
    logout: () => api.post('/auth/logout'),
    getUser: () => api.get('/auth/user'),
};

// Article functions
export const articles = {
    getAll: (params) => api.get('/articles', { params }),
    getOne: (id) => api.get(`/articles/${id}`),
    getPersonalized: (params) => api.get('/articles/feed/personalized', { params }),
};

// Preference functions
export const preferences = {
    get: () => api.get('/preferences'),
    update: (data) => api.put('/preferences', data),
    updateSources: (sourceIds) => api.put('/preferences/sources', { source_ids: sourceIds }),
    updateCategories: (categoryIds) => api.put('/preferences/categories', { category_ids: categoryIds }),
};

// Category/Source functions
export const categories = {
    getAll: () => api.get('/categories'),
};

export const sources = {
    getAll: () => api.get('/sources'),
};

export default api;
```

---

## 📖 Documentation Reference

| Document | Purpose |
|----------|---------|
| **README.md** | Project overview and quick start |
| **API_DOCUMENTATION.md** | Complete API endpoint reference |
| **DEPLOYMENT_GUIDE.md** | Production deployment instructions |
| **QUICKSTART.md** | Quick reference guide |
| **PROJECT_COMPLETE.md** | Implementation summary |
| **SETUP_INSTRUCTIONS.md** | This file - detailed setup |

---

## ✅ Success Indicators

You've successfully set up the backend if:
- ✅ `php artisan test` shows 21 tests passing
- ✅ `curl http://localhost:8000` returns JSON response
- ✅ `curl http://localhost:8000/api/articles` returns articles
- ✅ Database has articles, sources, and categories
- ✅ No errors in `storage/logs/laravel.log`

---

## 🆘 Getting Help

1. **Check logs**: `tail -f storage/logs/laravel.log`
2. **Run diagnostics**: `php artisan about`
3. **List routes**: `php artisan route:list`
4. **Clear caches**: `php artisan optimize:clear`
5. **Check database**: `php artisan tinker` then run queries

---

## 🎯 Next Steps

1. ✅ Backend is complete and running
2. ⏭️ Import Postman collection for API testing
3. ⏭️ Build your React frontend
4. ⏭️ Integrate frontend with this API
5. ⏭️ Deploy to production

---

**Setup Time**: ~5 minutes  
**Status**: Ready for Development ✅  
**Last Updated**: December 24, 2025

