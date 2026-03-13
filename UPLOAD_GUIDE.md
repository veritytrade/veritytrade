# VerityTrade – Step-by-Step Upload Guide (cPanel)

Your home path: `/home/veritytr`  
Backup: You are zipping `public_html` ✓

---

## Choose Your Method

| Method | Best for | Future updates |
|--------|----------|----------------|
| **GitHub** | Easier updates | `git pull` then run a few commands |
| **Zip upload** | One-time, no Git | Re-upload and replace files |

→ **Use GitHub** below for easier changes. Use **Zip upload** if GitHub isn't available.

---

# Method A: Deploy via GitHub (Recommended)

## A1. Push code to GitHub (one-time, from your PC)

1. Create a repository on GitHub (e.g. `veritytrade`). Use **private** for security.
2. On your PC, open PowerShell in `d:\APP\xampp\htdocs\veritytrade\`:

```powershell
# Build assets first (creates public/build/)
npm run build

git init
git add .
git add -f public/build
git commit -m "Production deployment"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/veritytrade.git
git push -u origin main
```

(Replace `YOUR_USERNAME` with your GitHub username. Use a Personal Access Token if prompted for password.)

3. `.env` is already in `.gitignore` – never push it. `vendor/` is excluded; the server will run `composer install`.

## A2. On cPanel – Clone from GitHub

1. **cPanel → Git™ Version Control** (or **Terminal** if no Git icon).

**If you have Git Version Control:**
- Create Repository → Clone from URL
- Repository URL: `https://github.com/YOUR_USERNAME/veritytrade.git`
- Clone to: `/home/veritytr/veritytrade`
- Deploy

**If using Terminal:**
```bash
cd ~
git clone https://github.com/YOUR_USERNAME/veritytrade.git veritytrade
cd veritytrade
```

## A3. Complete setup (same as zip method)

Follow **STEP 4** through **STEP 8** below (create .env, database, run artisan commands, permissions, test).

Then for **future updates**, you only need:

```bash
cd ~/veritytrade
git pull origin main
composer install --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**If you change CSS/JS:** Run `npm run build` locally, then `git add -f public/build` and push. The server will get the new build on `git pull`.

---

# Method B: Zip Upload

## Before You Start

**On your local PC**, ensure you have:
- [ ] Run `npm run build` (creates `public/build/`)
- [ ] Run `composer install --no-dev` (creates `vendor/`)

---

## B1. Prepare the zip on your PC

1. Go to: `d:\APP\xampp\htdocs\veritytrade\`
2. Select these folders and files (do NOT select `.env`, `node_modules`, `.git`):

```
✓ app
✓ bootstrap
✓ config
✓ database
✓ public
✓ resources
✓ routes
✓ storage
✓ vendor
✓ composer.json
✓ composer.lock
✓ package.json
✓ vite.config.js
✓ tailwind.config.js
✓ postcss.config.js
✓ .env.production.example
✓ artisan
```

3. Right-click → Send to → Compressed (zipped) folder
4. Name it: `veritytrade.zip`

**Do NOT include:**
- `.env` (your local env – never upload)
- `node_modules/` (not needed on server)
- `.git/` (optional, makes zip smaller if excluded)

---

## B2. Clear or rename public_html

In **cPanel File Manager**:

1. Go to `/home/veritytr/`
2. **Option A – Rename (safer):** Right-click `public_html` → Rename → `public_html_old`
3. **Option B – Delete contents:** Open `public_html` and delete everything inside (keep the folder)

---

## B3. Create new public_html structure for Laravel

**You need:** Laravel’s `public/` folder contents to be the web root.

**Option A – Document root change (best, keeps .env hidden):**

1. Create folder: `/home/veritytr/veritytrade`
2. Upload `veritytrade.zip` into `/home/veritytr/veritytrade/`
3. Extract the zip (right-click → Extract)
4. You should see: `app/`, `public/`, `vendor/`, etc. inside `veritytrade/`
5. Go to **cPanel → Domains → your domain**
6. Set **Document Root** to: `/home/veritytr/veritytrade/public`
7. Save

**Option B – No document root change (simpler, .env in public_html):**

1. Create new `public_html` if you renamed it: right-click in `/home/veritytr/` → New Folder → `public_html`
2. Upload `veritytrade.zip` into `public_html/`
3. Extract
4. **Move** the contents of `public_html/public/` up to `public_html/`:
   - Move: `public_html/public/*` → `public_html/`
   - Delete the now-empty `public/` folder
5. In `.env` set `APP_URL` to your domain (e.g. `https://veritytrade.ng`)

> **Recommend Option A** – document root to `veritytrade/public` keeps `.env` secure.

---

## STEP 4: Create .env on server (both methods)

1. In File Manager, go to `/home/veritytr/veritytrade/` (or `public_html/` if Option B)
2. Find `.env.production.example`
3. Right-click → Copy
4. Paste in same folder
5. Rename the copy to `.env`
6. Right-click `.env` → Edit
7. Fill in:

```
APP_NAME="VerityTrade"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_cpanel_db_name
DB_USERNAME=your_cpanel_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

SUPER_ADMIN_EMAIL=your-email@yourdomain.com
SUPER_ADMIN_PASSWORD=YourStrongPassword16CharsMin
SUPER_ADMIN_NAME="Your Name"
SUPER_ADMIN_PHONE=
SUPER_ADMIN_ADDRESS="Head Office"
```

8. Save (Changes / Save Changes)

---

## STEP 5: Create database (if new)

1. **cPanel → MySQL® Databases**
2. Create Database: e.g. `veritytr_veritytrade`
3. Create User: e.g. `veritytr_dbuser` with strong password
4. Add User to Database → All Privileges
5. Copy the **full** names into `.env` (cPanel adds prefix like `veritytr_`)

---

## STEP 6: Run deployment commands

1. **cPanel → Terminal**
2. Run (add `composer install --no-dev` if you used GitHub and didn't upload vendor):

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

**GitHub users:** `composer install` pulls dependencies. **Zip users:** Skip if you uploaded `vendor/`.

If you get "command not found" for `php`, try:
```bash
/usr/local/bin/ea-php82 artisan key:generate
```
(Or whatever PHP path your host uses – check with your host.)

---

## STEP 7: Set permissions

In File Manager:
- `storage/` → Right-click → Change Permissions → 775 (recursive)
- `bootstrap/cache/` → 775

---

## STEP 8: Test

1. Visit `https://yourdomain.com`
2. Test: Register, Login, Admin `/admin/login`, Create order, Generate invoice

---

## Summary: What to upload

| Include | Exclude |
|---------|---------|
| app/ | .env |
| bootstrap/ | node_modules/ |
| config/ | .git/ |
| database/ | |
| public/ | |
| resources/ | |
| routes/ | |
| storage/ | |
| vendor/ | |
| composer.json, composer.lock | |
| package.json, vite.config.js, tailwind.config.js, postcss.config.js | |
| .env.production.example | |
| artisan | |
