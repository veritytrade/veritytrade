# Security Checklist

Use this to avoid common vulnerabilities in production.

---

## 1. Environment

- **APP_DEBUG=false** in production (in `.env`). Never set `true` on a live site.
- **APP_KEY** must be set and kept secret. Rotate it if it was ever exposed.
- **.env** must not be in the web root. It should live in the Laravel app folder (e.g. `veritytrade/.env`), not in `public_html/`.

---

## 2. Helper scripts (public_html / public)

- **setup.php** – Run once with `?token=...`, then **delete** it. It can run migrations and seed the DB.
- **run_artisan.php** – Token-protected; only whitelisted cache/config commands. Set **ARTISAN_TOKEN** in `.env` to a long random string (e.g. `openssl rand -hex 32`) and use that in the URL. Delete the file when you have SSH and no longer need it.
- **debug_500.php, install_composer.php, test_db.php, run_migrations.php** – Blocked by `.htaccess`. Do not copy them to the server, or delete them; if present, they stay blocked.

---

## 3. File serving (/_f/)

- The `/_f/{path}` route serves only from `storage/app/public/`.
- Path traversal (`..`, `\`, null bytes) is stripped; only whitelisted extensions (jpg, png, gif, webp, svg, pdf, ico) are allowed.
- Resolved path is checked with `realpath()` so files outside `storage/app/public` cannot be served.
- **User-uploaded SVGs** can contain scripts. If you allow SVG uploads, sanitize them before storage (e.g. strip `<script>` and event handlers) to prevent XSS when the SVG is displayed.

---

## 4. General

- Use **HTTPS** (SSL) for the site.
- Keep Laravel and PHP up to date.
- Restrict admin and sensitive routes to authorised users; the app uses auth and permissions for this.
- Do not commit `.env` or any file containing secrets to Git.
