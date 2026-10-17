# API Reference

All routes live in `routes/web.php`. Everything except `guest` auth pages and `GET /invite/{token}` requires a logged-in session (Laravel `auth` middleware — cookie based, not token based).

---

## Authentication (guest)

| Method | Path | Action |
|---|---|---|
| GET | `/login` | login form (`login`) |
| POST | `/login` | log in |
| GET | `/register` | register form |
| POST | `/register` | create account |
| GET | `/forgot-password` | request reset (`password.request`) |
| POST | `/forgot-password` | send reset link (`password.email`) |
| GET | `/reset-password/{token}` | reset form (`password.reset`) |
| POST | `/reset-password` | update password (`password.update`) |

| Method | Path | Notes |
|---|---|---|
| POST | `/logout` | `auth` middleware (`logout`) |
| GET | `/invite/{token}` | accept workspace invite (`invite.accept`) — must be logged in, no `auth` middleware strictly enforced here per route |

## App Pages (auth)

| Method | Path | Action |
|---|---|---|
| GET | `/` | main API tester UI (`ApiTesterController::index`) |
| GET | `/collections/{collection}/docs` | public shareable docs page for a collection (`collection.docs`) |

## Request Proxy (auth)

| Method | Path | Body | Response |
|---|---|---|---|
| POST | `/api-tester/proxy` | `method`, `url`, `headers[]`, `cookies[]`, `params[]`, `auth`, `body` (validated by `ProxyRequest`) | `{ status, statusText, headers, body, size, time }` |

Methods supported: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS.
`time` = round-trip ms; failed outgoing requests return a normalized JSON error (no HTML error page).

## REST API — JSON CRUD (auth, prefix `/api`)

### Workspaces
| Method | Path | Notes |
|---|---|---|
| GET | `/api/workspaces` | list (index) |
| POST | `/api/workspaces` | create |
| GET | `/api/workspaces/{workspace}` | show |
| PUT/PATCH | `/api/workspaces/{workspace}` | update |
| DELETE | `/api/workspaces/{workspace}` | delete |

### Workspace members & invites
| Method | Path | Notes |
|---|---|---|
| GET | `/api/workspaces/{workspace}/members` | list |
| POST | `/api/workspaces/{workspace}/invites` | create invite link / email invite (`createInvite`) |
| DELETE | `/api/workspaces/{workspace}/members/{user}` | remove member |

### Collections
Standard `apiResource`: `GET|POST /api/collections`, `GET|PUT|PATCH|DELETE /api/collections/{collection}`.

### Collection items (tree of folders & requests)

| Method | Path | Notes |
|---|---|---|
| GET | `/api/collections/{collectionId}/items` | full tree (index) |
| POST | `/api/collections/{collectionId}/items` | create folder or request (store) |
| PUT | `/api/collections/items/{collectionItem}` | update (rename, move, edit request fields, description, examples) |
| DELETE | `/api/collections/items/{collectionItem}` | delete + cascade children |
| POST | `/api/collections/{collectionId}/reorder` | bulk drag-and-drop reorder/move (circular-reference protected) |

### Environments
Standard `apiResource`: `GET|POST /api/environments`, `GET|PUT|PATCH|DELETE /api/environments/{environment}`.

---

## Validation

Mutation endpoints are backed by `FormRequest` classes in `app/Http/Requests/`:
`ProxyRequest`, `StoreWorkspaceRequest`, `UpdateWorkspaceRequest`,
`StoreCollectionRequest`, `UpdateCollectionRequest`,
`StoreCollectionItemRequest`, `UpdateCollectionItemRequest`,
`StoreEnvironmentRequest`, `UpdateEnvironmentRequest`.

On validation failure the client receives the standard Laravel JSON
`422 {"message": …, "errors": {field: [msg]}}`.
