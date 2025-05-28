# URL Shortener

A modern URL shortening service built with Laravel and Docker.

## Features

- Shorten long URLs to easy-to-share short links
- Track analytics for each shortened URL
- RESTful API for integration with other services
- Simple and clean web interface
- Handle high traffic with low latency

## Environment Management

This project uses Docker for containerization and includes a handy `manage.sh` script to simplify common tasks.

### Requirements

- Docker and Docker Compose
- Bash shell

### Getting Started

1. Clone this repository
2. Run `./manage.sh start` to start the environment
3. Access the application at `http://localhost:8000`

### Commands

```bash
# Start the environment
./manage.sh start

# Stop the environment
./manage.sh stop

# Restart the environment
./manage.sh restart

# Run database migrations
./manage.sh migrate

# Clear all Laravel caches
./manage.sh clear-cache

# Run the test script
./manage.sh test
```

## API Documentation

The URL shortener provides a RESTful API for programmatic access to its features.

### API Endpoints

#### 1. Shorten URL

Shortens a long URL into a compact, easy-to-share link.

- **URL**: `/api/shorten`
- **Method**: `POST`
- **Headers**:
  - Content-Type: `application/json`
  - Accept: `application/json`
  - X-Requested-With: `XMLHttpRequest` (for CSRF protection bypass)
- **Request Body**:
  ```json
  {
    "url": "https://example.com/very/long/url/that/needs/shortening"
  }
  ```
- **Success Response**:
  - Status Code: `201`
  - Content:
    ```json
    {
      "success": true,
      "short_uri": "abc123",
      "short_url": "http://localhost:8000/s/abc123",
      "original_url": "https://example.com/very/long/url/that/needs/shortening"
    }
    ```
- **Error Response**:
  - Status Code: `400`
  - Content:
    ```json
    {
      "success": false,
      "error": "The url field is required."
    }
    ```

#### 2. Get URL Analytics

Retrieves analytics for a specific shortened URL.

- **URL**: `/api/analytics/{shortUri}`
- **Method**: `GET`
- **Headers**:
  - Accept: `application/json`
  - X-Requested-With: `XMLHttpRequest` (for CSRF protection bypass)
- **URL Parameters**:
  - `shortUri`: The short URI code (e.g., `abc123`)
- **Success Response**:
  - Status Code: `200`
  - Content:
    ```json
    {
      "success": true,
      "short_uri": "abc123",
      "short_url": "http://localhost:8000/s/abc123",
      "original_url": "https://example.com/very/long/url/that/needs/shortening",
      "created_at": "2023-05-01T12:34:56.000000Z",
      "view_count": 42,
      "latest_views": [
        {
          "time_visited": "2023-05-01T15:30:00.000000Z"
        },
        {
          "time_visited": "2023-05-01T14:20:00.000000Z"
        }
      ]
    }
    ```
- **Error Response**:
  - Status Code: `404`
  - Content:
    ```json
    {
      "success": false,
      "error": "Short URL not found"
    }
    ```

#### 3. Get All URLs Analytics

Retrieves analytics for all shortened URLs in the system.

- **URL**: `/api/analytics`
- **Method**: `GET`
- **Headers**:
  - Accept: `application/json`
  - X-Requested-With: `XMLHttpRequest` (for CSRF protection bypass)
- **Success Response**:
  - Status Code: `200`
  - Content:
    ```json
    {
      "success": true,
      "total_urls": 2,
      "urls": {
        "abc123": {
          "short_url": "http://localhost:8000/s/abc123",
          "original_url": "https://example.com/very/long/url/that/needs/shortening",
          "created_at": "2023-05-01T12:34:56.000000Z",
          "view_count": 42,
          "last_viewed": "2023-05-01T15:30:00.000000Z"
        },
        "def456": {
          "short_url": "http://localhost:8000/s/def456",
          "original_url": "https://example.com/another/long/url",
          "created_at": "2023-05-02T10:11:12.000000Z",
          "view_count": 7,
          "last_viewed": "2023-05-02T12:30:00.000000Z"
        }
      }
    }
    ```

#### 4. Access Shortened URL

Redirects a shortened URL to its original destination.

- **URL**: `/s/{shortUri}`
- **Method**: `GET`
- **URL Parameters**:
  - `shortUri`: The short URI code (e.g., `abc123`)
- **Success Response**:
  - Status Code: `302` (Redirect)
  - Headers:
    - Location: `[original URL]`
- **Error Response**:
  - Status Code: `404` (Not Found)

## Testing

The project includes a test script (`test.sh`) that verifies the functionality of the API endpoints. Run it with:

```bash
./manage.sh test
```

The test script performs the following checks:
1. Creates a short URL
2. Retrieves analytics for the short URL
3. Visits the short URL to ensure redirection works
4. Verifies that the view count increases after visiting

## Troubleshooting

If you encounter issues, try the following steps:

1. Clear the Laravel cache with `./manage.sh clear-cache`
2. Restart the environment with `./manage.sh restart`
3. Check the Docker logs for any errors

## License

MIT License

## High Traffic Handling

The URL shortener is designed to handle high traffic with low latency through:

1. **Redis Caching**: Frequently accessed URLs are cached in Redis to minimize database queries
2. **Asynchronous Analytics**: URL view tracking is processed in the background via queues
3. **Database Optimization**: Proper indexing for fast URL lookups
4. **Rate Limiting**: Prevents abuse while allowing legitimate high traffic
5. **Horizontal Scaling**: The containerized architecture allows easy horizontal scaling
6. **Environment-Aware Processing**: Automatic detection of testing environments for synchronous processing during tests, while maintaining asynchronous processing in production

When expecting high traffic, consider:
- Scaling the queue workers (increase the number of containers)
- Adjusting the Redis cache settings for higher memory allocation
- Implementing a CDN for global distribution
