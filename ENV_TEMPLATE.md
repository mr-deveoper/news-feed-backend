# Environment Configuration Template

## Create Your .env File

Copy this template to create your `.env` file in the project root.

```env
# ============================================
# APPLICATION
# ============================================
APP_NAME="News Aggregator API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

# ============================================
# LOGGING
# ============================================
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# ============================================
# DATABASE
# ============================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=root
DB_PASSWORD=

# ============================================
# SESSION & CACHE
# ============================================
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=file
CACHE_PREFIX=

# ============================================
# QUEUE
# ============================================
QUEUE_CONNECTION=database

# ============================================
# REDIS (Optional - Recommended for Production)
# ============================================
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# ============================================
# MAIL (For Password Reset)
# ============================================
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# ============================================
# NEWS API KEYS
# Get your free API keys from:
# - NewsAPI: https://newsapi.org/
# - Guardian: https://open-platform.theguardian.com/
# - NY Times: https://developer.nytimes.com/
# ============================================
NEWS_API_KEY=
GUARDIAN_API_KEY=
NYTIMES_API_KEY=

# ============================================
# FRONTEND (For CORS)
# ============================================
FRONTEND_URL=http://localhost:3000

# ============================================
# OPTIONAL SERVICES
# ============================================
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

---

## 📝 Setup Instructions

### Step 1: Create .env file
```bash
# Copy this template
cp ENV_TEMPLATE.md .env.local
# Then remove the markdown formatting and save as .env
```

Or create `.env` manually with the content above.

### Step 2: Configure Database
Replace these values:
```env
DB_DATABASE=news_feed          # Your database name
DB_USERNAME=root               # Your MySQL username
DB_PASSWORD=your_password      # Your MySQL password
```

### Step 3: Add API Keys
Get free API keys (see links above) and add them:
```env
NEWS_API_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_key_here
NYTIMES_API_KEY=your_nytimes_key_here
```

### Step 4: Generate Application Key
```bash
php artisan key:generate
```

This will automatically set the `APP_KEY` in your `.env` file.

---

## 🐳 Docker Environment

If using Docker, modify these values:

```env
DB_HOST=mysql              # Container name instead of 127.0.0.1
DB_DATABASE=news_feed
DB_USERNAME=news_user
DB_PASSWORD=secret

REDIS_HOST=redis           # Container name
CACHE_STORE=redis          # Use Redis in Docker
QUEUE_CONNECTION=redis     # Use Redis for queues
```

---

## 🔧 Environment Options

### Cache Drivers
- `file` - File-based (default, no setup needed)
- `database` - Database cache
- `redis` - Redis cache (recommended for production)

### Queue Drivers
- `sync` - Synchronous (no queue)
- `database` - Database queue
- `redis` - Redis queue (recommended for production)

### Log Channels
- `stack` - Multiple channels
- `single` - Single file
- `daily` - Daily rotating files

---

## 🚀 Production Environment

For production, update these critical settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

DB_HOST=your_production_db_host
DB_DATABASE=news_feed_prod
DB_PASSWORD=strong_secure_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_FROM_ADDRESS=noreply@yourdomain.com

FRONTEND_URL=https://yourdomain.com
```

---

## ✅ Validation Checklist

After creating your `.env` file, verify:

- [ ] Database credentials are correct
- [ ] Database exists (run `CREATE DATABASE news_feed;`)
- [ ] All 3 news API keys are added
- [ ] Frontend URL matches your React app URL
- [ ] `APP_KEY` is generated (run `php artisan key:generate`)

---

## 🆘 Troubleshooting

### Can't connect to database
1. Check MySQL is running: `sudo systemctl status mysql`
2. Verify credentials in `.env`
3. Create database: `CREATE DATABASE news_feed;`

### News fetching fails
1. Verify API keys are correct
2. Check internet connection
3. View logs: `storage/logs/laravel.log`

### CORS errors
1. Add your frontend URL to `.env`: `FRONTEND_URL=http://localhost:3000`
2. Check `config/cors.php`

---

## 📞 Need Help?

1. Check **SETUP_INSTRUCTIONS.md** for detailed troubleshooting
2. Review logs: `storage/logs/laravel.log`
3. Run diagnostics: `php artisan about`
4. Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

---

**Next Step**: Open **SETUP_INSTRUCTIONS.md** for complete setup guide!

