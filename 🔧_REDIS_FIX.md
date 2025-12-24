# 🔧 Redis Extension Fix

## ❌ Problem
```
Class "Redis" not found
```

**Cause**: PHP Redis extension not installed in Docker container

## ✅ Solution Applied

Updated `Dockerfile` to install PHP Redis extension:
- Added `pkg-config` (build dependency)
- Added `pecl install redis`
- Added `docker-php-ext-enable redis`

---

## 🚀 Fix Steps

### Step 1: Rebuild Docker Containers

```bash
# Stop containers
docker-compose down

# Rebuild with new Redis extension
docker-compose build --no-cache app

# Start everything
docker-compose up -d
```

### Step 2: Verify Redis Extension

```bash
# Check if Redis extension is installed
docker-compose exec app php -m | grep redis
```

Should output: `redis` ✅

### Step 3: Test API

```bash
# Test articles endpoint
curl http://localhost:8000/api/articles

# Should return articles (not 500 error)
```

---

## ✅ Expected Result

After rebuild:
- ✅ Redis extension installed
- ✅ API endpoints work (no 500 errors)
- ✅ Frontend can connect successfully
- ✅ Caching works properly

---

## 🔍 Verify Fix

```bash
# Check PHP extensions
docker-compose exec app php -m | grep -i redis

# Check Redis connection
docker-compose exec app php artisan tinker --execute="Cache::store('redis')->put('test', 'value', 60); echo Cache::store('redis')->get('test');"

# Test API
curl http://localhost:8000/api/articles
```

---

**After rebuild, your frontend should work!** 🎉

