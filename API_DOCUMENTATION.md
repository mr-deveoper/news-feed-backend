# News Aggregator API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication

All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {your-token-here}
```

## Response Format

All responses follow a consistent JSON format:

### Success Response
```json
{
    "data": {...},
    "message": "Success message"
}
```

### Error Response
```json
{
    "message": "Error message",
    "errors": {...}
}
```

### Paginated Response
```json
{
    "data": [...],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100
    }
}
```

---

## Endpoints

### 1. Authentication

#### 1.1 Register
**POST** `/api/auth/register`

Register a new user account.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
}
```

**Validation Rules:**
- `name`: required, string, min:2, max:255
- `email`: required, email, unique in users table
- `password`: required, min:8, must contain mixed case and numbers, must be confirmed

**Response (201):**
```json
{
    "message": "User registered successfully.",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2024-12-24T00:00:00.000000Z",
        "preference": {
            "id": 1,
            "preferred_sources": [],
            "preferred_categories": [],
            "preferred_authors": []
        }
    },
    "token": "1|abcdefgh..."
}
```

#### 1.2 Login
**POST** `/api/auth/login`

Login to get an authentication token.

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "Password123",
    "device_name": "web"
}
```

**Validation Rules:**
- `email`: required, email
- `password`: required
- `device_name`: optional, string

**Response (200):**
```json
{
    "message": "Login successful.",
    "user": {...},
    "token": "1|abcdefgh..."
}
```

#### 1.3 Get Current User
**GET** `/api/auth/user`  
🔒 **Protected**

Get the currently authenticated user.

**Response (200):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "preference": {...}
    }
}
```

#### 1.4 Logout
**POST** `/api/auth/logout`  
🔒 **Protected**

Logout and revoke the current token.

**Response (200):**
```json
{
    "message": "Logged out successfully."
}
```

#### 1.5 Forgot Password
**POST** `/api/auth/forgot-password`

Request a password reset link.

**Request Body:**
```json
{
    "email": "john@example.com"
}
```

**Response (200):**
```json
{
    "message": "Password reset link sent to your email."
}
```

#### 1.6 Reset Password
**POST** `/api/auth/reset-password`

Reset password using the token from email.

**Request Body:**
```json
{
    "token": "reset-token-from-email",
    "email": "john@example.com",
    "password": "NewPassword123",
    "password_confirmation": "NewPassword123"
}
```

**Response (200):**
```json
{
    "message": "Password reset successfully."
}
```

---

### 2. Articles

#### 2.1 List Articles
**GET** `/api/articles`

Get a paginated list of articles with optional filters. Supports filtering by keyword, date range, sources, categories, and authors.

**Query Parameters:**
- `keyword` (optional): Search in title, description, content
- `from` (optional): Start date in `Y-m-d` format (e.g., `2024-12-01`) - filters articles published on or after this date
- `to` (optional): End date in `Y-m-d` format (e.g., `2024-12-31`) - filters articles published on or before this date. Must be equal to or after `from` date
- `source_ids[]` (optional): Array of source IDs to filter by
- `category_ids[]` (optional): Array of category IDs to filter by
- `author_ids[]` (optional): Array of author IDs to filter by
- `sort_by` (optional): Field to sort by (`published_at`, `created_at`, `title`) - default: `published_at`
- `sort_order` (optional): `asc` or `desc` - default: `desc`
- `per_page` (optional): Results per page (1-100) - default: 15

**Examples:**

**Basic listing:**
```
GET /api/articles?per_page=20
```

**Search by keyword:**
```
GET /api/articles?keyword=technology&per_page=20
```

**Filter by date range:**
```
GET /api/articles?from=2024-12-01&to=2024-12-31&per_page=15
```

**Filter by date (from only):**
```
GET /api/articles?from=2024-12-01&per_page=15
```

**Filter by date (to only):**
```
GET /api/articles?to=2024-12-31&per_page=15
```

**Filter by sources and categories:**
```
GET /api/articles?source_ids[]=1&source_ids[]=2&category_ids[]=1&per_page=20
```

**Complete example with all filters:**
```
GET /api/articles?keyword=technology&from=2024-12-01&to=2024-12-31&source_ids[]=1&source_ids[]=2&category_ids[]=1&author_ids[]=1&sort_by=published_at&sort_order=desc&per_page=20
```

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "title": "Article Title",
            "slug": "article-title-abc123",
            "description": "Article description...",
            "content": "Full article content...",
            "url": "https://example.com/article",
            "image_url": "https://example.com/image.jpg",
            "published_at": "2024-12-24T00:00:00.000000Z",
            "source": {
                "id": 1,
                "name": "NewsAPI",
                "slug": "newsapi"
            },
            "author": {
                "id": 1,
                "name": "John Smith",
                "email": "john@example.com"
            },
            "categories": [
                {
                    "id": 1,
                    "name": "Technology",
                    "slug": "technology"
                }
            ]
        }
    ],
    "links": {...},
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100
    }
}
```

#### 2.2 Get Single Article
**GET** `/api/articles/{id}`

Get a specific article by ID.

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "title": "Article Title",
        "description": "...",
        "content": "...",
        "url": "...",
        "source": {...},
        "author": {...},
        "categories": [...]
    }
}
```

