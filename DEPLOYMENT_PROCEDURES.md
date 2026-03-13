# VerityTrade Deployment Procedures

Secure procedures to deploy the local Laravel website to your online server.

---

## Deploy via GitHub (Recommended)

1. **Initial setup (once):** Push code to GitHub, clone on server, run `composer install`, `npm run build`, migrations.
2. **Future updates:** `git pull` on server → `composer install --no-dev` → `npm run build` → `php artisan migrate --force` → `php artisan config:cache`.
3. See **Flow B: SSH + Git** below for full steps.

---

## Deployment Stages

For first-time deployment, follow **DEPLOYMENT_STAGES.md** step by step. It covers:
- Pre-deploy reset and stabilization
- Super admin security
- Build, server setup, deployment, verification

---

## Prerequisites

### What You Need Before Starting
- [ ] Online hosting with PHP 8.2+ (shared hosting or VPS)
- [ ] MySQL or MariaDB database (most hosts provide this)
- [ ] SSH or SFTP access to the server
- [ ] Domain pointing to your server (e.g., veritytrade.ng)

### Server Requirements
- PHP 8.2+ with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`
- MySQL 5.7+ or MariaDB 10.3+
- Composer (if deploying via SSH)
- Node.js & npm (only if building assets locally first)

---

## Method Comparison

| Method | Security | Ease | Best For |
|--------|----------|------|----------|
| **SFTP** | Good | Easy | Shared hosting, no SSH |
| **SSH + Git** | Best | Medium | VPS, full control |
| **FTP** | Avoid | Easy | Legacy; not recommended |

---

## Recommended: Secure Deployment (Choose One Flow)

---

## Flow A: SFTP Upload (Shared Hosting / cPanel)

**Use when:** You have cPanel with FTP/SFTP (File Manager, FileZilla, WinSCP). See `DEPLOYMENT_CPANEL.md` for cPanel-specific setup (folder structure, document root, Terminal).

### Step 1: Prepare Files Locally

```powershell
# In project folder (d:\APP\xampp\htdocs\veritytrade)

# 1. Exclude sensitive and dev files from upload
#    Create a list of what to EXCLUDE:
#    - .env (never upload – create on server)
#    - node_modules/
#    - vendor/ (reinstall on server if you have SSH, or upload)
#    - storage/logs/* (keep folder, empty it)
#    - database/*.sqlite (never upload – use MySQL on server)
#    - .git/ (optional, exclude for smaller upload)

# 2. Run production build
npm run build

