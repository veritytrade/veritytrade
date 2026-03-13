# VerityTrade Deployment – Stage-by-Stage Guide

Use this guide when deploying VerityTrade for the **first time**. Work through each stage before moving to the next. Each stage ends with verification steps.

---

## Overview of Stages

| Stage | Title | Purpose |
|-------|-------|---------|
| **1** | Pre-Deployment Reset & Stabilization | Clean local state, fix bugs, no dead links |
| **2** | Super Admin Security | Lock down super admin credentials & login |
| **3** | Production Build & Files | Build assets, prepare upload bundle |
| **4** | Server & Environment | Create DB, configure .env, set permissions |
| **5** | Application Deployment | Upload code, run migrations, seed data |
| **6** | Post-Deployment Checks | Verify site, admin, invoice, HTTPS |
| **7** | Ongoing Security | SSL, backups, updates |

---

## Stage 1: Pre-Deployment Reset & Stabilization

**Goal:** Ensure everything is in a clean, working state before deployment.

### 1.1 Reset database to initial state

This clears all test orders, invoices, and related data. Run only on your **local/test** environment:

```powershell
cd d:\APP\xampp\htdocs\veritytrade
php artisan migrate:fresh --seed
```

- Recreates all tables from migrations
- Runs seeders (roles, permissions, super admin, tracking stages, etc.)
- Invoice numbering will start from `VG-YYYYMM-0001` when first invoice is generated

### 1.2 Verify local app works

1. Visit `http://localhost/veritytrade` (or your local URL)
2. Register a test customer → verify email → login
3. Create an order (customer flow)
4. In admin: approve user, create shipment, assign order, generate invoice
5. Customer: download invoice PDF

### 1.3 Fixes already applied

- Order `invoice_id` is in `$fillable` so invoice–order link is saved correctly
- No dead links (removed references to deleted views)
- Admin login is rate-limited (5 attempts per minute)

---

## Stage 2: Super Admin Security

**Goal:** Ensure the super admin account cannot be compromised.

### 2.1 Set production credentials in `.env`

**Never** deploy with default values. On the server `.env`, set:

```env
SUPER_ADMIN_EMAIL=your-secure-email@yourdomain.com
SUPER_ADMIN_PASSWORD=YourVeryStrongP@ssw0rdHere
SUPER_ADMIN_NAME="Your Name"
SUPER_ADMIN_PHONE=+234XXXXXXXXXX
SUPER_ADMIN_ADDRESS="Secure Address"
```

Rules:
- Use a **unique email** not used elsewhere
- Password: **16+ characters**, mix of letters, numbers, symbols
- Avoid common words or simple patterns

### 2.2 Run seeder after setting env

```bash
php artisan db:seed --class=SuperAdminSeeder
```

This creates/updates the super admin user with the values from `.env`. Run this **after** migrations, and after any change to super admin credentials.

### 2.3 Security measures in place

- Super admin is created via seeder only (no public registration)
- Admin login: 5 attempts per minute (throttle)
- Only users with `super_admin`, `admin`, or `staff` can access admin panel
- Document root is `public/` so `.env` is not web-accessible

---

## Stage 3: Production Build & Files

**Goal:** Prepare files for upload to the server.

### 3.1 Build production assets

```powershell
npm run build
```

This creates optimized JS/CSS in `public/build/`. Ensure `public/build/manifest.json` exists.

### 3.2 Production Composer deps (optional)

If the server has Composer:

```powershell
composer install --no-dev --optimize-autoloader
```

If not, run this locally and upload the `vendor/` folder (slower upload).

### 3.3 What to upload / exclude

| Include | Exclude |
|---------|---------|
| `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/` | `.env` |
| `storage/` (empty structure) | `node_modules/` |
| `composer.json`, `composer.lock` | `vendor/` (if installing on server) |
| `package.json`, `vite.config.js`, `tailwind.config.js`, `postcss.config.js` | `.git/` (optional) |
| `public/build/` | `storage/logs/*` (keep folder, clear contents) |

---

## Stage 4: Server & Environment

**Goal:** Set up the server environment before deploying code.

### 4.1 Requirements

- PHP 8.2+ with: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`
- MySQL 5.7+ or MariaDB 10.3+
- Composer (if using Git/SSH deployment)
- Node.js (optional; can build locally)

### 4.2 Create database

1. Create MySQL database (e.g. `veritytrade_prod`)
2. Create user with full privileges on that database
3. Note: host, database name, username, password

### 4.3 Create `.env` on server

Copy `.env.production.example` to the server as `.env` (or copy from `.env.example` and adjust). Fill in:

```env
APP_NAME="VerityTrade"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=veritytrade_prod
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