#### 2.3 Personalized Feed
**GET** `/api/articles/feed/personalized`  
🔒 **Protected**

Get a personalized news feed based on user preferences. The feed automatically filters articles by:
- **Preferred Sources**: Only articles from user's selected sources
- **Preferred Categories**: Only articles in user's selected categories
- **Preferred Authors**: Only articles by user's selected authors

Users can customize their preferences using the `/api/preferences` endpoints (see User Preferences section).

**Query Parameters:**
- `per_page` (optional): Results per page (1-100) - default: 15

**Example:**
```
GET /api/articles/feed/personalized?per_page=20
```

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "title": "Article Title",
            "description": "...",
            "source": {...},
            "author": {...},
            "categories": [...]
        }
    ],
    "links": {...},
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 50
    }
}
```

**Note:** If user has no preferences set, all articles are returned. Users can update preferences via:
- `PUT /api/preferences` - Update all preferences
- `PUT /api/preferences/sources` - Update preferred sources only
- `PUT /api/preferences/categories` - Update preferred categories only
- `PUT /api/preferences/authors` - Update preferred authors only

---

### 3. Categories

#### 3.1 List Categories
**GET** `/api/categories`

Get all active categories.

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Technology",
            "slug": "technology",
            "description": "Tech news and updates",
            "is_active": true
        }
    ]
}
```

#### 3.2 Get Single Category
**GET** `/api/categories/{id}`

Get a specific category.

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Technology",
        "slug": "technology",
        "description": "...",
        "is_active": true
    }
}
```

---

### 4. Sources

#### 4.1 List Sources
**GET** `/api/sources`

Get all active news sources.

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "NewsAPI",
            "slug": "newsapi",
            "api_identifier": "newsapi",
            "url": "https://newsapi.org",
            "description": "News from over 70,000 sources",
            "is_active": true
        }
    ]
}
```

#### 4.2 Get Single Source
**GET** `/api/sources/{id}`

Get a specific news source.

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "name": "NewsAPI",
        "slug": "newsapi",
        "description": "..."
    }
}
```

---

### 5. Authors

#### 5.1 List Authors
**GET** `/api/authors`

Get a paginated list of authors.

**Query Parameters:**
- `per_page` (optional): Results per page (1-100) - default: 15

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "John Smith",
            "email": "john@example.com",
            "bio": "...",
            "avatar_url": "..."
        }
    ],
    "links": {...},
    "meta": {...}
}
```

#### 5.2 Get Single Author
**GET** `/api/authors/{id}`

