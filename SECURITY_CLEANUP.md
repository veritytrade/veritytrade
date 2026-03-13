# Security Cleanup for public_html

## Files to DELETE from public_html (on server)

These are one-time setup/debug scripts and should **not** remain on a live site:

| File | Risk |
|------|------|
| `debug_500.php` | No token. Shows stack traces, paths, server structure. |
| `install_composer.php` | Setup script. Exposes server info. |
| `test_db.php` | Token-protected but reveals DB host, database name, username. |
| `run_migrations.php` | Token-protected. Delete after migrations succeed. |
| `setup.php` | Token-protected. Delete after initial setup. |

**Keep only:** `index.php`, `.htaccess`, `build/`, `images/`, `storage` (symlink).

---

## run_artisan.php

- **Keep** only if you need it often to run cache commands.
- **Delete** when you're done with maintenance.
- If you keep it: change the token in the file to something random and secret (not the default).

---

## .htaccess protection

The main `.htaccess` now blocks direct access to: `debug_500.php`, `install_composer.php`, `test_db.php`, `run_migrations.php`, `setup.php`. Even if those files remain, visitors cannot run them.

---

## Action checklist

1. [ ] Copy updated `.htaccess` from repo to `public_html/.htaccess`
2. [ ] Delete from public_html: `debug_500.php`, `install_composer.php`, `test_db.php`
3. [ ] Delete `run_migrations.php` and `setup.php` if setup is complete
4. [ ] Delete or secure `run_artisan.php` when not needed
