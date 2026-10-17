# Deployment Guide

How to run Incognito Testing in Docker locally, and how the image is pushed to Docker Hub.

---

## 1. Published Image

```bash
docker pull incognitoman/incognito-api-testing:latest
```

Tags pushed: `latest`.

---

## 2. Quick Start (Docker Compose)

```bash
# create a .env (image ships without secrets)
cp .env.example .env

APP_KEY=$(docker run -it --rm incognitoman/incognito-api-testing:latest php artisan key:generate --show)
docker compose up -d
```

Open **http://localhost:8080**, register an account, and start testing APIs.

---

## 3. What the Image Contains

Multi-stage build (`docker/Dockerfile`):

| Stage | Base | Purpose |
|---|---|---|
| `frontend` | `node:22-alpine` | `npm ci && npm run build` → Tailwind/Vite assets |
| `vendor` | `composer:2` | `composer install --no-dev --optimize-autoloader` |
| `runtime` | `php:8.3-apache` | app code + vendor + public/build only |

Runtime details:

- Document root points to `public/`; `mod_rewrite` enabled; Laravel's `public/.htaccess` handles routing.
- `.env` is **not** baked into the image (see `.dockerignore`) — config comes via `APP_*` env vars or a mounted `.env`.
- If `APP_KEY` is unset, `docker/entrypoint.sh` generates one (in-memory) at boot; set `APP_KEY` explicitly to share sessions across restarts.
- SQLite database lives at `database/database.sqlite`; migrations run automatically on every container start (`php artisan migrate --force`), idempotent.
- Runs as `www-data`; `storage/`, `bootstrap/cache/`, `database/` are chown'd to `www-data`.

---

## 4. Environment Variables

All standard Laravel-style env vars. Most important:

| Variable | Default (compose) | Purpose |
|---|---|---|
| `APP_KEY` | — (*required for stable sessions*) | encryption key, `base64:…` |
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | set `true` only for debugging |
| `APP_URL` | `http://localhost:8080` | invite & docs links |
| `DB_CONNECTION` | `sqlite` | MySQL/PostgreSQL supported |
| `SESSION_DRIVER` | `database` | |
| `QUEUE_CONNECTION` | `database` | |

MySQL alternative (compose override for the `app.env`):

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=incognito
DB_USERNAME=incognito
DB_PASSWORD=secret
```

and uncomment the `mysql` service blocks in `docker-compose.yml`.

---

## 5. Docker Hub Publishing (maintainers)

```bash
docker build -f docker/Dockerfile -t incognitoman/incognito-api-testing:latest .

# smoke-test locally before pushing
docker run --rm -p 8080:80 incognitoman/incognito-api-testing:latest &
curl -fsS http://localhost:8080/login | head    # should return the login page
docker stop ...                                  # cleanup the background run

docker login
docker push incognitoman/incognito-api-testing:latest
```

Notes:

- The Docker Hub repo `incognitoman/incognito-api-testing` is public; it is auto-created on first push, visibility can be adjusted in Docker Hub settings.
- Push a new version by tagging (e.g. `:1.0.0` alongside `:latest`) before pushing.

---

## 6. Troubleshooting

| Symptom | Fix |
|---|---|
| `419 Page Expired` on forms | `APP_KEY` changed between restarts (was auto-generated). Set a fixed `APP_KEY` in `.env`. |
| `Could not open input file` / permission errors on `storage/` | Container started with the wrong user: `docker compose down && docker compose up -d --force-recreate`. |
| 500 after changing DB backend | run `docker compose exec app php artisan migrate --force`. |
| Slow first request | migrations run on boot; subsequent starts are warm. |
