# Todo Tracker SaaS - Installation Guide

## Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL 8.0+ (running on port 8889)
- Redis (optional, for caching and sessions)
- Node.js and npm (for frontend assets)

## Database Configuration

Your MySQL database should be configured with:
- **Host:** 127.0.0.1
- **Port:** 8889
- **Database:** todo_tracker_saas
- **Username:** vibe_templates
- **Password:** vibe_templates_password

## Installation Steps

### 1. Install PHP Dependencies

```bash
composer install
```

### 2. Create Database

Run the SQL schema file to create all necessary tables:

```bash
mysql -h 127.0.0.1 -P 8889 -u vibe_templates -p todo_tracker_saas < database-schema.sql
```

Or create the database manually and run the schema:

```sql
mysql -h 127.0.0.1 -P 8889 -u vibe_templates -pvibe_templates_password
CREATE DATABASE IF NOT EXISTS todo_tracker_saas;
USE todo_tracker_saas;
SOURCE database-schema.sql;
```

### 3. Configure Environment

Create a `.env` file in the root directory with the following configuration:

```env
APP_NAME="Todo Tracker SaaS"
APP_ENV=local
APP_KEY=base64:GENERATE_THIS_WITH_php_artisan_key:generate
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=todo_tracker_saas
DB_USERNAME=vibe_templates
DB_PASSWORD=vibe_templates_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Migrations (if using Laravel migrations instead of SQL file)

```bash
php artisan migrate
```

### 6. Seed Database (Optional)

```bash
php artisan db:seed
```

### 7. Create Storage Link

```bash
php artisan storage:link
```

### 8. Start Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication Endpoints

#### Register
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "timezone": "UTC"
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

Response includes authentication token:
```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "1|abc123..."
  }
}
```

### Using the API

Include the authentication token in all subsequent requests:

```http
Authorization: Bearer YOUR_TOKEN_HERE
```

### Todo Endpoints

- `GET /api/todos` - List all todos
- `POST /api/todos` - Create new todo
- `GET /api/todos/{id}` - Get specific todo
- `PUT /api/todos/{id}` - Update todo
- `DELETE /api/todos/{id}` - Delete todo
- `POST /api/todos/{id}/complete` - Mark as completed
- `POST /api/todos/bulk` - Bulk operations

### List Endpoints

- `GET /api/lists` - List all todo lists
- `POST /api/lists` - Create new list
- `GET /api/lists/{id}` - Get specific list
- `PUT /api/lists/{id}` - Update list
- `DELETE /api/lists/{id}` - Delete list

### Team Endpoints

- `GET /api/teams` - List all teams
- `POST /api/teams` - Create new team
- `GET /api/teams/{id}` - Get specific team
- `POST /api/teams/{id}/members` - Add team member
- `DELETE /api/teams/{id}/members/{userId}` - Remove member

### Subscription Endpoints

- `GET /api/subscription/plans` - Get available plans
- `GET /api/subscription/current` - Get current subscription
- `POST /api/subscription/subscribe` - Subscribe to a plan
- `POST /api/subscription/cancel` - Cancel subscription

## Testing

Run the test suite:

```bash
php artisan test
```

## Troubleshooting

### Database Connection Issues

If you can't connect to the database:

1. Verify MySQL is running on port 8889
2. Check credentials in `.env` file
3. Ensure the database exists: `CREATE DATABASE todo_tracker_saas;`

### Permission Issues

If you encounter permission errors:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Redis Connection Issues

If Redis is not available, you can use file-based caching:

```env
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

## Production Deployment

For production deployment:

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Configure proper mail settings
3. Set up SSL certificates
4. Configure queue workers
5. Set up scheduled tasks (cron)
6. Enable Redis for better performance
7. Configure proper backup strategies

## Support

For issues or questions, please refer to the documentation or create an issue in the repository.