# Super Admin – REQUIRED: set strong values before db:seed (see Stage 2)
SUPER_ADMIN_EMAIL=your-secure-email@yourdomain.com
SUPER_ADMIN_PASSWORD=YourStrongP@ssw0rdMin16Chars
SUPER_ADMIN_NAME="Your Name"
SUPER_ADMIN_PHONE=+234XXXXXXXXXX
SUPER_ADMIN_ADDRESS="Secure Address"
```

> **Super admin reminder:** In production, `db:seed` will fail if `SUPER_ADMIN_PASSWORD` is default or &lt; 16 chars. Set it in `.env` before seeding.

### 4.4 Document root

- **cPanel:** Domains → your domain → Document Root → set to `/home/username/veritytrade/public`. See `DEPLOYMENT_CPANEL.md`.
- **VPS:** Point to `/var/www/yourdomain.com/public`.

---

## Stage 5: Application Deployment

**Goal:** Deploy code and initialize the database.

### 5.1 Upload files (SFTP or Git)

- **cPanel/SFTP:** Upload to `/home/username/veritytrade/` (see Stage 3). Use File Manager or FileZilla.
- **Git:** If your cPanel has Git, clone into project folder, then run composer

### 5.2 Run deployment commands

Use **cPanel Terminal** (or SSH). Path example: `/home/username/veritytrade`.

```bash
cd /home/your_username/veritytrade

# If .env doesn't exist, copy from example
cp .env.example .env

# Generate app key
php artisan key:generate

# Install dependencies (if using Git)
composer install --no-dev --optimize-autoloader

# Storage link for uploads/invoices
php artisan storage:link

# Run migrations
php artisan migrate --force

# Seed (roles, super admin, tracking stages, etc.)
# ⚠️ Super admin: ensure SUPER_ADMIN_* set in .env first (Stage 2)
php artisan db:seed --force

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5.3 Permissions (cPanel)

Ensure `storage/` and `bootstrap/cache/` are writable (755 or 775). In File Manager, right‑click → Change Permissions. cPanel usually handles ownership automatically.

---

## Stage 6: Post-Deployment Verification

**Goal:** Confirm everything works in production.

### Checklist

- [ ] Homepage loads at `https://yourdomain.com`
- [ ] Registration works
- [ ] Login (customer) works
- [ ] Admin login at `/admin/login` works (super admin)
- [ ] Customer can create order
- [ ] Admin can approve user, create shipment, assign order
- [ ] Invoice generation works and PDF downloads
- [ ] Images (hero, deals) display via `public/storage`
- [ ] No 500 errors; `APP_DEBUG=false` is set

### If something fails

- Check `storage/logs/laravel.log`
- Ensure `storage/` and `bootstrap/cache/` are writable
- Verify `php artisan storage:link` was run
- For PDF errors: ensure PHP `gd` extension is enabled

---

## Stage 7: Ongoing Security & Maintenance

**Goal:** Keep the site secure and stable.

### Security

- [ ] HTTPS enabled (SSL certificate)
- [ ] `.env` not in web root (document root is `public/`)
- [ ] Strong super admin password
- [ ] Regular backups of database and `storage/` files

### Future updates

**With Git:**

```bash
git pull origin main
composer install --no-dev
npm run build
php artisan migrate --force
php artisan config:cache
```

**With SFTP:** Re-upload changed files, run migrations and cache commands via SSH/cPanel Terminal.

---

## Quick Reference: First Deploy

1. Reset locally: `php artisan migrate:fresh --seed`
2. Test locally (register, order, invoice)
3. Build: `npm run build` (and optionally `composer install --no-dev`)
4. Create DB and `.env` on server (**set SUPER_ADMIN_* before seeding**)
5. Upload code (or clone via Git)
6. Run: `key:generate`, `storage:link`, `migrate --force`, `db:seed --force`
7. Cache: `config:cache`, `route:cache`, `view:cache`
8. Verify all features
9. Enable HTTPS and schedule backups

---

## Related Docs

- `DEPLOYMENT_CPANEL.md` – cPanel-specific folder structure, Terminal, document root
- `DEPLOYMENT_PROCEDURES.md` – SFTP/Git flows, troubleshooting, security checklist
