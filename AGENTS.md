# AGENTS.md — Pramuka Soefat TB Website

## Repo structure

Flat PHP application (no framework). Two sub-apps sharing the same docroot:

| Path | Purpose |
|------|---------|
| `/` | Public site: landing, news (`news.php`), registration (`registration.php`), org chart (`organization-chart.php`) |
| `/spss/` | Student Portal (SPSS): passkey login, attendance (`attendance.php`), schedule (`schedule.php`), member list (`member-list.php`) |
| `/admin/` | Admin login (`login.php`), dashboard (`dashboard.php`), user manager (`user-manager.php`), CRUD pages |
| `/config/` | DB connection files (`database.php`, `database-secondary.php`) |

## File naming convention (migration from old → new)

| Old name | New name |
|----------|----------|
| `4dm1n.php` | `admin/dashboard.php` |
| `admin/index.php` | `admin/login.php` |
| `admin/index2.php` | removed (merged into dashboard) |
| `admin_berita.php` | `admin/news-manager.php` |
| `admin_jadwal.php` | `admin/schedule-manager.php` |
| `admin_bagan.php` | `admin/org-chart-admin.php` |
| `ganti.php` | `admin/user-manager.php` |
| `add_user.php` | `admin/siswa-manager.php` |
| `view_pendaftaran.php` | `admin/registration-list.php` |
| `view_file.php` | `admin/file-list.php` |
| `upload_file.php` | `admin/file-upload.php` |
| `vw_absen.php` | `admin/attendance-list.php` |
| `view_lgn.php` | `admin/login-log.php` |
| `api_siswa.php` | `admin/api-siswa.php` |
| `berita.php` | `news.php` |
| `detail_berita.php` | `news-detail.php` |
| `bagan.php` | `organization-chart.php` |
| `d4ft4r.php` | `registration.php` |
| `exportexcel.php` | `export-siswa.php` |
| `exportexcelpendaftaran.php` | `export-pendaftaran.php` |
| `koneksi.php` | `config/database.php` |
| `config.php` | `config/database-secondary.php` |
| `generate_sitemap.php` | `sitemap-generator.php` |
| `ip.php` | `ip-detector.php` |
| `office-rdr.php` | `office-reader.php` |
| `spss/index.php` | `spss/dashboard.php` |
| `spss/verify-login.php` | `spss/login-verify.php` |
| `spss/register.php` | `spss/passkey-register.php` |
| `spss/auth-check.php` | `spss/auth.php` |
| `spss/pr.php` | `spss/attendance.php` |
| `spss/list_siswa.php` | `spss/member-list.php` |
| `spss/jadwal.php` | `spss/schedule.php` |
| `spss/bagann.php` | `spss/organization-chart.php` |
| `spss/config.php` | `spss/config/database.php` |

## Database

- **Host**: `sql309.infinityfree.com` (shared MySQL)
- **Two connection files** both contain hardcoded credentials (keep them in sync):
  - `config/database.php` → DB `if0_37650982_pramuka` (public site)
  - `config/database-secondary.php` → DB `if0_37650982_p` (used by most app pages)
  - `spss/config/database.php` → DB `if0_37650982_p` (SPSS app)
- Timezone: `Asia/Jakarta` (set in `.htaccess`)

## Key pages as entrypoints

- **Public**: `index.php` (includes `config/database.php`)
- **SPSS**: `spss/dashboard.php` — session-based auth via passkey (WebAuthn). Redirects to `spss/login.php` if unauthenticated.
- **Admin**: `admin/login.php` POST → auth against `admin` table, then redirects to `admin/dashboard.php` (role `admin1`) or `admin/user-manager.php` (role `sudo`)

## Build / test / lint

No build system, no tests, no linter, no CI. Pure PHP served directly.

## CI/CD — GitHub Actions → FTP

- **Workflow**: `.github/workflows/deploy.yml`
- **Trigger**: push to `main`
- **Steps**: PHP syntax check → lftp cleanup (one-time stale file removal) → FTP deploy via `SamKirkland/FTP-Deploy-Action@v4.3.5`
- **Secrets**: `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`
- **Mode**: additive (no `dangerous-clean-slate`). Old files not in repo remain on server unless explicitly cleaned.
- **Excluded from deploy** (via `.ftpignore` and workflow `exclude`):
  - `.git/`, `.github/`, `*.md`, `*.sql`, `*.ova`
  - `uploads/`, `Uploads/`, `storage/`, `apk-anggota/`, `apk-dewan/`, `sweetalert2/` (user data)

## Dependencies

- **Root**: no `composer.json`. `vendor/` at root contains PhpSpreadsheet (used by export files).
- **SPSS**: `spss/vendor/autoload.php` required in `passkey-register.php`. Dependencies: `lbuchs/webauthn` (passkey auth), symfony components, `web-auth/webauthn-lib`, `phpdocumentor`, `doctrine/deprecations`.

## Auth system

- **Public admin**: session-based, checks `admin` table. Passwords stored as both bcrypt hash AND plaintext. Two roles: `admin1` (regular admin) and `sudo` (super admin).
- **SPSS**: WebAuthn passkey login via `lbuchs/webauthn`. Credentials stored in `credentials` table keyed by NIS. Sessions set `$_SESSION['authenticated']` and `$_SESSION['login']`.
- **API**: `admin/api-siswa.php` — JSON endpoint, checks `$_SESSION['login']`.

## Notable quirks

- `admin/login.php` has `usleep(rand(100000, 300000))` before login queries (timing-based rate limiting).
- `admin/dashboard.php` checks `$_SESSION['username']` against hardcoded `admin1` / `sudo` strings.
- Daftar page (`registration.php`) validates phone with `/^08[1-9]\d{8,9}$/`.
- `spss/dashboard.php` has session timeout of 1800s (30 min) with JS polling every 30s.
- `.htaccess` blocks sensitive files and suspicious query strings, enables HTTPS redirect, sets security headers.
- `spss/.htaccess` also blocks sensitive files, disables directory listing.
