# VerityTrade – Setup When Document Root Cannot Change (public_html only)

Use this when your host **does not allow** changing the document root from `public_html`.

---

## Step 1: Copy files to public_html

In **File Manager**, go to `/home/veritytr/public_html/`.

**Delete** everything inside public_html (or rename to `public_html_backup` and create new empty `public_html`).

Then **copy** from `/home/veritytr/veritytrade/public/` into `/home/veritytr/public_html/`:

| Copy this | To |
|-----------|-----|
| `index_public_html.php` | `public_html/index.php` (rename when pasting) |
| `.htaccess` | `public_html/.htaccess` |
| `build/` (entire folder) | `public_html/build/` |
| `images/` (entire folder) | `public_html/images/` ← **Required for logo** |
| `favicon.ico` (if exists) | `public_html/` |
| `robots.txt` (if exists) | `public_html/` |
| `setup.php` | `public_html/setup.php` |
| `run_artisan.php` | `public_html/run_artisan.php` (optional, for running artisan via browser) |

---

## Step 2: Edit index.php for your path

Open `public_html/index.php` (the copied `index_public_html.php`).

Find this line:
```php
$basePath = dirname(__DIR__).'/veritytrade';
```

If your Laravel folder has a **different name or path**, change it. Examples:
- Default: `/home/veritytr/veritytrade` → `dirname(__DIR__).'/veritytrade'` ✓
- If folder is `laravel`: `dirname(__DIR__).'/laravel'`
- Full path: `'/home/veritytr/veritytrade'` (use this if unsure)

Save.

---

## Step 3: Edit setup.php for your path

Open `public_html/setup.php`. Find:
```php
chdir(dirname(__DIR__));
```
Replace with:
```php
chdir('/home/veritytr/veritytrade');
```
Save.

---

## Step 4: Run setup

Visit:
```
https://yourdomain.com/setup.php?token=veritytrade-setup-2024
```

Then **delete** `public_html/setup.php`.

---

## Step 5: Storage link (for images, invoices) – **required for pictures to show**

Deal images, order slips, hero image, and phone brand images are stored under `storage/app/public/`. The site loads them from the URL `/storage/...`, so the **web root** must have a `storage` entry that points at that folder.

**When the web root is public_html**, create the link **inside public_html**:

**Option A – Run setup.php from public_html**  
If you copied `setup.php` into `public_html` and run it via the browser, it will create `public_html/storage` → `veritytrade/storage/app/public` automatically (if your host allows `symlink()` in PHP).

**Option B – File Manager (if Option A didn’t create it)**  
1. In **File Manager**, go to `public_html/`.  
2. Create **Symbolic Link**: link name `storage`, target `/home/veritytr/veritytrade/storage/app/public`.  
3. Save.

**Check:** In `public_html/` you should see `storage` (symlink). Opening `https://yourdomain.com/storage/` in the browser may show 403 or a listing; the important thing is that `https://yourdomain.com/storage/deals/` (and similar paths) serve the uploaded files. If **pictures don’t show** on the site, the most common cause is that this `storage` link is missing or wrong in public_html.

---

## Summary

- `public_html/` = web root (index.php, .htaccess, build, **storage** link, images)
- `/home/veritytr/veritytrade/` = Laravel app (app, bootstrap, config, .env, vendor, etc.)
