# 🔑 Authentication Guide - Using Tokens

## ✅ Token Authentication

Your API uses **Laravel Sanctum** for token-based authentication. Both **register** and **login** endpoints return tokens that you can use for authenticated requests.

---

## 📋 Authentication Flow

### 1. **Register** (Get Token)
```http
POST http://localhost:8000/api/auth/register
Content-Type: application/json

{
    "name": "test",
    "email": "test@test.com",
    "password": "password123",
    "password_confirmation": "password123",
    "device_name": "web"  // Optional
}
```

**Response:**
```json
{
    "message": "User registered successfully.",
    "user": {
        "id": 20,
        "name": "test",
        "email": "test@test.com",
        "preference": {...}
    },
    "token": "13|m3N7lB7xQDkggc9yC5brnzCayBGSvaPJsna9Sjq03009bffe"
}
```

✅ **Token is returned!** Use this token for authenticated requests.

---

### 2. **Login** (Get Token)
```http
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
    "email": "test@test.com",
    "password": "password123",
    "device_name": "web"  // Optional
}
```

**Response:**
```json
{
    "message": "Login successful.",
    "user": {
        "id": 20,
        "name": "test",
        "email": "test@test.com",
        "preference": {...}
    },
    "token": "14|xyz123abc456..."
}
```

✅ **Token is returned!** Use this token for authenticated requests.

---

## 🔐 Using the Token

### In HTTP Headers

Add the token to the `Authorization` header:

```http
Authorization: Bearer 13|m3N7lB7xQDkggc9yC5brnzCayBGSvaPJsna9Sjq03009bffe
```

### Example: Get Authenticated User

```http
GET http://localhost:8000/api/auth/user
Authorization: Bearer 13|m3N7lB7xQDkggc9yC5brnzCayBGSvaPJsna9Sjq03009bffe
```

**Response:**
```json
{
    "user": {
        "id": 20,
        "name": "test",
        "email": "test@test.com",
        "preference": {...}
    }
}
```

---

## 📱 Frontend Integration

### React/JavaScript Example

```javascript
// 1. Register or Login
const response = await fetch('http://localhost:8000/api/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        email: 'test@test.com',
        password: 'password123'
    })
});

const data = await response.json();
const token = data.token; // Save this token!

// 2. Use token for authenticated requests
const articlesResponse = await fetch('http://localhost:8000/api/articles/feed/personalized', {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
    }
});

const articles = await articlesResponse.json();
```

### Store Token (LocalStorage Example)

```javascript
// After login/register
localStorage.setItem('auth_token', data.token);

// Use in subsequent requests
const token = localStorage.getItem('auth_token');
fetch('http://localhost:8000/api/auth/user', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});
```

---

## 🔒 Protected Endpoints

These endpoints require authentication (token in header):

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/auth/user` | GET | Get current user |
| `/api/auth/logout` | POST | Logout (revoke token) |
| `/api/articles/feed/personalized` | GET | Get personalized feed |
| `/api/user-preferences` | GET | Get user preferences |
| `/api/user-preferences` | PUT | Update user preferences |

---

## 🧪 Testing with cURL

### Register
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "test",
    "email": "test@test.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@test.com",
    "password": "password123"
  }'
```

### Use Token (Get User)
```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Authorization: Bearer 13|m3N7lB7xQDkggc9yC5brnzCayBGSvaPJsna9Sjq03009bffe"
```

### Use Token (Get Personalized Feed)
```bash
curl -X GET "http://localhost:8000/api/articles/feed/personalized" \
  -H "Authorization: Bearer 13|m3N7lB7xQDkggc9yC5brnzCayBGSvaPJsna9Sjq03009bffe"
```

---

## 📝 Token Format

Tokens follow this format:
```
{token_id}|{random_string}
```

Example:
```
13|m3N7lB7xQDkggc9yC5brnzCayBGSvaPJsna9Sjq03009bffe
```

**Important**: Include the **entire token** (including the `|` and everything after it) in the Authorization header.

---

## ⚠️ Common Issues

### 1. "Unauthenticated" Error

**Problem**: Token not sent or invalid

**Solution**:
- Check token is in `Authorization` header
- Verify token format: `Bearer {token}`
- Make sure token hasn't expired or been revoked

### 2. Token Not Working

**Problem**: Token might be revoked or expired

**Solution**:
- Login again to get a new token
- Check if you logged out (which revokes the token)

### 3. CORS Issues

**Problem**: Frontend can't send Authorization header

**Solution**: CORS is already configured in `config/cors.php` to allow:
- `Access-Control-Allow-Origin: http://localhost:3000`
- `Access-Control-Allow-Credentials: true`
- `Access-Control-Allow-Headers: Authorization`

---

## 🔄 Token Lifecycle

1. **Create**: Token created on register/login
2. **Use**: Include in `Authorization: Bearer {token}` header
3. **Revoke**: Token revoked on logout
4. **Expire**: Tokens don't expire by default (can be configured)

---

## 📚 API Endpoints Summary

### Public (No Token):
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/forgot-password` - Request password reset
- `POST /api/auth/reset-password` - Reset password
- `GET /api/articles` - List articles (public)
- `GET /api/sources` - List sources
- `GET /api/categories` - List categories
- `GET /api/authors` - List authors

### Protected (Requires Token):
- `GET /api/auth/user` - Get current user
- `POST /api/auth/logout` - Logout
- `GET /api/articles/feed/personalized` - Personalized feed
- `GET /api/user-preferences` - Get preferences
- `PUT /api/user-preferences` - Update preferences

---

## ✅ Quick Start

1. **Register or Login** to get a token
2. **Save the token** from the response
3. **Add token to headers** for protected endpoints:
   ```
   Authorization: Bearer {your-token-here}
   ```
4. **Make authenticated requests**!

---

## 🎯 Your Current Token

From your register response:
```
Token: 13|m3N7lB7xQDkggc9yC5brnzCayBGSvaPJsna9Sjq03009bffe
```

**Use it like this:**
```http
Authorization: Bearer 13|m3N7lB7xQDkggc9yC5brnzCayBGSvaPJsna9Sjq03009bffe
```

---

**Your authentication is working perfectly!** ✅

**Both register and login return tokens that you can use immediately!** 🎉

