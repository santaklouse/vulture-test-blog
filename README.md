# Vulture Blog

A test assignment implementing a small blog with plain PHP, MySQL, and Smarty, without a PHP framework.

## Requirements

- Docker Desktop with Docker Compose;
- the TCP port configured through `WEB_PORT` must be available; it defaults to `8080`.

PHP, Composer, MySQL, and Nginx do not need to be installed on the host machine.

## Development setup

Create the local environment file:

```bash
cp .env.example .env
```

For convenient local development, the `.env` file must contain:

```dotenv
COMPOSE_FILE=compose.yaml:compose.development.yml
```

This setting combines the base Compose configuration with `compose.development.yml`. The development override mounts the source code into the PHP container, so PHP and Smarty template changes are available without rebuilding the image. Composer dependencies and Smarty runtime files are stored in separate Docker volumes.

Build and start the project:

```bash
docker compose up --build -d
```

Wait for the containers to become healthy and inspect their status:

```bash
docker compose ps
```

Open [http://localhost:8080](http://localhost:8080) in a browser.

Follow logs from all services:

```bash
docker compose logs -f
```

Stop the project:

```bash
docker compose down
```

## Docker services

- `web` runs Nginx 1.27 and accepts requests on `127.0.0.1:${WEB_PORT}`;
- `app` runs PHP 8.3 FPM with `pdo_mysql`, Composer, and Smarty;
- `db` runs MySQL 8.4 and stores its data in a named Docker volume.

Nginx forwards requests that do not match a static file to `public/index.php`. Routing can therefore be added later without changing the web server configuration.

## Composer commands

Inspect installed dependencies inside the PHP container:

```bash
docker compose exec app composer show
```

Check the entry point syntax:

```bash
docker compose exec app php -l public/index.php
```

The `composer.lock` file pins dependency versions and must be committed to the repository.
