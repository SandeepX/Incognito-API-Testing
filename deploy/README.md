# Run Incognito Testing from Docker Hub

No source code, no build steps — just Docker and this folder.

## 1. Configure

```bash
cp .env.example .env
```

Generate an app key and paste it into your new `.env` line `APP_KEY=`:

```bash
docker run --rm --entrypoint php incognitoman/incognito-api-testing:latest artisan key:generate --show
```

## 2. Run

```bash
docker compose up -d
```

Open **http://localhost:8080**, register an account, start testing APIs.

Migrations run automatically on first boot; SQLite data persists in the
`incognito-sqlite` volume, so restarts and upgrades keep everything.

## 3. Upgrade later

```bash
docker pull incognitoman/incognito-api-testing:latest
docker compose up -d
```

## Files

| File | Purpose |
|---|---|
| `docker-compose.yml` | runs image `incognitoman/incognito-api-testing:latest`, port 8080, SQLite volume |
| `.env.example` | copy to `.env` and set `APP_KEY` (+ `APP_URL` on a public server) |
