# News Aggregator Backend - Quick Start Guide

## Prerequisites

- PHP 8.3+
- Composer
- MySQL 8.0+
- (Optional) Docker & Docker Compose

## Installation

### Option 1: Local Setup

```bash
# 1. Clone the repository
cd news-feed-backend

# 2. Install dependencies
composer install

# 3. Create environment file
cp .env.example .env

# 4. Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=root
DB_PASSWORD=your_password

# 5. Add News API keys to .env
NEWS_API_KEY=your_newsapi_key
GUARDIAN_API_KEY=your_guardian_key
NYTIMES_API_KEY=your_nytimes_key

# 6. Generate application key
php artisan key:generate

# 7. Run migrations and seed database
php artisan migrate:fresh --seed

# 8. Fetch initial news articles
php artisan news:fetch

# 9. Start the development server
php artisan serve
```

Your API is now running at `http://localhost:8000`

### Option 2: Docker Setup

```bash
# 1. Create environment file
cp .env.example .env

# 2. Configure .env for Docker
DB_HOST=mysql
DB_DATABASE=news_feed
DB_USERNAME=sail
DB_PASSWORD=password

# 3. Start Docker containers
docker-compose up -d

# 4. Install dependencies
docker-compose exec app composer install

# 5. Generate key
docker-compose exec app php artisan key:generate

# 6. Run migrations
docker-compose exec app php artisan migrate:fresh --seed

# 7. Fetch news
docker-compose exec app php artisan news:fetch
```

Your API is now running at `http://localhost:8000`

## Getting API Keys

### NewsAPI (Free Tier Available)
1. Visit https://newsapi.org/
2. Sign up for a free account
3. Get your API key from the dashboard
4. Add to `.env`: `NEWS_API_KEY=your_key`

### The Guardian (Free)
1. Visit https://open-platform.theguardian.com/access/
2. Register for an API key
3. Add to `.env`: `GUARDIAN_API_KEY=your_key`

### New York Times (Free Tier Available)
1. Visit https://developer.nytimes.com/get-started
2. Create an account
3. Register an app and get Article Search API key
4. Add to `.env`: `NYTIMES_API_KEY=your_key`

## Testing the API

### 1. Register a User

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

Response:
```json
{
  "user": {...},
  "token": "1|abcdef123456..."
}
```

### 2. Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### 3. Get Articles

```bash
curl http://localhost:8000/api/articles?per_page=10
```

### 4. Search Articles

```bash
curl "http://localhost:8000/api/articles?keyword=technology&category_ids[]=1&per_page=10"
```

### 5. Get Personalized Feed (Requires Authentication)

```bash
curl http://localhost:8000/api/articles/feed/personalized \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 6. Update User Preferences (Requires Authentication)

```bash
curl -X PUT http://localhost:8000/api/preferences \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "preferred_sources": [1, 2],
    "preferred_categories": [1, 2, 3],
    "preferred_authors": []
  }'
```

## Running Scheduled Tasks

The news fetcher runs automatically every hour. To enable:

### Development

```bash
php artisan schedule:work
```

### Production

Add to crontab:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Or use a process manager like Supervisor to run `php artisan schedule:work`.

## Manual News Fetching

```bash
php artisan news:fetch
```

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run with coverage
php artisan test --coverage
```

## API Documentation

### Base URL
- Development: `http://localhost:8000/api`
- Production: `https://your-domain.com/api`

### Authentication
Most endpoints require authentication. Include the token in the Authorization header:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

### Pagination
All list endpoints support pagination:
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)

### Filtering Articles
Available filters for `/api/articles`:
- `keyword` - Search in title, description, content
- `from` - Start date (Y-m-d)
- `to` - End date (Y-m-d)
- `source_ids[]` - Array of source IDs
- `category_ids[]` - Array of category IDs
- `author_ids[]` - Array of author IDs
- `sort_by` - Field to sort by (default: published_at)
- `sort_order` - asc or desc (default: desc)
- `per_page` - Results per page

Example:
```
/api/articles?keyword=technology&from=2024-01-01&category_ids[]=1&category_ids[]=2&per_page=20
```

## Troubleshooting

### Database Connection Error
- Check MySQL is running
- Verify credentials in `.env`
- Ensure database exists: `CREATE DATABASE news_feed;`

### News Fetching Fails
- Verify API keys in `.env`
- Check internet connection
- View logs: `storage/logs/laravel.log`

### CORS Errors
- Check CORS configuration in `config/cors.php`
- Ensure frontend URL is in `allowed_origins`

### Token Not Working
- Ensure token is in format: `Bearer {token}`
- Check token hasn't expired
- Verify Sanctum middleware is applied to routes

## Development Tools

### Laravel Tinker
```bash
php artisan tinker
```

Test queries:
```php
// Get all articles
Article::count();

// Test news fetching
app(\App\Services\NewsAggregatorService::class)->aggregateNews();

// Clear cache
Cache::flush();
```

### Queue Worker (if using queues)
```bash
php artisan queue:work
```

### Code Formatting
```bash
vendor/bin/pint
```

## Environment Variables Reference

```env
# App
APP_NAME="News Aggregator"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=root
DB_PASSWORD=

# News APIs
NEWS_API_KEY=
GUARDIAN_API_KEY=
NYTIMES_API_KEY=

# Cache
CACHE_STORE=redis  # or file
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=sync  # or redis

# Mail (for password reset)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

## Production Deployment

1. Set `APP_ENV=production`
2. Set `APP_DEBUG=false`
3. Configure proper database credentials
4. Set up Redis for caching
5. Configure queue worker
6. Set up cron for scheduler
7. Configure mail server for password resets
8. Enable HTTPS
9. Set proper CORS origins
10. Run `php artisan config:cache`
11. Run `php artisan route:cache`
12. Run `php artisan view:cache`

## Next Steps

1. **Import Postman Collection** - Use the provided collection for easy API testing
2. **Build Frontend** - Connect your React app to this API
3. **Customize News Sources** - Add or remove news sources as needed
4. **Extend Features** - Add bookmarks, comments, sharing, etc.

## Support

For issues or questions:
1. Check the logs: `storage/logs/laravel.log`
2. Review the full documentation
3. Check Laravel documentation: https://laravel.com/docs

---

**Happy Coding! 🚀**

