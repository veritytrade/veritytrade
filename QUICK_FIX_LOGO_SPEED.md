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

## Slow loading – code fixes applied

The app has been optimized to reduce database queries on every request:

1. **Feature flags / settings** – Cached for 5 minutes (previously hit DB on every call).
2. **App bootstrap** – Removed `Schema::hasTable` and extra mail config queries.
3. **Homepage / landing** – Removed 4–5 `Schema::hasTable` checks per request.
4. **`.env`** – Prefer `SESSION_DRIVER=file` and `CACHE_STORE=file` on shared hosting (faster than database).

**If still slow after deploy:**

- In `.env`: set `SESSION_DRIVER=file` and `CACHE_STORE=file`.
- Run `php artisan config:cache` (and route/view cache via setup if available).
- Ask your host if PHP **opcache** is enabled.
