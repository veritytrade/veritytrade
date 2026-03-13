# Quick Fix: Logo + Slow Loading

## Logo not showing

The logo lives in `public/images/`. With **public_html** as web root, those files must be in `public_html/` too.

**Fix:** Copy the entire `images/` folder into `public_html/`:

1. **File Manager** → `/home/veritytr/veritytrade/public/`
2. Right-click **images** folder → **Copy**
3. Go to `/home/veritytr/public_html/`
4. **Paste**
5. You should end up with `public_html/images/invoice/logo.png` and `public_html/images/invoice-icons/`

---

## Slow loading

**Checks on your host:**
- PHP **opcache** – ask support if it’s enabled
- **LiteSpeed** – many cPanel hosts use it and it’s usually fast

**In your app:**
1. Ensure caches are built (already done by `run_migrations.php`):
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
2. In `.env`: `APP_DEBUG=false` (debug mode slows the app)
3. If you have SSH/Terminal: run `php artisan optimize` on deploy

**Host limits:**
- Shared hosting can be slow; upgrade or move to a VPS if needed.
