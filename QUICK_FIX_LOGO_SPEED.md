# Quick Fix: Logo + Slow Loading + Pictures

## Pictures (deals, orders, hero) not showing

Uploaded images are stored in `storage/app/public/`. The site loads them from the URL `/storage/...`.

**Option A – Laravel fallback (no symlink needed)**  
The app has a route that serves storage files through Laravel. If your host blocks symlinks, images should still work: requests to `/storage/deals/xxx.jpg` are handled by the app. Just deploy the latest code and clear config cache (`php artisan config:cache` or run_artisan.php).

**Option B – Symbolic link (faster, recommended)**  
When the web root is **public_html**, you can create a **symbolic link** so the server serves files directly (faster):

- In `public_html/`: link name `storage`, target `/home/veritytr/veritytrade/storage/app/public`  
- Or run `setup.php` from public_html (with the token); it may create this link if the host allows it.

See **PUBLIC_HTML_SETUP.md** Step 5 for full details.

---

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
