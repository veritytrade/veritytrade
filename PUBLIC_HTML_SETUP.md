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

## Step 5: Storage link (for images, invoices)

The `storage:link` command creates `public/storage` → `../storage/app/public`.  
Since we're in public_html, you need a link inside public_html.

**Option A – File Manager:** 
- In `public_html/`, create **Symbolic Link**
- Link from: `storage`
- Link to: `/home/veritytr/veritytrade/storage/app/public`

**Option B – If setup.php ran:** It may have created the link in veritytrade/public. Copy or recreate for public_html.

---

## Summary

- `public_html/` = web root (index.php, .htaccess, build, storage link)
- `/home/veritytr/veritytrade/` = Laravel app (app, bootstrap, config, .env, vendor, etc.)
