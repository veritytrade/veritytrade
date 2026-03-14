# After Pull – What to Run on the Server

After you **pull** the latest code on the server (e.g. cPanel, no SSH), run these so the app uses the new routes and config.

---

## 1. Clear caches (required)

Open each URL in your browser (use your real domain and the same token you use for `run_artisan.php`):

| Step | URL |
|------|-----|
| Clear route cache | `https://veritytrade.ng/run_artisan.php?token=veritytrade-setup-2024&cmd=route:clear` |
| Clear config | `https://veritytrade.ng/run_artisan.php?token=veritytrade-setup-2024&cmd=config:clear` |
| Rebuild config | `https://veritytrade.ng/run_artisan.php?token=veritytrade-setup-2024&cmd=config:cache` |
| Clear view cache (optional) | `https://veritytrade.ng/run_artisan.php?token=veritytrade-setup-2024&cmd=view:clear` |

**Why:** Image URLs now use the **`/_f/`** route (no symlink needed). Routes are loaded from `routes/web.php`; if the server was using a cached route list (`route:cache`), run **route:clear** so the `/_f/` route is active. Config cache can also serve old paths, so clear and rebuild it.

---

## 2. If images or pages still fail

- **PHP opcache:** The server may be serving old PHP files. In cPanel → **Select PHP Version** or **MultiPHP INI Editor**, see if you can **restart PHP** or **clear opcache**. Or open a ticket and ask: “Please clear PHP opcache (or restart PHP-FPM) for my account.”
- **Storage symlink (optional):** For images, you can still create the symlink so the server serves files directly (faster): in `public_html/`, create a symbolic link named `storage` pointing to `.../veritytrade/storage/app/public`. See **PUBLIC_HTML_SETUP.md** Step 5.

---

## 3. Check the log if you get 500

Look at `storage/logs/laravel.log` (inside the Laravel folder, e.g. `veritytrade/storage/logs/laravel.log`). The last lines will show the real error (e.g. missing method, wrong path, decryption). That will tell you exactly what to fix next.
