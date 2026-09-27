# Rightmo Product Backend API

REST API for a product catalogue: user authentication, categories, products with images, and product ratings.
Built with Laravel using a Controller → Service → Repository structure, Sanctum token authentication, Redis caching and OpenAPI (Swagger) documentation.

## Tech Stack

| Area | Technology |
|---|---|
| Language | PHP 8.4 |
| Framework | Laravel 13 |
| Authentication | Laravel Sanctum 4 (Bearer tokens) |
| Database | MySQL 8.4 (Docker), SQLite in-memory for tests |
| Cache | Redis 7 (via `predis/predis`) |
| Web server | Nginx 1.27 + PHP-FPM |
| API documentation | OpenAPI 3 / Swagger UI (`darkaonline/l5-swagger`) |
| Testing | PHPUnit 12 (feature tests) |
| Code style | Laravel Pint |
| Containers | Docker + Docker Compose |

## Features

- Register, login and logout with Sanctum Bearer tokens
- Product CRUD with search by name, category and price filters, pagination, and sorting by price, rating, name or date
- Product list returns the essentials (name, price, category name, first image, average rating); get by id returns full details with all images and the rating count
- Upload, replace and delete product images
- Rate products from 1 to 5 with an optional comment; users can update or delete only their own ratings
- Category list
- Product and category responses cached in Redis and refreshed automatically when data changes
- Consistent JSON responses, including `401` for missing or invalid tokens

## Run with Docker

**Requirements:** Docker and Docker Compose. PHP and MySQL do not need to be installed on your machine.

1. Clone the repository and go into the project folder:

   ```bash
   git clone git@github.com:Sandalanka/Rightmo-Product-Backend-API.git
   cd Rightmo-Product-Backend-API
   ```

2. Create the environment file:

   ```bash
   cp .env.example .env
   ```

3. Build and start the containers:

   ```bash
   docker compose up -d --build
   ```

   On first start the `app` container installs Composer packages, generates `APP_KEY`, links `public/storage`, runs the migrations and generates the API documentation. This can take a minute; follow it with `docker compose logs -f app`.

4. Load sample data (demo user, categories, products, images and ratings):

   ```bash
   docker compose exec app php artisan db:seed
   ```

5. Open the API documentation at **http://localhost:8089/api/documentation**

### Services and ports

| Service | Image | URL / host port |
|---|---|---|
| API (Nginx) | `nginx:1.27-alpine` | http://localhost:8089/api/v1 |
| App (PHP-FPM) | `php:8.4-fpm-alpine` (built from `Dockerfile`) | internal only |
| MySQL | `mysql:8.4` | `127.0.0.1:3312` |
| Redis | `redis:7-alpine` | `127.0.0.1:6392` |

Ports can be changed in `.env` with `APP_PORT`, `FORWARD_DB_PORT` and `FORWARD_REDIS_PORT`.
The MySQL database, user and password default to `rightmo_product` / `rightmo` / `secret` and can be changed with `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD`.
Set `RUN_MIGRATIONS=false` to skip migrations when the container starts.

### Useful commands

```bash
docker compose exec app php artisan test                  # run the test suite
docker compose exec app php artisan migrate:fresh --seed  # reset the database with sample data
docker compose exec app php artisan l5-swagger:generate   # regenerate the API docs after changing them
docker compose exec app php artisan cache:clear           # clear the Redis cache
docker compose logs -f app                                # follow the app logs
docker compose down                                       # stop the containers
docker compose down -v                                    # stop and delete MySQL and Redis data
```

## API Documentation

Swagger UI: **http://localhost:8089/api/documentation**
Raw OpenAPI JSON: **http://localhost:8089/docs**

To try the protected endpoints in Swagger UI:

1. Call **Auth → POST /auth/login** with the demo account (created by the seeder):

   ```json
   { "email": "test@example.com", "password": "Password@123" }
   ```

2. Copy `data.token` from the response.
3. Click **Authorize** and paste the token only (without the word `Bearer`).

All endpoints except register and login need the header `Authorization: Bearer <token>`.

## Endpoints

Base URL: `http://localhost:8089/api/v1`

| Method | Endpoint | Description |
|---|---|---|
| POST | `/auth/register` | Register a new user |
| POST | `/auth/login` | Login and get a token |
| POST | `/auth/logout` | Revoke the current token |
| GET | `/categories` | List categories |
| GET | `/products` | List products (`search`, `category_id`, `min_price`, `max_price`, `sort_by`, `sort_order`, `per_page`, `page`) |
| GET | `/products/{productId}` | Get product details |
| POST | `/products` | Create a product (optionally with images) |
| PUT | `/products/{productId}` | Update a product |
| DELETE | `/products/{productId}` | Delete a product and its images |
| POST | `/products/{productId}/images` | Add images to a product |
| PUT | `/products/{productId}/images/{imageId}` | Replace an image (send as `POST` with `_method=PUT` for file uploads) |
| DELETE | `/products/{productId}/images/{imageId}` | Delete an image |
| GET | `/products/{productId}/ratings` | List ratings of a product |
| POST | `/products/{productId}/ratings` | Rate a product |
| PUT | `/products/{productId}/ratings/{ratingId}` | Update your own rating |
| DELETE | `/products/{productId}/ratings/{ratingId}` | Delete your own rating |

`sort_by` accepts `price`, `rating`, `name` or `created_at` (default), and `sort_order` accepts `asc` or `desc` (default).

## Project Structure

```
app/
├── Contracts/        Repository interfaces
├── Repositories/     Database access (bound to the interfaces in AppServiceProvider)
├── Services/         Business logic and caching
├── Http/
│   ├── Controllers/  Thin controllers returning JSON responses
│   ├── Requests/     Validation
│   └── Resources/    API response shapes
├── Models/
└── Swagger/          OpenAPI documentation (schemas and endpoint definitions)
docker/               PHP config, container entrypoint and Nginx config
tests/Feature/        Feature tests for every endpoint
```

## Running Tests

Tests use an in-memory SQLite database and an array cache, so they do not need MySQL or Redis:

```bash
docker compose exec app php artisan test
```
