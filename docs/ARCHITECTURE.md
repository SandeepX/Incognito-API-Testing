# System Architecture

Incognito Testing is a **server-rendered Laravel application with a single dynamic client** (`resources/views/apitester/index.blade.php`)
that behaves like a Postman-style SPA while keeping all sensitive HTTP proxying server-side.

---

## 1. High-Level Overview

```
┌──────────────────────────────────────────────────────────────────┐
│                          Browser (User)                          │
│   Blade-rendered apitester UI (jQuery + Tailwind 4, via Vite)    │
│                                                                  │
│   • Sidebar: Collections / Environments / History / Flows        │
│   • Tabs: request builder + response viewer                      │
│   • Variable engine: {{var}} substitution + highlight            │
└───────┬───────────────────────────────────────────────┬──────────┘
        │ JSON over fetch (same origin)                 │ page loads
        ▼                                               ▼
┌──────────────────────────────────────────────────────────────────┐
│                       Laravel 13 (PHP 8.3)                       │
│                                                                  │
│  routes/web.php                                                  │
│   ├── Auth (login, register, password reset)      [guest]        │
│   ├── /api-tester/proxy (server-side request proxy)[auth]        │
│   ├── /collections/{id}/docs                      [auth]         │
│   ├── /invite/{token}   (workspace invite accept)                │
│   └── /api/* CRUD: workspaces, collections, items,               │
│                    environments, members, invites  [auth]        │
│                                                                  │
│  Controllers ── FormRequests (validation) ── Eloquent Models     │
│  Resources (API transformers) ── JSON responses                  │
└───────┬───────────────────────────────────────────────┬──────────┘
        │ Laravel HTTP Client (cURL)                    │ Eloquent
        ▼                                               ▼
┌───────────────────────┐                 ┌──────────────────────────┐
│  User's target APIs   │                 │  SQLite (default) /      │
│  (cross-origin; the   │                 │  MySQL / PostgreSQL      │
│   proxy bypasses CORS │                 │  database is also used   │
│   and SSL problems)   │                 │  for cache/sessions/queue│
└───────────────────────┘                 └──────────────────────────┘
```

---

## 2. Design Decisions

| Decision | Rationale |
|---|---|
| **Server-side proxy** (`POST /api-tester/proxy`) | The browser never fires cross-origin requests. Laravel's HTTP Client sends them, sidestepping CORS and mixed-content/SSL issues, and returns normalized JSON (`status`, `statusText`, `headers`, `body`, `size`, `time`). |
| **Single-page feel inside one Blade view** | No Node SSR required → trivial deployment; jQuery manages tabs/request state locally; collections & environments persist via the `/api` endpoints. |
| **UUIDs as primary keys** on domain entities | Safer exposure in client-side URLs and JSON payloads. |
| **Hierarchy via `parent_id` + `order`** | `collection_items` is a self-referencing tree (workspace → collections → folders → requests) with drag-and-drop reordering. |
| **Database cache/session/queue** | Zero external deps by default (Redis/Memcached are optional, controlled by `.env`). |
| **Client history in localStorage** | Last 50 requests, no server round-trip, one-click reload. |

---

## 3. Request Flow — sending an API test

1. User picks method, URL, params, headers, cookies, auth, body and an active environment.
2. Client substitutes `{{variables}}` from the environment and shows resolved values on hover.
3. Client `fetch → POST /api-tester/proxy` with the prepared request description.
4. `ProxyRequest` (FormRequest) validates the payload (method whitelist, URL, body types).
5. `ApiTesterController::proxy()` builds a Laravel HTTP Client call
   (`timeout(30)`, headers and body mapped by method) and measures elapsed time.
6. The original response is normalized to JSON; the client renders the Response Viewer
   (Pretty / Raw / Preview, headers, cookies, copy/save/clear).
7. Optionally the request (and a saved response example) is persisted as a
   `collection_item` inside a folder/collection tree.

Proxy methods supported: **GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS**.

---

## 4. Request Flow — persisting state (collections)

```
Create/rename/move item        POST/PUT/DELETE /api/collections[*]
        │
        ▼
CollectionItemController ── validation (nested folder rules,
        │                   circular-reference protection)
        ▼
collection_items (uuid, collection_id, parent_id, type: folder|request,
                  method, url, request_data JSON, order,
                  description, examples JSON ← migration 2026_06_16)
        │
        ▼
GET /collections/{collection}/docs   → CollectionDocumentationController
                                       renders apitester/docs.blade.php
       shareable docs page: folders, request/response details, examples)
```

Reorder moves are checkbox-less bulk operations behind
`POST /api/collections/{id}/reorder`, with circular-reference protection
when dragging a folder into its own subtree.

---

## 5. Request Flow — workspaces & invites

- Every user starts in a personal workspace. Workspaces have members with roles: `owner`, `admin`, `member` (pivot table `workspace_user.role`).
- `POST /api/workspaces/{workspace}/invites` creates a `workspace_invites` row:
  random-64 token, optional email, role, `expires_at` (7 days).
- `GET /invite/{token}` → `WorkspaceMemberController::acceptInvite()`: validates expiry/acceptance, then `accept(User)` attaches the user to the workspace and stamps `accepted_at`.

---

## 6. Authentication

| Flow | Files |
|---|---|
| Register / login (session based) | `Auth/RegisterController`, `Auth/LoginController` |
| Password reset (tokenized email flow) | `Auth/ForgotPasswordController`, `Auth/ResetPasswordController` |
| Session amplifier | Laravel `web` middleware, sessions live in the database per `.env.example` (`SESSION_DRIVER=database`) |

---

## 7. Module Map

| Area | Implementation |
|---|---|
| Request proxy | `app/Http/Controllers/ApiTesterController.php`, `app/Http/Requests/ProxyRequest.php` |
| Workspaces CRUD | `app/Http/Controllers/Api/WorkspaceController.php`, `AppServiceProvider` bootstraps defaults |
| Members & invites | `Api/WorkspaceMemberController.php` |
| Collections | `Api/CollectionController.php` |
| Tree items | `Api/CollectionItemController.php` |
| Environments | `Api/EnvironmentController.php` |
| Docs pages | `CollectionDocumentationController.php` → `resources/views/apitester/docs.blade.php` |
| API shaping | `app/Http/Resources/*.php` (Workspace / Collection / Item / Environment) |
| Frontend UI | `resources/views/apitester/index.blade.php` + `partials/{css,script}.blade.php` |
| Schema | `database/migrations/*.php` (see [DATA_MODEL.md](DATA_MODEL.md)) |

---

## 8. Non-Functional Properties

- **State multiplication: minimal** — the only mutable browser state worth mentioning is request history in localStorage and the active theme.
- **Multi-tenancy** — isolation happens per workspace membership (via pivot table `workspace_user`), not by row-level security.
- **Security touchpoints** — FormRequest validation on all JSON mutations, `auth` middleware on everything except guest auth pages (`Routes → routes/web.php`), `password`/`remember_token` hidden in `User`.
