# Stage 4: Server & Environment – Checklist

Use this checklist as you set up your server. Check each item before moving to Stage 5.

---

## 4.1 Verify Server Requirements

| Requirement | Your host | ✓ |
|-------------|-----------|---|
| PHP 8.2+ | | |
| MySQL 5.7+ or MariaDB 10.3+ | | |
| Extensions: bcmath, ctype, curl, dom, fileinfo, **gd**, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml | | |
| Composer (if using Git deploy) | | |
| SSH or SFTP access | | |

**Tip:** On cPanel, check PHP version in MultiPHP Manager. Extensions: **gd** is needed for invoice PDFs.

---

## 4.2 Create Database

**cPanel:** MySQL Databases → Create Database → Create User → Add User to Database (All Privileges)

| Item | Your value |
|------|------------|
| Database name | ________________ |
| Database user | ________________ |
| Database password | ________________ |
| Host (usually) | 127.0.0.1 or localhost |

---

## 4.3 Create .env on Server

1. Copy `.env.production.example` to your server as `.env` (or create from scratch).
2. Fill in all values.
3. Run `php artisan key:generate` on server to set `APP_KEY`.
4. **Super admin:** Set `SUPER_ADMIN_EMAIL` and `SUPER_ADMIN_PASSWORD` (16+ chars) before running `db:seed`.

| Variable | Your value |
|----------|------------|
| APP_URL | https://________________ |
| DB_DATABASE | ________________ |
| DB_USERNAME | ________________ |
| DB_PASSWORD | ________________ |
| SUPER_ADMIN_EMAIL | ________________ |
| SUPER_ADMIN_PASSWORD | ________________ |

---

## 4.4 Document Root (cPanel)

1. Upload project to `/home/your_username/veritytrade/` (outside `public_html`).
2. **Domains** → your domain → **Document Root** → set to:
   ```
   /home/your_username/veritytrade/public
   ```

**Critical:** The web root must be the `public/` folder so `.env` is not web-accessible.

See `DEPLOYMENT_CPANEL.md` for full cPanel guide.

---

## Done?

When all items are checked, proceed to **Stage 5: Application Deployment**.