# 3. (Optional) Install production-only Composer deps
composer install --no-dev --optimize-autoloader
```

### Step 2: What to Upload

| Upload | Skip |
|--------|------|
| `app/` | `node_modules/` |
| `bootstrap/` | `vendor/` *(if server has Composer)* |
| `config/` | `.env` |
| `database/` (migrations, seeders) | `database/*.sqlite` |
| `public/` | `.git/` (optional) |
| `resources/` | `storage/logs/*` (keep folder) |
| `routes/` | |
| `storage/` (empty folders: app, framework, logs) | |
| `composer.json` | |
| `composer.lock` | |
| `package.json` | |
| `vite.config.js` | |
| `tailwind.config.js` | |
| `postcss.config.js` | |
| `public/build/` *(from npm run build)* | |

### Step 3: Create `.env` on Server

**Never copy your local `.env`.** Create a new one on the server.

**Super admin reminder:** Set `SUPER_ADMIN_EMAIL` and `SUPER_ADMIN_PASSWORD` (min 16 chars) before running `db:seed`, or seeding will fail in production.

```env
APP_NAME="VerityTrade"
APP_ENV=production
APP_KEY=                    # Generate with: php artisan key:generate
APP_DEBUG=false             # Must be false in production
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

# Super Admin (set strong values; never use defaults in production)
SUPER_ADMIN_EMAIL=your-secure-email@yourdomain.com
SUPER_ADMIN_PASSWORD=YourStrongP@ssw0rd
SUPER_ADMIN_NAME="Super Admin"
SUPER_ADMIN_PHONE=+234XXXXXXXXXX
SUPER_ADMIN_ADDRESS="Head Office"
```

### Step 4: Server-Side Commands (if you have SSH)

```bash
cd /path/to/your/site

# Install Composer deps (if not uploaded)
composer install --no-dev --optimize-autoloader

# Generate app key
php artisan key:generate

# Storage link
php artisan storage:link

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 5: If No SSH (cPanel / File Manager)

1. Use cPanel "Terminal" if available, or upload `vendor/` after running `composer install` locally with `--no-dev`.
2. Create `storage/app/public` folders manually.
3. Set permissions: `storage/` and `bootstrap/cache/` → 775 (or 755 if 775 fails).
4. Create MySQL database and user in cPanel → add credentials to `.env`.

### Step 6: Document Root

Point the domain’s document root to the `public/` folder:

- Good: `public/` is the web root
- Bad: project root (contains `.env`, exposes config)

**cPanel:** Upload to `/home/your_username/veritytrade/`, then Domains → Document Root → `/home/your_username/veritytrade/public`. See `DEPLOYMENT_CPANEL.md`.

**Subdirectory deploy:** If URL is `https://yoursite.com/veritytrade`, set `APP_URL` and optionally `ASSET_URL` in `.env`.

---

## Flow B: SSH + Git (VPS / Recommended for Security)

**Use when:** You have SSH access and can install Git on the server.

### Step 1: Initialize Git Locally (if not done)

```powershell
cd d:\APP\xampp\htdocs\veritytrade
git init
```

Create `.gitignore` (Laravel usually has this):

```
/node_modules
/public/build
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.env.production
*.sqlite
```

### Step 2: Push to a Git Host

```powershell
git add .
git commit -m "Production-ready deployment"
git remote add origin https://github.com/YOUR_USERNAME/veritytrade.git
git push -u origin main
```

Use a **private** repository for security.

### Step 3: On the Server

```bash
cd /var/www   # or your web root parent
git clone https://github.com/YOUR_USERNAME/veritytrade.git yourdomain.com
cd yourdomain.com
```

### Step 4: On Server – Setup

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Create `.env` with production values (database, APP_URL, APP_DEBUG=false).

```bash
php artisan storage:link
php artisan migrate --force
npm run build   # if Node.js is installed, else build locally and upload public/build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 5: Web Server Config

Point Nginx/Apache document root to:

```
/var/www/yourdomain.com/public
```

---

## Security Checklist

### Super Admin (Critical)
- [ ] `SUPER_ADMIN_EMAIL` and `SUPER_ADMIN_PASSWORD` set to strong, unique values (never defaults)
- [ ] Run `php artisan db:seed --class=SuperAdminSeeder` after setting env
- [ ] Super admin created only via seeder; no public registration path

### Before Go-Live

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY` (from `php artisan key:generate`)
- [ ] `.env` not in public folder or web root
- [ ] Document root is `public/`, not project root
- [ ] HTTPS enabled (SSL certificate)
- [ ] Database credentials are strong and unique

### File Permissions

```bash
# Storage and cache writable by web server
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache   # Linux; adjust user
```

### Block Sensitive Paths (Apache `.htaccess` in project root, if needed)

```apache
# Deny access to sensitive folders
<FilesMatch "\.env$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## Post-Deployment Verification

1. **Homepage** – Loads correctly
2. **Login/Register** – Works
3. **Admin** – `/admin/login` works
4. **File uploads** – Upload image (e.g. hero, deal) and confirm it displays
5. **Invoice generation** – Generate an invoice and download PDF
6. **Storage link** – `public/storage` symlink exists; images/invoices load

---

## Quick Reference: First-Time Deploy

```
1. Prepare: npm run build, composer install --no-dev
2. Upload (or git pull) all files except .env, node_modules, vendor (if using composer on server)
3. Create .env on server with production values (include SUPER_ADMIN_* before seeding)
4. php artisan key:generate
5. php artisan storage:link
6. php artisan migrate --force
7. php artisan config:cache
8. Set document root to public/
9. Enable HTTPS
```

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 500 error | Check `storage/logs/laravel.log`; ensure `storage/` and `bootstrap/cache/` writable |
| Images not loading | Run `php artisan storage:link`; ensure `public/storage` exists |
| Blank page | Set `APP_DEBUG=true` temporarily to see error (then set back to false) |
| "No application encryption key" | Run `php artisan key:generate` |
| PDF/invoice fails | Ensure PHP `gd` extension is enabled on server |

---

## Future Updates (After First Deploy)

**With Git:**
```bash
git pull origin main
composer install --no-dev
npm run build
php artisan migrate --force
php artisan config:cache
```

**With SFTP:**  
Re-upload changed files; run migrations and cache commands via SSH or cPanel Terminal if available.
