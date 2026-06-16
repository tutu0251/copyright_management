# Usage — Copyright Management (CodeIgniter 2.1.3)

This application runs as a classic **PHP 5.6 / MySQL 5.x** stack on
**CodeIgniter 2.1.3**, server-rendered (no SPA, no build step). See
**[README.md](README.md)** for setup and the stack overview.

## Run

```bash
# One-time: drop CodeIgniter 2.1.3's system/ folder in the project root,
# then load the database.
mysql -u root -e "CREATE DATABASE copyright_management CHARACTER SET utf8;"
mysql -u root copyright_management < sql/schema.sql
mysql -u root copyright_management < sql/seed.sql

# Set DB creds in application/config/database.php, then serve:
php -S localhost:8080        # or an Apache vhost at the project root
```

Open **http://localhost:8080** and sign in.

## Default account (after loading `sql/seed.sql`)

- Email: `admin@example.com`
- Password: `Admin123!`

**Change this password in any shared environment**, and set a strong
`encryption_key` in `application/config/config.php` before deploying.

## Day-to-day

| Area | Path | Permission |
|------|------|------------|
| Dashboard | `/dashboard` | `dashboard.view` |
| Works (+ assets) | `/works` | `works.*` |
| Owners | `/owners` | `owners.*` |
| Licensees | `/licensees` | `licensees.*` |
| Licenses | `/licenses` | `licenses.*` |
| Usage reports | `/usage_reports` | `usage_reports.*` |
| Cases (+ status, notes) | `/cases` | `cases.*`, `cases.status_update` |
| Activity log | `/activities` | `activities.view` |
| Reports | `/reports` | `reports.view` |
| Users | `/users` | `users.manage` |
| Roles & permissions | `/roles` | `settings.manage` |

New self-registrations (`/register`) get the read-only **Viewer** role.

## File assets

On a work's page (`/works/show/<id>`) you can upload files (stored under
`uploads/work_<id>/`), preview them inline, download them, or delete them.
Previews/downloads stream with HTTP Range support so audio/video can seek.

## Notes

- The dashboard charts are rendered with plain HTML/CSS (no JS chart library), so
  the UI works on old browsers and offline.
- Deletes are **soft** (`deleted_at`): records leave the lists but remain in the
  database.
- `uploads/` and the CodeIgniter `system/` folder are gitignored.
