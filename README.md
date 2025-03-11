# Weather Notifications API

A Laravel-based REST API service that provides weather information and manages weather notification subscriptions. The service allows users to subscribe to weather updates for specific locations and receive email notifications at their preferred times.

## Features

- Real-time weather data retrieval for any location
- Email notification subscription management
- Scheduled weather notifications
- RESTful API endpoints
- Docker containerization
- Comprehensive test coverage

## Tech Stack

- **Framework**: Laravel 12.x
- **PHP Version**: 8.2+
- **Database**: MySQL
- **Containerization**: Docker
- **Testing**: PHPUnit
- **External Services**: Weather API Integration
- **Queue System**: Laravel Queue (for email notifications)

## Prerequisites

- Docker and Docker Compose
- Git
- Composer (for local development)
- PHP 8.2+ (for local development)

## Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd weather-notifications-app
   ```

2. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

3. Configure your `.env` file with:
   - Database credentials
   - Weather API key
   - Mail server settings
   - Queue configuration

4. Start the Docker containers:
   ```bash
   docker-compose up -d
   ```

5. Install dependencies:
   ```bash
   docker-compose exec app composer install
   ```

6. Generate application key:
   ```bash
   docker-compose exec app php artisan key:generate
   ```

7. Run database migrations:
   ```bash
   docker-compose exec app php artisan migrate
   ```

## API Endpoints

### Weather Endpoints

- `GET /api/weather/current` - Get current weather for a location
  - Required params: `location`
  - Optional params: `units` (m/f/s), `language` (2-letter code)

- `GET /api/weather/health` - Check weather service health

### Subscription Endpoints

- `GET /api/subscriptions` - List all active subscriptions
- `POST /api/subscriptions` - Create new subscription
  - Required params: `email`, `location`, `notification_time`
- `GET /api/subscriptions/{id}` - Get subscription details
- `PUT /api/subscriptions/{id}` - Update subscription
- `DELETE /api/subscriptions/{id}` - Delete subscription
- `POST /api/subscriptions/unsubscribe/{id}` - Deactivate subscription

## Testing

Run the test suite:

```bash
docker-compose exec app php artisan test
```

For specific test files:

```bash
docker-compose exec app php artisan test --filter=WeatherControllerTest
```

## Debugging

### Common Issues and Solutions

1. **Container Issues**
   ```bash
   # Check container status
   docker-compose ps
   
   # View container logs
   docker-compose logs -f app
   docker-compose logs -f nginx
   docker-compose logs -f mysql
   ```

2. **Database Issues**
   ```bash
   # Refresh database
   docker-compose exec app php artisan migrate:fresh
   
   # Check database connection
   docker-compose exec app php artisan db:show
   ```

3. **Queue Issues**
   ```bash
   # Monitor queues
   docker-compose exec app php artisan queue:monitor
   
   # Restart queue worker
   docker-compose exec app php artisan queue:restart
   ```

4. **Laravel Logs**
   - Check logs at `storage/logs/laravel.log`
   ```bash
   docker-compose exec app tail -f storage/logs/laravel.log
   ```

### Development Tools

1. **Laravel Pail** (for log monitoring):
   ```bash
   docker-compose exec app php artisan pail
   ```

2. **Laravel Tinker** (for REPL):
   ```bash
   docker-compose exec app php artisan tinker
   ```

## Maintenance

### Queue Worker

The application uses queues for sending email notifications. Ensure the queue worker is running:

```bash
docker-compose exec app php artisan queue:work
```

### Scheduled Tasks

To run the scheduler for notifications:

```bash
docker-compose exec app php artisan schedule:work
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the MIT license.
