# PowerShell script to create correct .env file for Docker

Write-Host "Creating .env file for Docker..." -ForegroundColor Yellow

$envContent = @"
APP_NAME="News Aggregator API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# DOCKER DATABASE - These values match docker-compose.yml
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=news_feed
DB_USERNAME=news_user
DB_PASSWORD=secret

# DOCKER REDIS
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# LOGGING
LOG_CHANNEL=stack
LOG_LEVEL=debug

# SESSION
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# MAIL
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME=`${APP_NAME}

# NEWS API KEYS - Add your keys here
# Get free keys from:
# https://newsapi.org/
# https://open-platform.theguardian.com/
# https://developer.nytimes.com/
NEWS_API_KEY=
GUARDIAN_API_KEY=
NYTIMES_API_KEY=

# FRONTEND
FRONTEND_URL=http://localhost:3000
"@

# Write to .env file
$envContent | Out-File -FilePath ".env" -Encoding utf8 -Force

Write-Host "✅ .env file created successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "IMPORTANT: Edit .env and add your API keys:" -ForegroundColor Cyan
Write-Host "  - NEWS_API_KEY=your_key" -ForegroundColor Yellow
Write-Host "  - GUARDIAN_API_KEY=your_key" -ForegroundColor Yellow
Write-Host "  - NYTIMES_API_KEY=your_key" -ForegroundColor Yellow
Write-Host ""
Write-Host "Get free keys from:" -ForegroundColor Cyan
Write-Host "  - https://newsapi.org/" -ForegroundColor White
Write-Host "  - https://open-platform.theguardian.com/" -ForegroundColor White
Write-Host "  - https://developer.nytimes.com/" -ForegroundColor White
Write-Host ""
Write-Host "After adding keys, run:" -ForegroundColor Cyan
Write-Host "  docker-compose down" -ForegroundColor White
Write-Host "  docker-compose up -d" -ForegroundColor White
Write-Host "  docker-compose exec app php artisan key:generate" -ForegroundColor White
Write-Host "  docker-compose exec app php artisan migrate:fresh --seed" -ForegroundColor White
Write-Host "  docker-compose exec app php artisan test" -ForegroundColor White

