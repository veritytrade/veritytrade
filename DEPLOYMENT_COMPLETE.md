# VerityTrade – Complete cPanel Deployment Instructions

**Your setup:** `/home/veritytr` | Repo: `https://github.com/veritytrade/veritytrade.git`  
**GitHub push:** ✓ Done

---

## PRE-REQUISITES (do first)

- [ ] cPanel login
- [ ] Domain pointing to your server
- [ ] Backed up old `public_html` (zip/download)
- [ ] Code pushed to GitHub

---

## STEP 1: PHP Version & Extensions

1. **cPanel → MultiPHP Manager**
2. Select your domain → Set PHP version to **8.2** or higher
3. **cPanel → Select PHP Version** (or **MultiPHP INI Editor**)
4. Enable: **gd**, **pdo_mysql**, **mbstring**, **openssl**, **curl**, **fileinfo**, **dom**, **xml**  
   *(gd is required for invoice PDFs)*

---

## STEP 2: Create Database

1. **cPanel → MySQL® Databases**
2. **Create New Database:** e.g. `veritytrade` → Create
3. **Create New User:** e.g. `dbuser` with strong password → Create
4. **Add User to Database:** Select user and database → **All Privileges** → Add
5. Note the **full names** (cPanel adds prefix):
   - Database: `veritytr_veritytrade`
   - Username: `veritytr_dbuser`

---

## STEP 3: Clone from GitHub

**If your domain currently uses `public_html`:** Rename it first (File Manager → `/home/veritytr/` → rename `public_html` to `public_html_old`).

cPanel Git Version Control does **not** allow credentials in the URL.  
**No Terminal?** Use METHOD B or C below – you can still update easily.

---

**METHOD A – Clone via Terminal** *(skip if no Terminal)*

1. **cPanel → Terminal**
2. Run:

```bash
cd ~
git config --global credential.helper store
git clone https://github.com/veritytrade/veritytrade.git veritytrade
```

3. When prompted:
   - **Username:** your GitHub username (e.g. `veritytrade`)
   - **Password:** your Personal Access Token (not your GitHub password)

4. Credentials are saved for future `git pull`. Continue with Step 4 below.

---

**METHOD B – Make repo public** *(works without Terminal)*

1. GitHub → repo → **Settings** → **Danger Zone** → **Change visibility** → **Public**
2. **cPanel Git Version Control** → Clone: `https://github.com/veritytrade/veritytrade.git` to `/home/veritytr/veritytrade`
3. After clone succeeds: GitHub → Settings → Change visibility → **Private** again
4. **Future updates:** cPanel Git → **Update from Remote** → **Deploy HEAD Commit**
5. **First-time only:** Create `.env` (Step 5), then visit `https://yourdomain.com/setup.php?token=veritytrade-setup-2024` – then **delete** `public/setup.php`
6. Continue with Step 4 below.

---

**METHOD C – Zip upload (no Git on server)**

1. On your PC: go to https://github.com/veritytrade/veritytrade → **Code** → **Download ZIP**
2. **cPanel File Manager** → Create folder `/home/veritytr/veritytrade`
3. Upload the zip → Extract
4. For future updates: download new zip, upload, extract (overwrite). No `git pull`.
5. Continue with Step 4 below.

---

## STEP 4: Set Document Root

1. **cPanel → Domains**
2. Click your domain (e.g. veritytrade.ng)
3. Find **Document Root**
4. Change to: `/home/veritytr/veritytrade/public`
5. Save

---


## STEP 5: Create .env File

1. **cPanel → File Manager**
2. Go to `/home/veritytr/veritytrade/`
3. Find `.env.production.example`
4. Right-click → **Copy**
5. Paste in same folder
6. Right-click the copy → **Rename** → `.env`
7. Right-click `.env` → **Edit**
8. Replace placeholders:

```env
APP_NAME="VerityTrade"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=veritytr_veritytrade
DB_USERNAME=veritytr_dbuser
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

SUPER_ADMIN_EMAIL=admin@yourdomain.com
SUPER_ADMIN_PASSWORD=YourStrongPassword16CharsMinimum
SUPER_ADMIN_NAME="Super Admin"
SUPER_ADMIN_PHONE=+234XXXXXXXXXX
SUPER_ADMIN_ADDRESS="Head Office"
```

9. **Save** (Save Changes)

---

## STEP 6: Run Deployment Commands

**No Terminal?** Use one of these:

### Option A – Setup script (no Terminal)

1. Ensure Step 4 (Document Root) and Step 5 (.env) are done
2. Visit: `https://yourdomain.com/setup.php?token=veritytrade-setup-2024`
3. Wait until it finishes (key, storage link, migrate, seed, cache)
4. **Delete** `public/setup.php` (File Manager)

### Option B – Deploy + setup script

1. **cPanel Git** → **Deploy HEAD Commit** (runs composer, migrate, cache)
2. Visit `https://yourdomain.com/setup.php?token=veritytrade-setup-2024` for key + storage link + seed
3. Delete `public/setup.php`

### Option C – Terminal (if available)

```bash
cd ~/veritytrade
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan storage:link
php artisan migrate --force

php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**If `php` not found:** Try `/usr/local/bin/ea-php82` instead of `php`:
```bash
/usr/local/bin/ea-php82 artisan key:generate
```

**If `composer` not found:** Ask your host or use Softaculous/installer. Alternatively, run `composer install --no-dev` on your PC and upload the `vendor/` folder.

---

## STEP 7: Set Permissions

1. **cPanel → File Manager** → `/home/veritytr/veritytrade/`
2. Right-click `storage` → **Change Permissions** → 775, check **Recurse into subdirectories** → Change
3. Right-click `bootstrap/cache` → **Change Permissions** → 775 → Change

---

## STEP 8: Verify

1. Visit `https://yourdomain.com` – homepage should load
2. **Register** a test customer
3. **Login** as customer
4. Go to `/admin/login` – login with SUPER_ADMIN_EMAIL and SUPER_ADMIN_PASSWORD
5. Approve the test user, create shipment, assign order, generate invoice
6. Customer: download invoice PDF
7. Ensure images and styles load (public/build, storage link)

---

## FUTURE UPDATES

**With cPanel Git (no Terminal needed):**

1. On your PC: make changes → `git add .` → `git commit` → `git push`
2. **cPanel → Git Version Control** → your repo → **Manage**
3. Click **Update from Remote** (pulls latest from GitHub)
4. Click **Deploy HEAD Commit** (runs .cpanel.yml: composer, migrate, cache)

That’s it. No shell access required.

---

**If you have Terminal:**

```bash
cd ~/veritytrade
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

**If you changed CSS/JS:** Run `npm run build` locally, `git add -f public/build`, commit, push. Then Update from Remote + Deploy on cPanel.

> **Note:** `.cpanel.yml` in the repo tells cPanel what to run on Deploy. If your path is not `/home/veritytr/veritytrade`, edit `.cpanel.yml` (File Manager) and change `REPOPATH`.

---

## TROUBLESHOOTING

| Issue | Fix |
|-------|-----|
| 500 error | Check `storage/logs/laravel.log`; set storage & bootstrap/cache to 775 |
| No application encryption key | Run `php artisan key:generate` |
| Images/invoices don't load | Run `php artisan storage:link` |
| PDF generation fails | Enable **gd** in Select PHP Version |
| Repository not found (git) | Clear Windows Credential Manager GitHub entries; use correct account |
| db:seed fails in production | Set SUPER_ADMIN_PASSWORD in .env (min 16 chars) before seeding |
