# News Aggregator Backend API

A Laravel 11 backend API for a news aggregator application that fetches articles from 4 major news sources (NewsAPI, The Guardian, New York Times, BBC News) and provides personalized news feeds to users.

## Features

- **Multi-Source News Aggregation**: Fetches news from 4 major sources
- **User Authentication**: Register, login, password reset with Laravel Sanctum
- **Personalized Feeds**: Users can customize news by sources, categories, and authors
- **Advanced Search & Filtering**: Search by keyword, filter by date, source, category, author
- **Redis Caching**: Performance optimization with intelligent caching
- **Repository Pattern**: Clean architecture with repositories and services
- **Scheduled Tasks**: Automated hourly news fetching
- **Comprehensive Tests**: Feature tests covering all endpoints
- **Fully Dockerized**: Complete docker-compose setup
- **API Documentation**: Postman collection and comprehensive API docs

## Tech Stack

- **Framework**: Laravel 11
- **PHP**: 8.3+
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Queue**: Redis
- **Authentication**: Laravel Sanctum
- **Testing**: Pest PHP
- **Containerization**: Docker & Docker Compose

## Quick Start with Docker

### Prerequisites
- Docker Desktop installed and running

### Step 1: Configure Environment

Create a `.env` file in the project root:

```env
APP_NAME="News Aggregator API"
APP_ENV=local
APP_DEBUG=true

# Docker Database Settings
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=news_user
DB_PASSWORD=secret

# Redis (in Docker)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis

# News API Keys
NEWS_API_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_key_here
NYTIMES_API_KEY=your_nytimes_key_here

# Frontend URL
FRONTEND_URL=http://localhost:3000
```

### Step 2: Start Docker Containers

```bash
# Start all services
docker-compose up -d

# Wait for containers to be ready (~30 seconds)
docker-compose ps
```

### Step 3: Setup Application

```bash
# Install PHP dependencies
docker-compose exec app composer install

# Generate application encryption key
docker-compose exec app php artisan key:generate

# Run database migrations and seed initial data
docker-compose exec app php artisan migrate:fresh --seed

# Fetch news articles from all sources
docker-compose exec app php artisan news:fetch

# Run tests to verify everything works
docker-compose exec app php artisan test
```

### Step 4: Verify

- **API Running**: http://localhost:8000
- **Test Endpoint**: http://localhost:8000/api/articles
- **Health Check**: http://localhost:8000/up

## Getting API Keys

You need API keys from these services (all have free tiers):

### 1. NewsAPI (Free - Instant)
- Visit: https://newsapi.org/
- Sign up for free account
- Get instant API key
- Add to `.env`: `NEWS_API_KEY=your_key`
- **Used for**: NewsAPI and BBC News sources

### 2. The Guardian (Free - Instant)
- Visit: https://open-platform.theguardian.com/access/
- Register for developer key
- Instant approval
- Add to `.env`: `GUARDIAN_API_KEY=your_key`

### 3. New York Times (Free Tier - Instant)
- Visit: https://developer.nytimes.com/get-started
- Create account
- Register app and enable "Article Search API"
- Get API key
- Add to `.env`: `NYTIMES_API_KEY=your_key`

**Total Time**: ~5-10 minutes to get all 3 keys  
**Cost**: $0 (all free tiers)  
**Coverage**: Access to 4 news sources with 3 API keys

## API Endpoints

### Authentication
```
POST   /api/auth/register          Register new user
POST   /api/auth/login             Login user
POST   /api/auth/logout            Logout user (protected)
GET    /api/auth/user              Get current user (protected)
POST   /api/auth/forgot-password   Request password reset
POST   /api/auth/reset-password    Reset password
```

### Articles
```
GET    /api/articles               List articles with filters
GET    /api/articles/{id}          Get single article
GET    /api/articles/feed/personalized   Personalized feed (protected)
```

**Query Parameters for /api/articles:**
- `keyword` - Search in title/description/content
- `from` - Start date (Y-m-d)
- `to` - End date (Y-m-d)
- `source_ids[]` - Filter by source IDs
- `category_ids[]` - Filter by category IDs
- `author_ids[]` - Filter by author IDs
- `sort_by` - Field to sort (published_at, created_at, title)
- `sort_order` - asc or desc
- `per_page` - Results per page (1-100, default 15)

### Categories, Sources, Authors
```
GET    /api/categories             List all categories
GET    /api/categories/{id}        Get single category
GET    /api/sources                List all sources
GET    /api/sources/{id}           Get single source
GET    /api/authors                List all authors
GET    /api/authors/{id}           Get single author
```

