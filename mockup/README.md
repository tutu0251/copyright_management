# Copyright Management — UI mockup

Self-contained prototype under this folder: controllers, config (sample data), views, and static CSS source. The main app wires routes in `app/Config/Routes.php` and serves `mockup/public/css/mockup.css` at **`/mockup/css/mockup.css`** so styles work without copying files into `public/`.

## Run locally

```bash
cd d:\work\copyright_man
php spark serve
```

Open **http://localhost:8080/**. Set `app.baseURL` in `.env` if URLs do not match your host/port.

## URL map

| Screen | Path |
|--------|------|
| Dashboard | `/` |
| Asset Registry | `/registry` |
| Register Work | `/register-work` |
| Work Detail | `/works/{id}` |
| Ownership | `/ownership` |
| Licenses | `/licenses` |
| License Detail | `/licenses/{id}` |
| Usage Reports | `/usage-reports` |
| Cases | `/cases` |
| Case Detail | `/cases/{id}` |
| Settings / Roles | `/settings` |
| Stylesheet (served from disk here) | `/mockup/css/mockup.css` |

## Folder layout

```
mockup/
  README.md
  Controllers/
    Mockup.php
  Config/
    CopyrightMockData.php
  Views/
    layout/
      main.php
      header.php
      sidebar.php
    dashboard.php
    asset_registry.php
    register_work.php
    work_detail.php
    ownership.php
    licenses.php
    license_detail.php
    usage_reports.php
    infringement_cases.php
    case_detail.php
    settings.php
  public/
    css/
      mockup.css
```

## Notes

- Namespace: `App\Mockup\…` with Composer mapping `App\Mockup\` → `mockup/`.
- Forms and most buttons are non-functional by design.
- Unknown work/license/case IDs return 404.
