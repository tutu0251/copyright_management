# Copyright Management — Developer & User Guide

This document describes the **Copyright Management** application: how to run and extend it as a developer, and how to use the product as an end user or administrator. It augments the CodeIgniter 4 starter `README.md`, `USAGE.md` (prototype notes), and `offline_tools/OFFLINE_README.txt`.

---

## 1. What this application is

- **Stack:** PHP **8.2+**, [CodeIgniter 4](https://codeigniter.com/) (`codeigniter4/framework` ^4.7), MySQL (MySQLi) for persistence, [Dompdf](https://github.com/dompdf/dompdf) for PDF export.
- **Purpose:** Web application for managing **copyright-related catalog data**: registered **works** (assets), **owners**, **licensees**, **licenses**, **usage reports** (monitoring / disposition), **infringement cases** (with evidence and notes), **reports** (analytics + CSV/PDF export), **audit activities**, **users**, and **role-based permissions**.
- **Two UI surfaces:**
  1. **Main application** (`/` … authenticated, database-backed, RBAC). This is the primary product described in the **User guide** below.
  2. **Static mockup** (`/mockup/…`) — read-only screens driven by **`app/Config/CopyrightMockData.php`**, no login, useful for demos or layout review. See [Section 6](#6-mockup-routes-optional-demo).

Document root in production must be the **`public/`** directory, not the repository root.

---

## 2. User guide

### 2.1 Getting access

1. An administrator creates your account (**Users** area) or you self-register if **Register** is enabled for your deployment.
2. Open the site URL your team provides (for local dev, often `http://localhost:8080/`).
3. Sign in on **Login**. Sign out via **Logout** (POST, typically a button in the header).

**First-time database setup (admin):** After migrations and seeding, a default admin exists (see [Section 4.4](#44-database-migrations-and-seeding)). Use only in development; change the password before any shared or production use.

### 2.2 Roles and what they mean

Seeded roles (custom deployments may differ):

| Role | Typical use |
|------|-------------|
| **Administrator** | Full access; can manage users and role permissions. |
| **Manager** / **Editor** | Create and update catalog, licensing, usage reports, and cases; broad operational access without full admin. |
| **Viewer** | Read-only: view dashboard, reports, and entities; no create/update/delete. |

Exact abilities are defined by **permission** assignments (see [2.4](#24-permissions-reference)). Administrators can adjust role permissions under **Settings → Roles** if they have `settings.manage`.

### 2.3 Main areas of the app

Navigation items appear based on your permissions (see `app/Helpers/nav_helper.php` function `copyright_nav_items()`).

| Area | Path (typical) | What you do there |
|------|------------------|-------------------|
| **Dashboard** | `/dashboard` | Summary metrics and entry point after login. |
| **Works** (assets) | `/works` | List, register, view, edit works; manage linked owners per work. |
| **Owners** | `/owners` | Directory of rights holders / entities; link to works. |
| **Licensees** | `/licensees` | Parties licensed to use works. |
| **Licenses** | `/licenses` | License agreements tied to works and licensees. |
| **Usage reports** | `/usage-reports` | Monitoring findings; mark disposition, escalate to cases where configured. |
| **Cases** | `/cases` | Infringement case workflow, evidence, notes, status. |
| **Activities** | `/activities` | Audit / activity log (read). |
| **Reports** | `/reports` (subpaths for works, licenses, usage, cases, activity) | Analytics; CSV/PDF export where allowed. |
| **Users** | `/users` | User accounts (admin). |
| **Settings → Roles** | `/settings/roles` | Role and permission matrix (admin). |

### 2.4 Permissions reference

Routes enforce permissions via the `permission` filter (see `app/Config/Routes.php`). Slugs are stored in the database and seeded by `RbacPermissionSeeder`.

**Works:** `works.view`, `works.create`, `works.update`, `works.delete`  

**Owners:** `owners.view`, `owners.create`, `owners.update`, `owners.delete`  

**Licensees:** `licensees.view`, `licensees.create`, `licensees.update`, `licensees.delete`  

**Licenses:** `licenses.view`, `licenses.create`, `licenses.update`, `licenses.delete`  

**Usage reports:** `usage_reports.view`, `usage_reports.create`, `usage_reports.update`, `usage_reports.delete`  

**Cases:** `cases.view`, `cases.create`, `cases.update`, `cases.delete`, `cases.status_update`  

**Cross-feature:** Escalating a usage report to a case requires `cases.create` in addition to usage-report permissions.

**App-wide:** `dashboard.view`, `reports.view`, `activities.view`, `settings.manage`, `users.manage`

If the permissions tables are missing, the permission service can fall back so logged-in users are treated as authorized for some checks—**normal operation assumes migrations have been applied.**

### 2.5 Language selection

The app supports localized strings (e.g. English, Japanese, Chinese). Language files live under `app/Language/{locale}/`. Switching language is typically via a locale route such as `/lang/{locale}` (see `app/Config/Routes.php` and `Language` controller).

### 2.6 Troubleshooting for users

| Issue | What to try |
|-------|-------------|
| “Forbidden” or redirect to login | Session expired or missing permission for that URL; sign in again or ask an admin to grant the right role/permission. |
| Missing menu items | Your role does not include the related permission. |
| Forms fail after submit | CSRF or validation errors; ensure cookies are enabled and you are not mixing HTTP/HTTPS incorrectly for your environment. |

---

## 3. Developer guide — environment and setup

### 3.1 Requirements

- **PHP 8.2+** with extensions: **intl**, **mbstring**, **json**; **mysqlnd** for MySQL; **curl** if you use HTTP client features.
- **Composer** for dependencies.
- **MySQL** (or compatible) for the main app data.

See the project root `README.md` for framework-level requirements.

### 3.2 Install dependencies

From the project root (directory containing `composer.json` and `spark`):

```bash
composer install
```

### 3.3 Environment (`.env`)

1. Copy **`env`** to **`.env`** in the project root.
2. Set **`database.default.*`** for your MySQL instance (hostname, database name, username, password, port).
3. Set **`app.baseURL`** to your public URL with a **trailing slash**, e.g. `http://localhost:8080/`.  
   The app can infer base URL from the request when unset (`app/Config/App.php`); explicit `.env` values are still recommended for CLI and consistent links.
4. For production, set **`CI_ENVIRONMENT = production`**, configure **`encryption.key`** per CodeIgniter docs, and review session and cookie settings.

### 3.4 Database migrations and seeding

With MySQL running and `.env` configured:

```bash
php spark migrate
php spark db:seed DatabaseSeeder
```

- **`DatabaseSeeder`** runs **`AuthSeeder`** (roles + default admin user) and **`RbacPermissionSeeder`** (permissions and role-permission mappings).

**Default admin (development only):**

- Email: `admin@example.com`  
- Password: `Admin123!`  

Change this password immediately outside local development.

### 3.5 Run the application locally

```bash
php spark serve
```

Default: **http://localhost:8080/** with document root **`public/`**. Root route **`/`** redirects to **`/dashboard`** (requires login).

### 3.6 Deploy behind Apache / Nginx

Point the vhost document root to **`public/`**. Follow the [CodeIgniter 4 user guide](https://codeigniter.com/user_guide/) for URL rewriting so requests route to `public/index.php`. Ensure `writable/` is writable by the web server for logs, sessions, and caches.

### 3.7 Offline / air-gapped install (Windows bundle)

If the repository includes `offline_tools/` with `vendor_snapshot` and batch scripts, follow **`offline_tools/OFFLINE_README.txt`**: restore `vendor` from the snapshot, copy `.env`, run `php spark serve` or configure the server to use `public/`.

### 3.8 Tests

```bash
composer test
```

or:

```bash
vendor\bin\phpunit
```

See **`tests/README.md`** for database test configuration and coverage. Configure the **`tests`** DB group in `app/Config/Database.php` or `.env` when running tests that need MySQL.

---

## 4. Developer guide — architecture

### 4.1 Request flow

1. **Front controller:** `public/index.php`
2. **Filters** (`app/Config/Filters.php`): global **locale**, **CSRF**; route groups use **guest** (login/register) or **auth** (signed-in). Individual routes add **`permission:some.slug`** via `PermissionFilter`.
3. **Controllers** in `app/Controllers/` handle HTTP and call models/services.
4. **Views** in `app/Views/`; layout pieces under `app/Views/layouts/`.
5. **Models** in `app/Models/`; shared logic in `app/Services/` (e.g. `PermissionService`, `AuditLogService`).

### 4.2 Important directories

| Path | Role |
|------|------|
| `app/Config/` | Routes, filters, validation, services registration. |
| `app/Controllers/` | Feature controllers (`Works`, `Licenses`, `Cases`, …). |
| `app/Models/` | Database access per entity. |
| `app/Database/Migrations/` | Schema history; run with `php spark migrate`. |
| `app/Database/Seeds/` | Seeders for auth and RBAC. |
| `app/Filters/` | `AuthFilter`, `GuestFilter`, `PermissionFilter`, `LocaleFilter`. |
| `app/Helpers/` | `auth_helper`, `permission_helper`, `nav_helper`, etc. |
| `app/Language/` | UI strings per locale. |
| `public/assets/` | CSS/JS for the main UI (and mockup assets where shared). |
| `writable/` | Logs, cache, sessions (permissions on disk in production). |

### 4.3 Adding a new permission

1. Add a migration if you need new DB-driven behavior (usually permissions are rows in `permissions`).
2. Insert the slug in **`RbacPermissionSeeder`** (or a new seeder) so new installs get it; existing installs may need a one-off seed or admin UI under **Settings → Roles**.
3. Register the route with `['filter' => 'permission:your.slug']` in **`app/Config/Routes.php`**.
4. Use `user_can('your.slug')` or helpers in views/controllers as needed.
5. After changing role permissions in the UI, **`PermissionService::clearCache()`** is used where relevant so changes apply immediately.

### 4.4 Audit logging

`AuditLogService` and `AuditLogModel` support tracking changes for compliance. Entity views may include audit components (see `app/Views/components/entity_audit_history.php`).

### 4.5 PDF / CSV reports

Reports and exports are implemented in **`Reports`** controller; PDF generation uses **Dompdf** (`composer.json`). Ensure fonts and memory limits are adequate in production for large PDFs.

---

## 5. URL map — main application

Authoritative list: **`app/Config/Routes.php`**. Summary:

- **`/`** → redirect to dashboard  
- **`/login`**, **`/register`** (guest)  
- **`/dashboard`** — `dashboard.view`  
- **`/reports`**, **`/reports/works`**, **`/reports/licenses`**, **`/reports/usage`**, **`/reports/cases`**, **`/reports/activity`**, exports **`/reports/export/csv`**, **`/reports/export/pdf`** — `reports.view`  
- **`/activities`** — `activities.view`  
- **`/settings/roles`**, **`/settings/roles/(:num)/permissions`** — `settings.manage`  
- **`/users`** … — `users.manage`  
- **`/owners`** … — owner permissions  
- **`/works`** … — work permissions; **`/works/(:num)/owners`** for pivot  
- **`/licensees`** … — licensee permissions  
- **`/licenses`** … — license permissions  
- **`/usage-reports`** … — usage report permissions; evidence download routes as defined in Routes  
- **`/cases`** … — case permissions; evidence file download route as defined  

All successful access to these routes requires an authenticated session and the listed permission unless your deployment changes filters.

---

## 6. Mockup routes (optional demo)

Unauthenticated routes under **`/mockup/`** use **`Mockup`** controller and **`app/Config/CopyrightMockData.php`**. They do **not** reflect live database data.

| Screen | Path |
|--------|------|
| Dashboard | `/mockup/` |
| Assets list | `/mockup/assets` |
| Register work | `/mockup/register` |
| Work detail | `/mockup/work/{id}` |
| Ownership | `/mockup/ownership` |
| Licenses | `/mockup/licenses` |
| License detail | `/mockup/license/{id}` |
| Usage reports | `/mockup/usage-reports` |
| Monitoring | `/mockup/monitoring` |
| Reports | `/mockup/reports` |
| Cases | `/mockup/cases` |
| Case detail | `/mockup/case/{id}` |
| Settings | `/mockup/settings` |

**Note:** Historical **`USAGE.md`** focuses on this mockup-centric workflow. The **live** product routes are under **`/dashboard`**, **`/works`**, etc., as in Section 5.

---

## 7. Security checklist for operators

- Use **HTTPS** in production; align **`app.baseURL`** and reverse-proxy headers.
- Set a strong **`encryption.key`** and restrict **`writable/`** permissions.
- Remove or change default **seed** credentials; disable open **registration** if not required.
- Keep **PHP**, **MySQL**, and **Composer** dependencies updated.
- Review **CSRF** (enabled globally) and session cookie settings for your domain.

---

## 8. Further reading

- [CodeIgniter 4 user guide](https://codeigniter.com/user_guide/)
- **`README.md`** — generic CI4 starter requirements  
- **`USAGE.md`** — mockup-oriented usage (partially superseded by this guide for the database-backed app)  
- **`offline_tools/OFFLINE_README.txt`** — offline Composer workflow  
- **`tests/README.md`** — PHPUnit and coverage  

---

*Document generated to match the repository layout and conventions as of the project state including RBAC, migrations, and main feature controllers.*
