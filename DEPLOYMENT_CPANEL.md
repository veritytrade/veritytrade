# VerityTrade – cPanel Deployment Guide

This guide is for deploying VerityTrade on **cPanel shared hosting**. Use with `DEPLOYMENT_STAGES.md` and `STAGE_4_CHECKLIST.md`.

---

## cPanel Folder Structure

**Recommended setup:**

1. Create a folder **outside** `public_html` for the Laravel project, e.g.:
   ```
   /home/your_username/veritytrade/
   ```
   (Replace `your_username` with your cPanel login.)

2. Upload all project files into `veritytrade/` (see Stage 3 for what to upload).

3. Point your domain’s document root to the `public` subfolder:
   - **cPanel:** Domains → Your Domain → Document Root
   - Set to: `/home/your_username/veritytrade/public`

**Why:** The web root must be `public/` so `.env` and sensitive files stay outside the web root.

> If your host doesn’t allow changing document root, you can put the project in `public_html/veritytrade` and access it at `https://yoursite.com/veritytrade`. Set `APP_URL` accordingly.

---

## Running Artisan Commands on cPanel

### Option 1: cPanel Terminal (recommended)

If your host provides **Terminal** in cPanel:

```bash
cd ~/veritytrade
php artisan key:generate
php artisan storage:link
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Use the PHP version you selected in MultiPHP Manager (e.g. 8.2).

### Option 2: No Terminal – upload `vendor/` and use cron/setup script

If there is no Terminal:

1. Run `composer install --no-dev` **locally** and upload the entire `vendor/` folder.
2. For `key:generate`, run it locally and copy the generated `APP_KEY=` value into `.env` on the server.
3. For `storage:link`, create the symlink via File Manager:
   - Go to `public/`
   - Create a symbolic link: `storage` → `../storage/app/public`
   (Or ask your host how to create symlinks.)
4. For migrations and seeding, you may need to ask your host if they offer a way to run PHP scripts (cron, one-time script, or support request).

---

## cPanel-Specific Checklist

| Step | Where in cPanel | Action |
|------|-----------------|--------|
| PHP version | **MultiPHP Manager** | Select PHP 8.2+ for your domain |
| PHP extensions | **Select PHP Version** → Extensions | Enable: gd, pdo_mysql, mbstring, openssl, etc. |
| Database | **MySQL® Databases** | Create database + user, Add User to Database (All) |
| Files | **File Manager** or SFTP | Upload to `veritytrade/` |
| Document root | **Domains** → Your domain → Document Root | Set to `veritytrade/public` |
| .env | File Manager | Create in `veritytrade/` (project root, not public/) |
| Run commands | **Terminal** (if available) | Run artisan commands |

---

## Database Note for cPanel

cPanel often prefixes database and username, e.g.:
- Database: `cpaneluser_veritytrade` (not just `veritytrade`)
- Username: `cpaneluser_dbuser`

Use the **full** names in `.env`:
```env
DB_DATABASE=cpaneluser_veritytrade
DB_USERNAME=cpaneluser_dbuser
DB_PASSWORD=your_password
```

---

## Troubleshooting (cPanel)

| Issue | Fix |
|-------|-----|
| 500 error | Check `storage/logs/laravel.log`; ensure `storage/` and `bootstrap/cache/` are writable (755 or 775) |
| "No application encryption key" | Run `php artisan key:generate` or add `APP_KEY=` manually to `.env` |
| Images/invoices don’t load | Run `php artisan storage:link` or create `public/storage` symlink to `../storage/app/public` |
| PDF/invoice fails | Enable **gd** extension in Select PHP Version |
| Document root can’t be changed | Put app in `public_html/veritytrade`, set `APP_URL=https://yoursite.com/veritytrade` |

---

## Deploy via GitHub (Easier Updates)

Use `UPLOAD_GUIDE.md` → **Method A: Deploy via GitHub** for:
- Initial: push to GitHub, clone on cPanel (Git Version Control or Terminal)
- Updates: `git pull` then run artisan commands (no re-upload)

---

## After Deployment

See **Stage 6** in `DEPLOYMENT_STAGES.md` for verification steps. Ensure:

- Homepage loads
- Admin login at `/admin/login` works
- Invoice PDF generation works (needs **gd**)
