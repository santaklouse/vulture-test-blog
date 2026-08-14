# Vulture Blog

A test assignment implementing a small blog with plain PHP, MySQL, Smarty, and SCSS without a PHP framework.

## Requirements

- Docker Desktop with Docker Compose;
- the TCP port configured through `WEB_PORT` must be available; it defaults to `8080`.

PHP, Composer, MySQL, Nginx do not need to be installed on the host machine.

## Development setup

Create the local environment file:

```bash
cp .env.example .env
```

For convenient local development, the `.env` file must contain:

```dotenv
COMPOSE_FILE=compose.yaml:compose.development.yml
```

This setting combines the base Compose configuration with `compose.development.yml`. The development override mounts the source code into the PHP container, so PHP, Smarty, and SCSS changes are available without rebuilding the application image. Composer dependencies and Smarty runtime files are stored in separate Docker volumes.

Build and start the project:

```bash
docker compose up --build -d
```

Add development categories and posts:

```bash
docker compose exec app composer db:seed
```

Open [http://localhost:8080](http://localhost:8080) in a browser. If `WEB_PORT` is changed, use the configured port instead.

Follow logs from all services:

```bash
docker compose logs -f
```

Stop the project:

```bash
docker compose down
```

## Database schema

When the `mysql_data` volume is initialized for the first time, the MySQL container creates the database and application user from the `DB_*` values in `.env`. It then imports `database/schema.sql` automatically. The schema file is not imported again when an existing data volume is reused.

The initial schema contains:

- `categories` for category names, descriptions, and URL slugs;
- `posts` for article content, images, publication dates, and view counts;
- `post_categories` for the many-to-many relationship between posts and categories.

Foreign keys in `post_categories` use `ON DELETE CASCADE`, so deleting a post or category also removes its relationship rows.

### Database seeding

The seed command creates four categories, twelve posts, and their many-to-many relationships:

```bash
docker compose exec app composer db:seed
```

Seed records are identified by their unique slugs. The command can be run again to update the same records without creating duplicates.

### Regex routes

All application routes are defined in `config/routes.php`. The file receives the router and controller instances through a typed registration function. Route patterns are anchored automatically and may contain named regular-expression groups:

```php
$router->get(
    '/posts/(?P<slug>[a-z0-9-]+)',
    static fn (Request $request, string $slug): Response => new Response($slug),
);
```

Named matches are passed to the route handler by parameter name. A controller method uses the same signature:

```php
public function show(Request $request, string $slug): Response
{
    return new Response($slug);
}
```

One optional trailing slash is accepted. A missing path returns `404 Not Found`. A path that exists for another HTTP method returns `405 Method Not Allowed` with an `Allow` response header.

## SCSS

SCSS is compiled in PHP with `scssphp/scssphp`; the project does not require npm or Node.js. The package is a development dependency and the generated CSS is committed to the repository so the production image can install Composer dependencies with `--no-dev`.

Build CSS once:

```bash
docker compose exec app composer scss:build
```

Watch all files under `assets/scss` and rebuild after changes:

```bash
docker compose exec app composer scss:watch
```

The entry file is `assets/scss/app.scss`. It is compiled to `public/assets/css/app.css`. Do not edit the generated CSS directly.

## Tests and checks

Run the router tests:

```bash
docker compose exec app composer test
```

Compile SCSS and run all tests:

```bash
docker compose exec app composer check
```

## Project structure

```text
.
├── assets/
│   └── scss/                       # SCSS source files
├── bin/
│   ├── compile-scss.php            # One-time SCSS compiler
│   ├── seed.php                    # Database seeding command
│   └── watch-scss.php              # SCSS development watcher
├── config/
│   └── routes.php                  # HTTP route definitions
├── database/
│   ├── schema.sql                  # Initial MySQL schema
│   └── seed.php                    # Development seed data
├── docker/
│   └── nginx/default.conf          # Nginx virtual host
├── public/
│   ├── assets/css/app.css          # Generated stylesheet
│   └── index.php                   # HTTP front controller
├── runtime/
│   ├── cache/                      # Smarty cache
│   └── compile/                    # Compiled Smarty templates
├── src/
│   ├── Assets/                     # Asset build services
│   ├── Controller/                 # MVC controllers
│   ├── Database/                   # PDO configuration and connection factory
│   ├── Http/                       # Request and Response objects
│   ├── Model/                      # Blog data models
│   ├── Repository/                 # PDO data access
│   ├── Routing/                    # Regex router and route exceptions
│   ├── View/                       # Smarty integration
│   └── Application.php             # Application composition root
├── templates/
│   ├── errors/                     # HTTP error views
│   ├── layouts/                    # Shared Smarty layouts
│   └── pages/                      # Page templates
├── tests/                          # Dependency-free unit tests
├── compose.development.yml         # Development services and mounts
├── compose.yaml                    # Base application services
├── composer.json                   # PHP dependencies and commands
└── Dockerfile                      # PHP-FPM image
```

The `public` directory is the only HTTP document root. Application code, templates, Composer files, and environment configuration cannot be requested directly from the web server.
