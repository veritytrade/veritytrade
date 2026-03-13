# Stage 5: Application Deployment – cPanel Checklist

Follow these steps in order. Replace `your_username` with your cPanel login.

---

## Step 1: Create project folder

In **File Manager** (or via SFTP):

1. Go to your home directory (e.g. `/home/your_username/`)
2. Create folder: `veritytrade`
3. Full path: `/home/your_username/veritytrade/`

---

## Step 2: Upload files

Upload from your local `d:\APP\xampp\htdocs\veritytrade\` to `/home/your_username/veritytrade/`:

| Upload these | Do NOT upload |
|--------------|----------------|
| app/ | .env |
| bootstrap/ | node_modules/ |
| config/ | vendor/ *(if using Composer on server)* |
| database/ | .git/ (optional) |
| public/ | storage/logs/* (keep folder, empty) |
| resources/ | |
| routes/ | |
| storage/ (empty: app, framework, logs) | |
| composer.json, composer.lock | |
| package.json, vite.config.js, tailwind.config.js, postcss.config.js | |
| public/build/ | |

**Tip:** Zip the folder locally, upload the zip, then Extract in File Manager.

---

## Step 3: Create .env on server

1. Copy `.env.production.example` to the server (or create new file named `.env`).
2. Place it in `/home/your_username/veritytrade/.env` (same folder as `artisan`).
3. Fill in:
   - APP_URL (your domain)
   - DB_DATABASE, DB_USERNAME, DB_PASSWORD
   - SUPER_ADMIN_EMAIL, SUPER_ADMIN_PASSWORD (16+ chars)

---

## Step 4: Upload vendor/ (if no Composer on server)

If the server has no Composer: run locally first, then upload:

```powershell
cd d:\APP\xampp\htdocs\veritytrade
composer install --no-dev --optimize-autoloader
```

Upload the entire `vendor/` folder to `/home/your_username/veritytrade/vendor/`.

---

## Step 5: Set document root

1. **cPanel** → **Domains** → your domain
2. **Document Root** → Change
3. Set to: `/home/your_username/veritytrade/public`

---

## Step 6: Run deployment commands

Open **cPanel Terminal** (or SSH). Run:

```bash
cd ~/veritytrade
```

**If .env is new (no APP_KEY yet):**
```bash
php artisan key:generate
```

**If you uploaded vendor/ (no Composer on server), skip this. Otherwise:**
```bash
composer install --no-dev --optimize-autoloader
```

**Then run:**
```bash
php artisan storage:link
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Step 7: Set permissions

In File Manager:
- Right-click `storage/` → Change Permissions → 755 or 775 (recursive)
- Right-click `bootstrap/cache/` → Change Permissions → 775

---

## Done

Proceed to **Stage 6** to verify: homepage, login, admin, invoice.
