# Incognito Testing

A Postman-style **API testing and debugging tool** built with **Laravel 13**. Send HTTP requests, organize them into collections and folders, manage environments with variables, share work across team workspaces, and generate live documentation for your APIs — right from the browser.

> Built as a lean, self-hostable alternative to Postman with the core workflows you use most.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Running the App](#running-the-app)
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
