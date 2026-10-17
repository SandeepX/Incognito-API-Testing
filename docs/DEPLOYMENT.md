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

APP_KEY=$(docker run --rm --entrypoint php incognitoman/incognito-api-testing:latest artisan key:generate --show)
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

## 5. HTTPS / Reverse Proxy

The container itself serves plain HTTP on port 80. Terminate TLS in front of it —
never expose the raw container to the internet. Two standard setups:

### Nginx reverse proxy

```nginx
server {
    listen 443 ssl http2;
    server_name tester.example.com;

    ssl_certificate     /etc/letsencrypt/live/tester.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tester.example.com/privkey.pem;

    location / {
        proxy_pass         http://127.0.0.1:8080;   # or the container IP/port
        proxy_set_header   Host               $host;
        proxy_set_header   X-Real-IP          $remote_addr;
        proxy_set_header   X-Forwarded-For    $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto  $scheme;
        proxy_read_timeout 120s;                     # long-ish proxy requests
        client_max_body_size 32m;                    # large JSON payloads
    }
}

server {
    listen 80;
    server_name tester.example.com;
    return 301 https://$host$request_uri;
}
```

Then set in `.env`:

```env
APP_URL=https://tester.example.com
```

This matters: `APP_URL` is used for workspace **invite links** and **docs links** —
if it still says `localhost:8080`, everyone you invite gets a broken link.

If the request to the app really comes through a TLS proxy, Laravel trusts the
`X-Forwarded-Proto` header once `TrustProxies` is configured (Laravel 13 handles
`REMOTE_ADDR` proxies automatically; for a dedicated proxy IP, list it in
the trusted-proxies configuration (`bootstrap/app.php` middleware settings).

### Caddy (simplest, auto-HTTPS)

```Caddyfile
tester.example.com {
    reverse_proxy 127.0.0.1:8080
}
```

Caddy fetches and renews the certificate automatically. No other config needed.

⚠️ **Self-signed / plain HTTP on a public server**: the app transmits API keys
and bearer tokens typed into the request builder — plaintext HTTP puts your
users' secrets on the public network. Always TLS in production.

---

## 6. Docker Hub Publishing (maintainers)

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

## 7. Troubleshooting

| Symptom | Fix |
|---|---|
| `419 Page Expired` on forms | `APP_KEY` changed between restarts (was auto-generated). Set a fixed `APP_KEY` in `.env`. |
| `Could not open input file` / permission errors on `storage/` | Container started with the wrong user: `docker compose down && docker compose up -d --force-recreate`. |
| 500 after changing DB backend | run `docker compose exec app php artisan migrate --force`. |
| Slow first request | migrations run on boot; subsequent starts are warm. |
