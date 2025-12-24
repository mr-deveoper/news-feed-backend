# News Aggregator Backend - Implementation Guide

## Project Overview
A comprehensive Laravel backend for a news aggregator application with:
- Multi-source news fetching (NewsAPI, The Guardian, NY Times)
- User authentication with Sanctum
- Personalized news feeds
- Advanced search and filtering
- Repository pattern architecture
- Caching for performance
- Scheduled news updates

## Architecture

### Design Patterns Implemented
- **Repository Pattern**: Data access layer abstraction
- **Service Layer**: Business logic separation
- **Factory Pattern**: News API client creation
- **Dependency Injection**: Throughout the application
- **Resource Pattern**: API response transformation

### Key Features
1. **Authentication**: Register, Login, Password Reset (Sanctum)
2. **Article Management**: CRUD, Search, Filter, Pagination
3. **User Preferences**: Customize news feed by sources, categories, authors
4. **News Aggregation**: Scheduled fetching from multiple APIs
5. **Caching**: Redis-ready caching for performance
6. **Security**: Request validation, sanitization, rate limiting

## Directory Structure
```
app/
├── Console/Commands/          # Artisan commands
│   └── FetchNewsArticles.php  # News fetching command
├── Contracts/                 # Interfaces
│   ├── RepositoryInterface.php
│   ├── ArticleRepositoryInterface.php
│   └── NewsApiClientInterface.php
├── Http/
│   ├── Controllers/Api/       # API Controllers
│   ├── Requests/              # Form Request Validators
│   └── Resources/             # API Resources
├── Models/                    # Eloquent Models
├── Repositories/              # Repository implementations
├── Services/                  # Business logic services
│   └── NewsApi/              # News API clients
└── ...

database/
├── factories/                 # Model factories
├── migrations/                # Database migrations
└── seeders/                   # Database seeders

routes/
├── api.php                    # API routes
└── console.php                # Console routes & scheduling
```

## Database Schema

### Tables
- `users` - User accounts
- `personal_access_tokens` - API tokens (Sanctum)
- `password_reset_tokens` - Password resets
- `sources` - News sources (NewsAPI, Guardian, NYTimes)
- `categories` - Article categories
- `authors` - Article authors
- `articles` - News articles
- `article_category` - Pivot table
- `user_preferences` - User feed preferences

### Indexes
- Full-text index on articles (title, description, content)
- Composite indexes for common queries
- Foreign key indexes for relationships

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout (protected)
- `GET /api/auth/user` - Get current user (protected)
- `POST /api/auth/forgot-password` - Request password reset
- `POST /api/auth/reset-password` - Reset password

### Articles
- `GET /api/articles` - List articles (with filters)
  - Query params: `keyword`, `from`, `to`, `source_ids[]`, `category_ids[]`, `author_ids[]`, `per_page`
- `GET /api/articles/{id}` - Get single article
- `GET /api/articles/feed/personalized` - Personalized feed (protected)

### Categories
- `GET /api/categories` - List all categories
- `GET /api/categories/{id}` - Get single category

### Sources
- `GET /api/sources` - List all sources
- `GET /api/sources/{id}` - Get single source

### Authors
- `GET /api/authors` - List all authors
- `GET /api/authors/{id}` - Get single author

### User Preferences (Protected)
- `GET /api/preferences` - Get user preferences
- `PUT /api/preferences` - Update all preferences
- `PUT /api/preferences/sources` - Update preferred sources
- `PUT /api/preferences/categories` - Update preferred categories
- `PUT /api/preferences/authors` - Update preferred authors

## Environment Variables

Required in `.env`:
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=root
DB_PASSWORD=

# News API Keys
NEWS_API_KEY=your_newsapi_key_here
GUARDIAN_API_KEY=your_guardian_key_here
NYTIMES_API_KEY=your_nytimes_key_here

# Cache (optional, defaults to file)
CACHE_STORE=redis

# Queue (optional)
QUEUE_CONNECTION=redis
```

## Running the Application

### Initial Setup
```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Fetch initial articles
php artisan news:fetch
```

### Development
```bash
# Start Laravel server
php artisan serve

# Run queue worker (if using queues)
php artisan queue:work

# Run scheduler (for automated news fetching)
php artisan schedule:work
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ArticleTest
```

## Scheduled Tasks

The news fetching command runs automatically every hour:
```php
Schedule::command('news:fetch')->hourly();
```

To run the scheduler:
```bash
# Development
php artisan schedule:work

# Production (add to cron)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Caching Strategy

- **User personalized feeds**: Cached for 1 hour
- **Article listings by source/category/author**: Cached for 1 hour
- **Active sources & categories**: Cached indefinitely, cleared on updates

Cache is automatically cleared when:
- User preferences are updated
- Articles are created/updated
- Sources/categories are modified

## Performance Optimizations

1. **Eager Loading**: Relationships loaded efficiently
2. **Database Indexes**: Strategic indexing for common queries
3. **Caching**: Redis caching for frequent queries
4. **Pagination**: All list endpoints paginated
5. **Full-Text Search**: MySQL full-text index for article search

## Security Features

1. **Authentication**: Laravel Sanctum token-based auth
2. **Validation**: Form Request validation on all inputs
3. **Sanitization**: XSS protection via Laravel's default escaping
4. **Rate Limiting**: API rate limiting enabled
5. **CORS**: Configured for cross-origin requests
6. **SQL Injection**: Protected by Eloquent ORM
7. **Password Hashing**: Bcrypt hashing

## Code Quality & Best Practices

Following SOLID principles:
- **Single Responsibility**: Each class has one purpose
- **Open/Closed**: Open for extension, closed for modification
- **Liskov Substitution**: Interfaces properly implemented
- **Interface Segregation**: Clean, focused interfaces
- **Dependency Inversion**: Depend on abstractions

Following DRY & KISS:
- **Don't Repeat Yourself**: Reusable repository & service layers
- **Keep It Simple**: Straightforward, readable code

PSR-4 Autoloading:
- Proper namespace structure
- PSR-4 compliant autoloading

## Testing

Test files created for:
- Authentication flows
- Article CRUD operations
- Search & filtering
- User preferences
- News aggregation

Run tests with:
```bash
php artisan test
```

## API Documentation

Complete API documentation is provided in:
1. Postman collection (importable JSON)
2. OpenAPI/Swagger specification
3. This implementation guide

## Docker Support

The application is fully Dockerized with:
- PHP 8.3 container
- MySQL 8 container
- Redis container
- Nginx container

See `docker-compose.yml` for configuration.

## Frontend Integration

For React frontend integration:
1. Use the provided Postman collection for API reference
2. Base URL: `http://localhost:8000/api`
3. Include Bearer token in Authorization header for protected routes
4. All responses follow JSON:API format

## Troubleshooting

### Common Issues

**News fetching fails:**
- Check API keys in `.env`
- Verify internet connection
- Check logs: `storage/logs/laravel.log`

**Authentication not working:**
- Verify Sanctum is installed: `composer require laravel/sanctum`
- Check middleware in `api.php` routes
- Ensure token is in `Authorization: Bearer {token}` header

**Database errors:**
- Run migrations: `php artisan migrate:fresh --seed`
- Check database connection in `.env`

## Next Steps

1. Configure `.env` with your API keys
2. Run migrations and seeders
3. Fetch initial news articles
4. Import Postman collection
5. Start developing your React frontend

## Support

For issues or questions, refer to:
- Laravel Documentation: https://laravel.com/docs
- Project README.md
- API documentation in Postman collection

