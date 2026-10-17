# Data Model

All domain entities use **UUID primary keys** (except `workspace_invites`, which uses serial `id`). Default connection is SQLite; the schema below is portable to MySQL/PostgreSQL.

---

## Entity Relationship Diagram

```
users ─┬─< workspace_user >─── workspaces (owner: owner_id)
       │              (role: owner|admin|member)
       │
       └─< workspace_invites >── workspaces

workspaces ──< collections ──< collection_items (self-referencing tree)
workspaces ──< environments
```

---

## Tables

### `users`
| Column | Type | Notes |
|---|---|---|
| id | bigint | autoincrement |
| name | string | |
| email | string, unique | |
| email_verified_at | datetime, nullable | |
| password | string | hashed cast |
| remember_token | string, nullable | |

### `workspaces`
| Column | Type | Notes |
|---|---|---|
| id | uuid PK | client-generated |
| owner_id | FK → users | workspace creator |
| name | string | |
| description | text, nullable | |
| timestamps | — | |

Relations: `collections() hasMany`, `users() belongsToMany via workspace_user (role)`, `owner() belongsTo`, `invites() hasMany`.

### `workspace_user` (pivot)
| Column | Type | Notes |
|---|---|---|
| workspace_id | FK | cascade delete |
| user_id | FK | cascade delete |
| role | string | `owner` / `admin` / `member` |
| timestamps | — | |

### `collections`
| Column | Type | Notes |
|---|---|---|
| id | uuid PK | |
| workspace_id | FK → workspaces, nullable | `null` = personal/unassigned; `nullOnDelete` |
| name | string | |
| timestamps | — | |

Relations: `workspace() belongsTo`, `items() hasMany whereNull(parent_id) orderBy order`, `allItems() hasMany`.

### `collection_items` (folders + requests)
| Column | Type | Notes |
|---|---|---|
| id | uuid PK | |
| collection_id | FK → collections | cascade delete |
| parent_id | FK → collection_items, nullable | folder nesting; cascade delete subtree |
| type | enum | `folder` / `request` |
| name | string | |
| method | string, nullable, default GET | request items only |
| url | text, nullable | request items only |
| request_data | JSON, nullable | params, headers, cookies, auth, body |
| order | unsigned int | sibling ordering for drag & drop |
| description | text, nullable | added in 2026_06_16 — docs enrichment |
| examples | JSON, nullable | added 2026_06_16 — saved response examples |
| timestamps | — | |

Indexes: `(collection_id, parent_id)`, `(parent_id, order)`.

### `environments`
| Column | Type | Notes |
|---|---|---|
| id | uuid PK | |
| workspace_id | FK, nullable | added 2026_06_16_000003 |
| name | string | indexed |
| variables | JSON, nullable | `[{key, value, …}]` used for `{{var}}` substitution |
| timestamps | — | |

### `workspace_invites`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| workspace_id | FK → workspaces | cascade delete |
| invited_by | FK → users | cascade delete |
| email | string, nullable | optional direct invite |
| token | string(64), unique | shareable invite token (`Str::random(64)`) |
| role | string, default `member` | role granted on accept |
| expires_at / accepted_at | timestamps, nullable | validity = not accepted & not expired |
| timestamps | — | |

### Framework tables
- `cache`, `cache_locks` — database cache store
- `sessions` — session driver = database
- `jobs`, `job_batches`, `failed_jobs` — database queue

---

## API Resource Transformers

JSON emitted to the client is shaped by `app/Http/Resources/`:

| Resource | Used for |
|---|---|
| `WorkspaceResource` | workspace list/show, membership context |
| `CollectionResource` | collection list/show |
| `ItemResource` | collection items incl. `request_data`, `description`, `examples` |
| `EnvironmentResource` | environments incl. variables |

---

## Migrations Index

| Migration | Purpose |
|---|---|
| (0001_01_01_000000/000001) | users, cache, jobs, sessions |
| (0001_01_01_000002) | failed_jobs |
| 2026_06_11_113407 | environments |
| 2026_06_11_113410 | workspaces |
| 2026_06_11_113418 | collections |
| 2026_06_11_113436 | collection_items |
| 2026_06_16_000001 | workspace_user pivot |
| 2026_06_16_000002 | workspace_invites |
| 2026_06_16_000003 | workspace_id on environments |
| 2026_06_16_071722 | description + examples on collection_items |
