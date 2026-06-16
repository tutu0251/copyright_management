# Dependencies — Copyright Management

This document describes the **components** this project depends on and how they
fit together. The application is a classic **LAMP** app: **CodeIgniter 2.1.3** on
**PHP 5.6.2** with **MySQL 5.x**, server-rendered. It was refactored back from a
prior MERN port — there is no Node, npm, or MongoDB in this tree anymore.

For setup commands see [README.md](README.md) and [USAGE.md](USAGE.md).

---

## 1. Stack at a glance

| Layer | Technology | Location / notes |
|-------|------------|------------------|
| Framework | **CodeIgniter 2.1.3** | `system/` (you provide), `application/` (this repo) |
| Language | **PHP 5.6.2** | `mysqli` driver; `password_hash`/`password_verify` (bcrypt) |
| Database | **MySQL 5.x** | InnoDB / utf8; DDL + seed in `sql/` |
| Web server | Apache + `mod_rewrite` (or `php -S` for dev) | Clean URLs via root `.htaccess` |

```text
Browser
  │  HTTP (session cookie)
  ▼
CodeIgniter 2.1.3 controller  (application/controllers/*)
  │  Secure_Controller → RBAC permission check
  ▼
Model (Query Builder, application/models/*)  ──►  MySQL 5.x
  │
  └─►  View (application/views/*)  →  HTML
Uploaded files: filesystem under uploads/work_<id>/ (metadata in work_assets)
```

---

## 2. External runtime (you must provide)

| Component | Requirement | Notes |
|-----------|-------------|-------|
| **CodeIgniter 2.1.3** | the official release `system/` folder | Dropped next to `index.php`; gitignored. We ship only `application/`, `index.php`, and `.htaccess`. |
| **PHP** | **5.6.2** | Extensions: `mysqli` (required), `fileinfo`/`gd` recommended for uploads. |
| **MySQL** | **5.x** | `metadata` audit column is `TEXT` (MySQL 5.x has no JSON type). |
| **Web server** | Apache (`mod_rewrite`) or PHP built-in | `.htaccess` strips `index.php` and blocks `system/` & `application/`. |

There are **no Composer packages**. CodeIgniter 2.1.3 bundles everything the app
uses (Session, Upload, Form validation, URL/Form helpers, Query Builder).

> Note: the `offline_tools/` folder contains an unrelated **CodeIgniter 4 +
> Composer** snapshot (dompdf / phpunit / faker) left over from earlier work. It
> is **not** used by this CI 2.1.3 application and can be ignored or removed.

---

## 3. CodeIgniter components used (from `system/`)

| Component | Loaded in | Role |
|-----------|-----------|------|
| Database (Query Builder) | autoload | All data access (`application/models/*`) |
| Session | autoload | Login state (`user_id`), flash messages |
| URL helper | autoload | `site_url()`, `redirect()` |
| Form helper | autoload | `form_open()`, `set_value()`, etc. |
| Upload library | `assets` controller | File uploads → `uploads/work_<id>/` |
| Form validation library | `auth`, `users` | Registration / user-creation rules |

Configured in `application/config/autoload.php` and `.../config.php`.

---

## 4. Application structure (this repo)

| Path | Role |
|------|------|
| `index.php` | Front controller (sets `BASEPATH`/`APPPATH`) |
| `.htaccess` | Clean URLs; denies `system/` & `application/` |
| `application/config/` | `config`, `database` (mysqli), `routes`, `autoload`, `constants` |
| `application/core/MY_Controller.php` | `MY_Controller`, `Public_Controller`, `Secure_Controller` (auth + RBAC) |
| `application/core/MY_Model.php` | Soft-delete CRUD base over Query Builder |
| `application/helpers/auth_helper.php` | `has_perm`, `current_user`, `audit_log`, `human_size`, `badge` |
| `application/models/` | One model per table |
| `application/controllers/` | `auth`, `dashboard`, `works`, `owners`, `licensees`, `licenses`, `usage_reports`, `cases`, `assets`, `activities`, `reports`, `users`, `roles` |
| `application/views/` | Layout partials + per-module views |
| `assets/css/app.css` | UI stylesheet (no JS framework) |
| `sql/schema.sql`, `sql/seed.sql` | Database schema + seed |
| `uploads/` | Stored asset files (gitignored) |

---

## 5. Database schema

Defined in `sql/schema.sql` (InnoDB, utf8). Tables: `users`, `roles`,
`permissions`, `user_roles`, `role_permissions`, `works`, `owners`,
`work_owners`, `licensees`, `licenses`, `usage_reports`, `infringement_cases`,
`case_notes`, `audit_logs`, `work_assets`. Integer `AUTO_INCREMENT` PKs; the old
embedded arrays (user roles, role permissions, case notes) are normalized into
junction / child tables; soft deletes via nullable `deleted_at`.

`sql/seed.sql` is idempotent and loads the permission catalog, the four default
roles with their preset grants, the admin user (bcrypt hash of `Admin123!`), and
a small sample dataset.

---

## 6. Security notes

- **Passwords**: bcrypt via `password_hash()` / `password_verify()` (PHP 5.5+).
- **Sessions**: CI encrypted cookie — set a real `encryption_key` in
  `application/config/config.php`.
- **RBAC**: enforced server-side per action (`require_perm`), not just in the UI.
- **Uploads**: served only through the `assets` controller; `uploads/.htaccess`
  denies direct access and disables PHP execution there.
- Consider enabling CI's CSRF protection (`$config['csrf_protection']`) before a
  production deployment.
