-- Create test database for running tests
CREATE DATABASE IF NOT EXISTS news_feed_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON news_feed_test.* TO 'news_user'@'%';
FLUSH PRIVILEGES;

