# Copyright Management (CodeIgniter 2.1.3)

A server-rendered **PHP / MySQL** application for copyright / asset / license
management — works, owners, licensees, licenses, usage reports, infringement
cases, file assets, users, roles and permissions, a dashboard, reports, and an
audit activity log. Built on **CodeIgniter 2.1.3**.

> **Stack history.** This product was previously ported to a MERN stack and has
> now been refactored **back** to classic LAMP (CodeIgniter 2.1.3 / PHP 5.6 /
> MySQL 5.x). The MERN tree has been removed.

## Stack

| Layer | Technology | Notes |
|-------|------------|-------|
| Framework | **CodeIgniter 2.1.3** | Classic MVC; server-rendered PHP views |
| Language | **PHP 5.6.2** | `mysqli` DB driver; `password_hash()`/`password_verify()` (bcrypt) |
| Database | **MySQL 5.x** | InnoDB, utf8; schema in `sql/` |
| Auth | **CI session** (encrypted cookie) | Session-based login; RBAC by permission slug |
| UI | Plain CSS (`assets/css/app.css`) | No SPA, no build step; works on old browsers |

## Prerequisites

- **PHP 5.6.2** with the `mysqli` extension (and `gd`/`fileinfo` recommended).
- **MySQL 5.x** reachable on `127.0.0.1:3306`.
- A web server with URL rewriting (Apache + `mod_rewrite`), or PHP's built-in
  server for local development.
- The official **CodeIgniter 2.1.3** release — drop its **`system/`** folder next
  to `index.php` (it is intentionally gitignored; we ship only `application/`).

## Quick start

```bash
# 1. Put the CodeIgniter 2.1.3 `system/` folder in the project root.

# 2. Create the database and load schema + seed.
mysql -u root -e "CREATE DATABASE copyright_management CHARACTER SET utf8 COLLATE utf8_general_ci;"
mysql -u root copyright_management < sql/schema.sql
mysql -u root copyright_management < sql/seed.sql

# 3. Set DB credentials in application/config/database.php
#    (or create application/config/database.local.php — gitignored).
#    Also set a real $config['encryption_key'] in application/config/config.php.

# 4. Serve it.
php -S localhost:8080            # dev: http://localhost:8080
#   …or point an Apache vhost at the project root (uses the bundled .htaccess).
```

Sign in at the site root with **`admin@example.com` / `Admin123!`**.
**Change this password in any shared environment.**

> Using PHP's built-in server, the bundled `.htaccess` is ignored; CodeIgniter
> still routes correctly because `index.php` is the front controller and
> `$config['index_page']` is empty. For pretty URLs under Apache, ensure
> `mod_rewrite` is enabled.

## Project layout

```
index.php                front controller (BASEPATH→system, APPPATH→application)
.htaccess                clean URLs; blocks system/ & application/
system/                  CodeIgniter 2.1.3 core (you provide; gitignored)
application/
  config/                config, database, routes, autoload, constants
  core/                  MY_Controller (Base/Public/Secure + RBAC), MY_Model
  helpers/               auth_helper (has_perm, current_user, audit_log, badge…)
  models/                user, role, permission, work, owner, licensee,
                         license, usagereport, case, asset, audit
  controllers/           auth dashboard works owners licensees licenses
                         usage_reports cases activities reports users roles assets
  views/                 layouts/, auth/, dashboard/, works/, … , errors/403
assets/css/app.css       UI stylesheet
uploads/                 uploaded asset files (gitignored; per-work subfolders)
sql/                     schema.sql, seed.sql
```

## Authentication & permissions

- Login creates a CI session (`user_id`). `Secure_Controller` loads the user and
  re-derives their permission slugs from `roles → role_permissions → permissions`
  on every request, and `require_perm('works.create')` gates each action.
- Permission slugs (`works.view`, `licenses.create`, `cases.status_update`, …) and
  the default roles (admin / manager / editor / viewer) match the original
  product; see `sql/seed.sql`.
- The sidebar hides links the current user can't access (`has_perm()`); the server
  still enforces every permission independently (a hidden link is also a 403).

## File assets

Uploaded files are stored on disk under `uploads/work_<id>/` (random stored
names) with a metadata row in `work_assets`. The `assets` controller streams
downloads with `inline`/`attachment` disposition and **HTTP Range** support so
audio/video can seek. The `uploads/` folder ships with an `.htaccess` that denies
direct web access and script execution.

## Configuration

| Where | Key | Purpose |
|-------|-----|---------|
| `application/config/database.php` | `hostname/username/password/database` | MySQL connection (`mysqli`) |
| `application/config/config.php` | `encryption_key` | **Set a long random value** — secures the session cookie |
| `application/config/config.php` | `base_url` | Optional; set if auto-detection misbehaves |

See [Depends.MD](Depends.MD) for the dependency rationale and [USAGE.md](USAGE.md)
for day-to-day operation.
