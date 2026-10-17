# Incognito Testing

A Postman-style **API testing and debugging tool** built with **Laravel 13**. Send HTTP requests, organize them into collections and folders, manage environments with variables, share work across team workspaces, and generate live documentation for your APIs — right from the browser.

> Built as a lean, self-hostable alternative to Postman with the core workflows you use most.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Quick Start for Non‑Developers](#quick-start-for-non-developers--10-minutes)
- [Installation](#installation)
- [Running the App](#running-the-app)
- [Docker](#docker)
- [Documentation](#documentation)
- [Project Structure](#project-structure)
- [Screenshots](#screenshots)
- [License](#license)

---

## Features

### Request Builder
- **HTTP methods:** GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS
- **Query Params, Headers, Cookies** — add/edit/remove key-value rows
- **Authorization types:** None, Bearer Token, Basic Auth, API Key
- **Body types:** None, Raw (plain text), JSON, Form Data
- **Server-side proxy** to send requests and bypass CORS / SSL issues
- **Cancel in-flight requests** and resizable request/response panels
- **Keyboard shortcut:** `Ctrl/Cmd + Enter` to send the current request
- **Multiple request tabs** — create, rename, close, close others, close all

### Response Viewer
- Status code + status text, response time (ms) and response size
- **Pretty / Raw / Preview** formats for the response body
- **JSON syntax highlighting**
- Separate **Headers** and **Cookies** response tabs
- Copy, Save, and Clear response actions
- **Save a request + its response** into a collection or folder

### cURL Support
- **Import cURL** commands — parses method, URL, headers, body, cookies, and auth (Bearer/Basic/API key)
- **Export** the current request as a ready-to-paste cURL command

### Collections
- Create, rename, delete, and **search** collections
- **Folders & subfolders** with requests organized in a tree
- **Drag & drop** reordering and moving items between folders/collections
  - Circular-reference protection when moving folders into their own subfolders
- **Bulk operations** — select multiple items and move or delete them at once
- **Import / Export** collections as JSON files
- Per-request **description and response examples** (docs enrichment)

### Environments & Variables
- Create and manage multiple **environments** with key-value **variables**
- **Variable substitution** (`{{variableName}}`) in URL, params, headers, cookies, body, and auth fields
- Live **variable highlighting** with hover preview showing the resolved value
- Quick-switch active environment from the top bar

### Workspaces & Collaboration
- Multiple **workspaces**, each with its own collections and environments
- Roles: **owner, admin, member**
- Invite team members via **shareable invite link** (or email) with 7-day expiry
- Accept invites, remove members, or leave a workspace

### Documentation
- Auto-generated, shareable **documentation pages** for any collection
- Folder grouping, request/response details, and response examples
- Copy the docs link or open docs in a new tab

### Authentication
- Register, login, logout
- **Password reset** flow (forgot password → reset via token)

### UI / UX
- Postman-inspired interface with **light & dark themes** (toggle + persisted)
- Toast notifications and SweetAlert2 modals
- Request **history** (last 50 requests, stored locally) with one-click reload
- Responsive sidebar (Collections / Environments / History / Flows)

---

## Tech Stack

| Layer     | Technology                                  |
|-----------|---------------------------------------------|
| Backend   | Laravel 13 (PHP `^8.3`)                      |
| Frontend  | Blade + jQuery + Tailwind CSS 4 (via Vite)   |
| HTTP      | Laravel HTTP Client (server-side proxy)      |
| Database  | SQLite by default (MySQL/PostgreSQL supported) |
| Assets    | Vite, Tailwind CSS                           |

---

## Requirements

- **PHP** `^8.3` (with required Laravel extensions: `curl`, `mbstring`, `sqlite3`, etc.)
- **Composer** (latest)
- **Node.js** `^18` or newer and **npm**
- A database:
  - **SQLite** (default — no extra setup needed), or
  - MySQL / PostgreSQL (update your `.env`)
- Redis / Memcached are **optional** (the app defaults to database cache/queue)

---

## Quick Start for Non‑Developers (≈ 10 minutes)

No code, no build tools — just install **Docker Desktop** ([Windows/Mac](https://www.docker.com/products/docker-desktop/), [Linux](https://docs.docker.com/engine/install/)) and follow along.

### Step 1 — Get the app files (2 min)

Download this project's repository as a ZIP (green **Code** button → *Download ZIP*), unzip it, and open a terminal inside the folder...

...or, if you only want to *run* the app (not develop it), grab just the **`deploy/`** folder — everything below works from inside it too.

### Step 2 — Create an access key (1 min)

In your terminal, run:

```bash
docker run --rm --entrypoint php incognitoman/incognito-api-testing:latest artisan key:generate --show
```

It prints a line like `base64:XzHjsYLkDp4b...` — copy it.

### Step 3 — Configure (2 min)

From inside the `deploy/` folder (or project root), run:

```bash
cp .env.example .env
```

Open the new `.env` file in any text editor, find the line starting with `APP_KEY=`, and paste your copied key after the `=`. Optionally set `APP_PORT` if port 8080 is busy.

### Step 4 — Run (3 min)

```bash
docker compose up -d
```

First run downloads the app (a few hundred MB) — subsequent starts take seconds. When it's done, open **http://localhost:8080** in a browser.

### Step 5 — Sign up (1 min)

Click **Register**, make an account, and you're in — collections, environments, and history all live in local Docker storage, so they come back even after a restart or upgrade.

### Stopping & restarting

```bash
docker compose down     # stop
docker compose up -d    # start again later — your data is kept
```

### Common issues

| What you see | What it means | Fix |
|---|---|---|
| `port is already allocated` | Something else uses port 8080 | Set `APP_PORT=8090` (or any free port) in `.env`, then `docker compose up -d` again |
| Browser shows nothing | App still starting or wrong port | Wait ~15 s and refresh; open the URL shown after `docker compose up -d` |
| `docker: command not found` | Docker isn't installed (or running) | Install/start **Docker Desktop**, then retry |
| Forgot password / want a fresh start | — | `docker compose down -v` deletes saved data (users, collections), then `up -d` re-creates everything |

> Putting this on a server for your team? See [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) (HTTPS setup, MySQL, upgrades).

---

## Installation

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd Incognito-API-Testing
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Set up the environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure the database** in `.env` (defaults to SQLite)
   ```env
   DB_CONNECTION=sqlite
   # DB_DATABASE=absolute/path/to/database.sqlite
   ```
   Create the SQLite file if it does not exist:
   ```bash
   touch database/database.sqlite
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Install and build frontend assets**
   ```bash
   npm install
   npm run build
   ```

> Tip: the project ships a `composer setup` script that runs most of the steps above for you:
> ```bash
> composer setup
> ```

---

## Docker

The app ships with a production-ready multi-stage Docker image (published on
Docker Hub as [`incognitoman/incognito-api-testing`](https://hub.docker.com/r/incognitoman/incognito-api-testing)).

```bash
# Run the published image
cp .env.example .env
APP_KEY=$(docker run --rm --entrypoint php incognitoman/incognito-api-testing:latest artisan key:generate --show)
docker compose up -d
# → http://localhost:8080
```

Or build locally:

```bash
docker build -f docker/Dockerfile -t incognitoman/incognito-api-testing:latest .
docker compose up -d
```

Migrations run automatically on container start; the SQLite database persists
in the `sqlite-data` volume; set `APP_KEY` in `.env` for stable sessions.
See [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) for environment variables,
MySQL setup, and publishing instructions.

---

## Documentation

| Doc | Contents |
|---|---|
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | System architecture, request flows, design decisions, module map |
| [docs/DATA_MODEL.md](docs/DATA_MODEL.md) | Database schema, entity relationships, migrations index |
| [docs/API_REFERENCE.md](docs/API_REFERENCE.md) | Every HTTP route with methods, paths and semantics |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Docker deployment, env vars, Docker Hub publishing |

---

## Running the App

**Start the dev server**
```bash
php artisan serve
```
Then open http://localhost:8000 in your browser, register an account, and start testing APIs.

**Optional: run everything at once** (server + queue + logs + Vite HMR)
```bash
composer dev
```

**Start Vite separately** (for hot-reloading during development)
```bash
npm run dev
```

**Run the test suite**
```bash
composer test
```

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ApiTesterController.php        # Server-side request proxy
│   │   ├── CollectionDocumentationController.php
│   │   ├── Auth/                          # Login, Register, Password Reset
│   │   └── Api/                           # Workspaces, Collections, Items, Environments
│   ├── Requests/                          # FormRequest validation classes
│   └── Resources/                         # API Resource transformers
├── Models/                                # User, Workspace, Collection, CollectionItem,
│                                          # Environment, WorkspaceInvite
database/
├── migrations/                            # Schema for all entities
└── seeders/
resources/views/
├── apitester/
│   ├── index.blade.php                    # Main Postman-style UI
│   ├── docs.blade.php                     # Auto-generated collection docs
│   └── partials/                          # CSS, JS, docs partials
└── auth/                                  # Auth views
routes/web.php                             # All web + API routes
```

---

## License

[MIT](LICENSE)