### User Preferences (Protected)
```
GET    /api/preferences            Get user preferences
PUT    /api/preferences            Update all preferences
PUT    /api/preferences/sources    Update preferred sources
PUT    /api/preferences/categories Update preferred categories
PUT    /api/preferences/authors    Update preferred authors
```

## Example Usage

### Register a User
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "Password123"
  }'
```

### Get Articles with Filters
```bash
curl "http://localhost:8000/api/articles?keyword=technology&category_ids[]=1&per_page=10"
```

### Get Personalized Feed
```bash
curl http://localhost:8000/api/articles/feed/personalized \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Update User Preferences
```bash
curl -X PUT http://localhost:8000/api/preferences \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "preferred_sources": [1, 2],
    "preferred_categories": [1, 2, 3],
    "preferred_authors": [1]
  }'
```

## Scheduled Tasks

The news fetcher runs automatically every hour. To enable scheduled tasks:

### Development
```bash
php artisan schedule:work
```

### Production
Add to your crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Manual News Fetching

```bash
php artisan news:fetch
```

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthenticationTest.php

# Run with coverage
php artisan test --coverage
```

## Project Structure

```
app/
├── Console/Commands/          # Artisan commands
├── Contracts/                 # Interfaces
├── Http/
│   ├── Controllers/Api/       # API Controllers
│   ├── Requests/              # Form Request Validators
│   └── Resources/             # API Resources
├── Models/                    # Eloquent Models
├── Repositories/              # Repository Pattern
├── Services/                  # Business Logic
│   └── NewsApi/              # News API Clients
└── Providers/                 # Service Providers

database/
├── factories/                 # Model Factories
├── migrations/                # Database Migrations
└── seeders/                   # Database Seeders

tests/
└── Feature/                   # Feature Tests

docker/                        # Docker Configuration
```

## Architecture

This project follows SOLID principles and implements:

- **Repository Pattern**: Data access abstraction
- **Service Layer**: Business logic separation
- **Factory Pattern**: News API client creation
- **Dependency Injection**: Throughout the application
- **Resource Pattern**: API response transformation

## Performance Optimizations

- **Caching**: Articles, feeds, categories cached with Redis
- **Eager Loading**: Relationships loaded efficiently
- **Database Indexes**: Strategic indexing for common queries
- **Full-Text Search**: MySQL full-text index on articles
- **Pagination**: All list endpoints paginated

## Security Features

- **Authentication**: Laravel Sanctum token-based auth
- **Validation**: Form Request validation on all inputs
- **Sanitization**: XSS protection via Laravel escaping
- **CORS**: Configured for cross-origin requests
- **Rate Limiting**: API rate limiting enabled
- **Password Hashing**: Bcrypt hashing

## Troubleshooting

### News Fetching Fails
- Verify API keys in `.env`
- Check internet connection
- View logs: `storage/logs/laravel.log`

### Database Connection Error
- Verify MySQL is running
- Check credentials in `.env`
- Create database: `CREATE DATABASE news_feed;`

### CORS Errors
- Check `config/cors.php`
- Add your frontend URL to `allowed_origins`

### Authentication Not Working
- Ensure Sanctum middleware is applied
- Check token format: `Authorization: Bearer {token}`

## Docker Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f app

# Run artisan commands
docker-compose exec app php artisan [command]

# Access MySQL
docker-compose exec mysql mysql -u news_user -psecret news_feed

# Rebuild containers
docker-compose up -d --build
```

## Environment Variables

Key environment variables in `.env`:

```env
DB_CONNECTION=mysql
DB_DATABASE=news_feed
DB_USERNAME=news_user
DB_PASSWORD=secret

NEWS_API_KEY=your_key
GUARDIAN_API_KEY=your_key
NYTIMES_API_KEY=your_key

CACHE_STORE=redis
QUEUE_CONNECTION=redis

FRONTEND_URL=http://localhost:3000
```

## Documentation

- **API Documentation**: See `API_DOCUMENTATION.md`
- **Docker Guide**: See `DOCKER_GUIDE.md`
- **Deployment Guide**: See `DEPLOYMENT_GUIDE.md`
- **Quick Start**: See `QUICKSTART.md`
- **Environment Setup**: See `ENV_TEMPLATE.md`

## License

This project is open-sourced software licensed under the MIT license.

## Support

For issues and questions:
- Check the documentation
- Review test files for examples
- Check logs: `storage/logs/laravel.log`
- Review Laravel documentation: https://laravel.com/docs

**APIs Used:**
- NewsAPI.org
- The Guardian Open Platform
- New York Times Developer API
