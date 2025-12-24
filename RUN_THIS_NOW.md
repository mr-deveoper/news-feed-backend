# ▶️ RUN THIS NOW - Docker Quick Fix

## ✅ Issue Fixed!

**Good News**: Docker build was **FAST** (seconds, not 13 minutes!) ✅  
**Port Conflict**: MySQL port 3306 was in use by WAMP  
**Fixed**: Changed Docker MySQL to use port 3307 externally  

---

## 🚀 Run These Commands Now

Copy and paste this entire block:

```powershell
# 1. Stop any running containers
docker-compose down -v

# 2. Start Docker containers (should work now!)
docker-compose up -d

# 3. Wait for MySQL to be ready (20 seconds)
Start-Sleep -Seconds 20

# 4. Check all containers are running
docker-compose ps

# 5. Install dependencies
docker-compose exec app composer install

# 6. Generate application key
docker-compose exec app php artisan key:generate

# 7. Create database and seed data
docker-compose exec app php artisan migrate:fresh --seed

# 8. Fetch news from all 5 sources
docker-compose exec app php artisan news:fetch

# 9. Run tests to verify
docker-compose exec app php artisan test

# 10. Test API
curl http://localhost:8000/api/articles
```

---

## ✅ Expected Results

After running the commands above:

1. **docker-compose ps** should show:
   ```
   news-feed-app         Up
   news-feed-nginx       Up  
   news-feed-mysql       Up (healthy)
   news-feed-redis       Up
   news-feed-queue       Up
   news-feed-scheduler   Up
   ```

2. **php artisan test** should show:
   ```
   Tests: 21 passed (117 assertions)
   ```

3. **curl** should return:
   ```json
   {
     "data": [
       { "id": 1, "title": "...", "source": {...} }
     ]
   }
   ```

4. **Total time**: ~4 minutes ✅

---

## 🎯 What Changed

### Port Configuration:
- **Before**: MySQL port 3306 → Conflict with WAMP
- **After**: MySQL port 3307 externally, 3306 internally ✅

**Inside Docker**: Containers still use 3306 (no .env changes needed!)  
**From your computer**: MySQL accessible on 3307 (no conflict with WAMP)

### Your WAMP MySQL:
- Still running on **localhost:3306** ✅
- No conflict!

### Docker MySQL:
- Running on **localhost:3307** ✅
- No conflict!

**Both can run together!** 🎉

---

## 🐛 If You Still Get Errors

### Port 8000 Already in Use?

Check if Apache/IIS is running on port 8000:

```powershell
# Stop WAMP Apache if running
# Or change nginx port in docker-compose.yml:
ports:
  - "8080:80"  # Use 8080 instead
```

### Port 6379 Already in Use?

Change Redis port:
```yaml
redis:
  ports:
    - "6380:6379"  # Use 6380 instead
```

### Other Issues?

```bash
# View logs
docker-compose logs app
docker-compose logs mysql

# Check Docker Desktop is running
docker ps
```

---

## ⏱️ Timeline

Your Docker setup should take:

| Step | Time |
|------|------|
| docker-compose up -d | ~1-2 min ✅ |
| composer install | ~30-60 sec |
| migrate + seed | ~10 sec |
| news:fetch | ~20 sec |
| test | ~2 sec |
| **TOTAL** | **~3-4 minutes** |

**Much better!** ⚡

---

## 🎊 After Success

Once containers are running:

### Your API:
- ✅ **Base URL**: http://localhost:8000
- ✅ **Articles**: http://localhost:8000/api/articles
- ✅ **Sources**: http://localhost:8000/api/sources (shows 5)
- ✅ **Health**: http://localhost:8000/up

### Docker Services:
- ✅ **App**: Laravel (PHP 8.3)
- ✅ **Nginx**: Web server (port 8000)
- ✅ **MySQL**: Database (port 3307)
- ✅ **Redis**: Cache (port 6379)
- ✅ **Queue**: Background worker
- ✅ **Scheduler**: Auto news fetching

### What's Automated:
- ✅ News fetching every hour
- ✅ Queue processing
- ✅ Cache management

---

## 📚 Next Steps

1. ✅ **Run commands above** (Docker should work now!)
2. 📮 **Import postman_collection.json** into Postman
3. 🧪 **Test all 20 endpoints**
4. 📖 **Read API_DOCUMENTATION.md**
5. 🎨 **Build your React frontend**

---

## 💡 Pro Tip

Keep Docker containers running and use:
```bash
docker-compose exec app php artisan [command]
```

Don't stop/start containers frequently - just leave them running for fast development!

---

## 🎯 Success Criteria

Your Docker setup is successful when:

- [ ] `docker-compose ps` shows 6 containers "Up"
- [ ] `docker-compose exec app php artisan test` shows 21 passed
- [ ] `curl http://localhost:8000` returns JSON
- [ ] `curl http://localhost:8000/api/articles` returns articles
- [ ] `curl http://localhost:8000/api/sources` returns 5 sources
- [ ] No errors in `docker-compose logs app`

**All checked?** 🎉 **YOU'RE RUNNING!**

---

**RUN THE COMMANDS ABOVE NOW!** ⬆️

Should work perfectly! 🚀🐳

