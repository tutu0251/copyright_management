# Copyright Management — Usage Instructions

This repository is a **CodeIgniter 4** web application that ships a **clickable UI prototype** for a copyright / asset / license management product. There is **no real database or authentication** in the current mock: screens are wired to static fixtures in PHP so you can review flows, layout, and copy.

---

## 1. What you get

| Area | Description |
|------|-------------|
| **Runtime** | PHP 8.2+, CodeIgniter 4 (`codeigniter4/framework` ^4.7) |
| **Entry** | Web requests hit `public/index.php`; document root for production should be **`public/`** |
| **Prototype UI** | Controllers in `app/Controllers/Mockup.php`; views under `app/Views/mockup/`; layout in `app/Views/layouts/main.php` |
| **Sample data** | `app/Config/CopyrightMockData.php` (works, licenses, cases, charts, roles, etc.) |
| **Static assets** | `public/assets/css/mockup.css`, `public/assets/js/mockup.js` |
| **CLI** | `spark` in the project root (CodeIgniter’s command-line tool) |

There is also a **`mockup/`** directory with an alternate self-contained layout (`mockup/Controllers/Mockup.php`, `mockup/Views/…`). The **served application** described in this document uses **`app/`** + the routes in `app/Config/Routes.php`, not that folder’s controller in isolation.

---

## 2. Prerequisites

- **PHP 8.2 or newer**
- Extensions expected by CodeIgniter 4 (see root `README.md`), notably **intl** and **mbstring**
- **Composer** ([getcomposer.org](https://getcomposer.org/)) to install PHP dependencies

Optional:

- **MySQL** (or another DB) only if you extend the app beyond the mock; the prototype **does not require** a database connection to run.
- **Xdebug** if you run PHPUnit with code coverage.

---

## 3. First-time setup

### 3.1 Install dependencies

From the project root (the folder that contains `composer.json` and `spark`):

```bash
composer install
```

### 3.2 Environment file

1. Copy the template **`env`** to **`.env`** in the project root (same level as `spark`).
2. For local development with `php spark serve`, you often only need to set the public URL of the app.

Uncomment and set **`app.baseURL`** so it matches how you open the site in the browser, **including trailing slash**, for example:

```ini
app.baseURL = 'http://localhost:8080/'
```

If you omit `.env`, CodeIgniter falls back to `app/Config/App.php` (`$baseURL` defaults to `http://localhost:8080/`), which matches the default dev server port.

### 3.3 Writable directories (production / some features)

For a full CI4 deployment, ensure `writable/` is writable by the web server. The static mock mostly renders HTML; you still follow CI4 conventions for logs, cache, and sessions if you enable them later.

---

## 4. Running the application locally

### 4.1 Built-in PHP server (recommended for quick review)

From the project root:

```bash
php spark serve
```

By default this serves at **http://localhost:8080/** with the document root set to **`public/`**.

Open in a browser:

- **http://localhost:8080/** — the root route **redirects** to the mockup dashboard at **`/mockup/`**.

If you use another host or port (for example `php spark serve --host 0.0.0.0 --port 9000`), update **`app.baseURL`** in `.env` accordingly so generated links (`base_url()`, `site_url()`) stay correct.

### 4.2 Apache / Nginx

Point the virtual host’s document root to **`public/`**, not the repository root. See the CodeIgniter 4 user guide for rewrite rules and `index.php` handling.

---

## 5. URL map (current routes)

Routes are defined in **`app/Config/Routes.php`**. All prototype screens live under the **`mockup`** prefix.

| Screen | URL path |
|--------|----------|
| Root (redirect) | `/` → redirects to `/mockup/` |
| Dashboard | `/mockup/` |
| Assets (paginated list) | `/mockup/assets` (query: `?page=2`) |
| Legacy redirect | `/mockup/works` → redirects to `/mockup/assets` (preserves query string) |
| Register work | `/mockup/register` |
| Work detail | `/mockup/work/{id}` — example: `/mockup/work/WRK-001` |
| Ownership | `/mockup/ownership` |
| Licenses list | `/mockup/licenses` |
| License detail | `/mockup/license/{id}` — examples: `LIC-2024-089`, `LIC-2025-014` |
| Usage reports | `/mockup/usage-reports` |
| Monitoring | `/mockup/monitoring` |
| Reports (charts + usage) | `/mockup/reports` |
| Cases | `/mockup/cases` |
| Case detail | `/mockup/case/{id}` — examples: `IC-014`, `IC-019`, `IC-021`, `IC-024` |
| Settings | `/mockup/settings` |

**404 behavior:** Unknown work IDs, license IDs, or case IDs throw CodeIgniter’s page-not-found response (by design).

---

## 6. How the prototype behaves

- **Forms and most actions** are non-functional or cosmetic unless you add backend logic.
- **Data** is read-only and comes from **`CopyrightMockData`** (deterministic generated works such as `WRK-001` … `WRK-025`, plus fixed license/case rows).
- **Charts** appear on the dashboard and reports pages when the layout loads Chart.js-related behavior via `mockup.js` (see `app/Controllers/Mockup.php` for `useCharts` / `chartPayload`).

---

## 7. Customizing content and UI

| Goal | Where to look |
|------|----------------|
| Change KPIs, tables, lists, chart series | `app/Config/CopyrightMockData.php` |
| Change routes or add pages | `app/Config/Routes.php` + `app/Controllers/Mockup.php` (or new controllers) |
| Change HTML structure / copy | `app/Views/mockup/*.php`, `app/Views/layouts/main.php` |
| Change styling | `public/assets/css/mockup.css` |
| Change client-side charts / interactions | `public/assets/js/mockup.js` |

The controller comment in `Mockup.php` notes the intended next step: replace with real controllers, models, and auth filters.

---

## 8. Tests

Dev dependencies include PHPUnit. From the project root:

```bash
composer test
```

or on Windows:

```bash
vendor\bin\phpunit
```

See **`tests/README.md`** for database test configuration and coverage commands. The stock starter tests may not cover the mockup screens specifically.

---

## 9. Optional: OPcache preloading

**`preload.php`** in the project root is CodeIgniter’s **sample** OPcache preload script. It is **not** required to run the app. To use it in production you would configure `opcache.preload` in `php.ini` to point at this file and follow the comments inside `preload.php`. Do not enable preload output/debug `echo` in production.

---

## 10. Troubleshooting

| Symptom | What to check |
|---------|----------------|
| CSS/JS missing or wrong layout | Browser should request **`/assets/css/mockup.css`** (under `public/assets/`). Ensure you are not serving only the repo root without `public/` as docroot. |
| Links go to wrong host/port | Set **`app.baseURL`** in `.env`** to match the URL you use in the browser. |
| Blank page / errors | PHP error log; CI4 logs under **`writable/logs/`** if logging is enabled. |
| `composer` or `php spark` fails | PHP version ≥ 8.2; run commands from the directory that contains **`composer.json`** and **`spark`**. |

---

## 11. Further reading

- [CodeIgniter 4 user guide](https://codeigniter.com/user_guide/)
- Root **`README.md`** — framework version, server requirements, security note about `public/`
- **`mockup/README.md`** — describes the **`mockup/`** subfolder layout and an older-style URL table; use **`app/Config/Routes.php`** as the source of truth for the running app’s paths
