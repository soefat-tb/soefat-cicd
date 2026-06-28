# AGENTS.md — Pramuka Soefat TB Website

## Repo structure

Flat PHP application (no framework). Two sub-apps sharing the same docroot:

| Path | Purpose |
|------|---------|
| `/` | Public site: landing, news (`berita.php`), registration (`d4ft4r.php`), org chart (`bagan.php`) |
| `/spss/` | Student Portal (SPSS): passkey login, attendance (`pr.php`), schedule (`jadwal.php`), member list |
| `/admin/` | Admin login form — redirects to `../4dm1n.php` (dashboard) or `../ganti.php` (sudo) |
| `index2.php` | Admin data management panel (CRUD for members, credentials, attendance) |

## Database

- **Host**: `sql309.infinityfree.com` (shared MySQL)
- **Two connection files** both contain hardcoded credentials (keep them in sync):
  - `config.php` → DB `if0_37650982_pramuka` (public site)
  - `koneksi.php` → DB `if0_37650982_p` (used by `index.php`, `berita.php`, `d4ft4r.php`, `admin/index.php`, etc.)
  - `spss/config.php` → DB `if0_37650982_p` (SPSS app)
- Timezone: `Asia/Jakarta` (set in `.htaccess`)

## Key pages as entrypoints

- **Public**: `index.php` (includes `koneksi.php`)
- **SPSS**: `spss/index.php` — session-based auth via passkey (WebAuthn). Redirects to `spss/login.php` if unauthenticated.
- **Admin**: `admin/index.php` POST → auth against `admin` table, then redirects to `4dm1n.php` (role `admin1`) or `ganti.php` (role `sudo`)

## Build / test / lint

No build system, no tests, no linter, no CI. Pure PHP served directly.

## Dependencies

- **Root**: no `composer.json`. `vendor/` at root contains PhpSpreadsheet (used by export files).
- **SPSS**: `spss/vendor/autoload.php` required in `register.php`. Dependencies: `lbuchs/webauthn` (passkey auth), symfony components, `web-auth/webauthn-lib`, `phpdocumentor`, `doctrine/deprecations`.

## Auth system

- **Public admin**: session-based, checks `admin` table. Passwords stored as both bcrypt hash AND plaintext. Two roles: `admin1` (regular admin) and `sudo` (super admin).
- **SPSS**: WebAuthn passkey login via `lbuchs/webauthn`. Credentials stored in `credentials` table keyed by NIS. Sessions set `$_SESSION['authenticated']` and `$_SESSION['login']`.
- **API**: `api_siswa.php` — JSON endpoint, checks `$_SESSION['login']`.

## Notable quirks

- `admin/index.php` has `usleep(rand(100000, 300000))` before login queries (timing-based rate limiting).
- `4dm1n.php` checks `$_SESSION['username']` against hardcoded `admin1` / `sudo` strings.
- Daftar page (`d4ft4r.php`) validates phone with `/^08[1-9]\d{8,9}$/`.
- `spss/index.php` has session timeout of 1800s (30 min) with JS polling every 30s.
- `.htaccess` blocks sensitive files and suspicious query strings, enables HTTPS redirect, sets security headers.
- `spss/.htaccess` also blocks sensitive files, disables directory listing.