Get a specific author.

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "name": "John Smith",
        "email": "john@example.com"
    }
}
```

---

### 6. User Preferences

All preference endpoints are **protected** and require authentication.

#### 6.1 Get Preferences
**GET** `/api/preferences`  
🔒 **Protected**

Get the current user's news feed preferences.

**Response (200):**
```json
{
    "data": {
        "id": 1,
        "user_id": 1,
        "preferred_sources": [1, 2],
        "preferred_categories": [1, 2, 3],
        "preferred_authors": [1]
    }
}
```

#### 6.2 Update All Preferences
**PUT** `/api/preferences`  
🔒 **Protected**

Update all user preferences at once.

**Request Body:**
```json
{
    "preferred_sources": [1, 2],
    "preferred_categories": [1, 2, 3],
    "preferred_authors": [1]
}
```

**Validation Rules:**
- `preferred_sources`: optional, array, each must be a valid source ID
- `preferred_categories`: optional, array, each must be a valid category ID
- `preferred_authors`: optional, array, each must be a valid author ID

**Response (200):**
```json
{
    "message": "Preferences updated successfully.",
    "preference": {...}
}
```

#### 6.3 Update Preferred Sources
**PUT** `/api/preferences/sources`  
🔒 **Protected**

Update only preferred sources.

**Request Body:**
```json
{
    "source_ids": [1, 2]
}
```

**Response (200):**
```json
{
    "message": "Preferred sources updated successfully."
}
```

#### 6.4 Update Preferred Categories
**PUT** `/api/preferences/categories`  
🔒 **Protected**

Update only preferred categories.

**Request Body:**
```json
{
    "category_ids": [1, 2, 3]
}
```

**Response (200):**
```json
{
    "message": "Preferred categories updated successfully."
}
```

#### 6.5 Update Preferred Authors
**PUT** `/api/preferences/authors`  
🔒 **Protected**

Update only preferred authors.

**Request Body:**
```json
{
    "author_ids": [1, 2]
}
```

**Response (200):**
```json
{
    "message": "Preferred authors updated successfully."
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 405 | Method Not Allowed |
| 422 | Validation Error |
| 500 | Internal Server Error |

## Rate Limiting

API rate limiting is enabled:
- **Public endpoints**: 60 requests per minute
- **Protected endpoints**: 60 requests per minute per user

---

## Example Workflow

### 1. Register & Login
```bash
# Register
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
  }'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "Password123"
  }'

# Save the token from response
export TOKEN="your-token-here"
```

### 2. Browse Articles
```bash
# Get all articles
curl http://localhost:8000/api/articles

# Search for technology articles
curl "http://localhost:8000/api/articles?keyword=technology"

# Filter by source and category
curl "http://localhost:8000/api/articles?source_ids[]=1&category_ids[]=1&per_page=20"

# Get articles from last 7 days
curl "http://localhost:8000/api/articles?from=2024-12-17&to=2024-12-24"
```

### 3. Customize Preferences
```bash
# View current preferences
curl http://localhost:8000/api/preferences \
  -H "Authorization: Bearer $TOKEN"

# Get all sources
curl http://localhost:8000/api/sources

# Get all categories
curl http://localhost:8000/api/categories

# Update preferences
curl -X PUT http://localhost:8000/api/preferences \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "preferred_sources": [1, 2],
    "preferred_categories": [1, 2, 3],
    "preferred_authors": [1]
  }'
```

### 4. Get Personalized Feed
```bash
curl http://localhost:8000/api/articles/feed/personalized \
  -H "Authorization: Bearer $TOKEN"
```

---

## Database Models

### User
- id (bigint)
- name (string)
- email (string, unique)
- email_verified_at (timestamp, nullable)
- password (string, hashed)
- created_at, updated_at

### Source
- id (bigint)
- name (string, unique)
- slug (string, unique)
- api_identifier (string, unique, nullable)
- url (string, nullable)
- description (text, nullable)
- is_active (boolean)
- created_at, updated_at

### Category
- id (bigint)
- name (string, unique)
- slug (string, unique)
- description (text, nullable)
- is_active (boolean)
- created_at, updated_at

### Author
- id (bigint)
- name (string)
- email (string, nullable)
- bio (text, nullable)
- avatar_url (string, nullable)
- created_at, updated_at

### Article
- id (bigint)
- title (string)
- slug (string, unique)
- description (text, nullable)
- content (longtext, nullable)
- url (string, unique)
- image_url (string, nullable)
- source_id (foreign key)
- author_id (foreign key, nullable)
- published_at (timestamp, nullable)
- created_at, updated_at

### UserPreference
- id (bigint)
- user_id (foreign key, unique)
- preferred_sources (json)
- preferred_categories (json)
- preferred_authors (json)
- created_at, updated_at

---

## Advanced Features

### Full-Text Search
When using MySQL, the API utilizes full-text indexes for fast article searching. For SQLite (testing), it falls back to LIKE queries.

### Caching
- User personalized feeds cached for 1 hour
- Source/category listings cached
- Cache automatically cleared on preference updates

### Scheduled Tasks
News articles are automatically fetched every hour via:
```bash
php artisan news:fetch
```

---

## Frontend Integration Guide

### Setup Axios
```javascript
import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Add token to requests
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default api;
```

### Example API Calls
```javascript
// Register
const register = async (name, email, password) => {
    const response = await api.post('/auth/register', {
        name,
        email,
        password,
        password_confirmation: password
    });
    localStorage.setItem('token', response.data.token);
    return response.data;
};

// Login
const login = async (email, password) => {
    const response = await api.post('/auth/login', { email, password });
    localStorage.setItem('token', response.data.token);
    return response.data;
};

// Get articles
const getArticles = async (filters = {}) => {
    const response = await api.get('/articles', { params: filters });
    return response.data;
};

// Get personalized feed
const getPersonalizedFeed = async () => {
    const response = await api.get('/articles/feed/personalized');
    return response.data;
};

// Update preferences
const updatePreferences = async (preferences) => {
    const response = await api.put('/preferences', preferences);
    return response.data;
};
```

---

## Postman Collection

Import the `postman_collection.json` file into Postman for easy API testing.

### Setup
1. Import the collection
2. Set environment variable `base_url` to `http://localhost:8000`
3. Login to automatically set the `token` variable
4. All protected endpoints will use the token automatically

---

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Run tests: `php artisan test`
3. Refer to Laravel documentation: https://laravel.com/docs

---

**Last Updated**: December 24, 2025  
**API Version**: 1.0.0

