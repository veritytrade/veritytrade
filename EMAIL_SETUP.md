# Email Setup for VerityTrade

The app uses **Authenticated SMTP** (not PHP `mail()`), so you must use the same settings as cPanel’s “Mail Client Manual Settings”.

## 1. Create email in cPanel

1. **cPanel → Email Accounts**
2. **Create** → New email: `noreply@veritytrade.ng` (or your domain)
3. Set a strong password
4. Save

## 2. Match “Connect Devices” in .env

Use the **exact** values from **cPanel → Email Accounts → Connect Devices** (Secure SSL/TLS).

**For port 465 (SSL) – recommended by your host:**

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.veritytrade.ng
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=noreply@veritytrade.ng
MAIL_PASSWORD=the_email_account_password
MAIL_FROM_ADDRESS=noreply@veritytrade.ng
MAIL_FROM_NAME="VerityTrade"
```

**If your host gives port 587 (TLS) instead:**

```env
MAIL_PORT=587
MAIL_ENCRYPTION=starttls
```

**Do not use port 25** – it is often blocked. Use **465 (SSL)** or **587 (TLS)** only.

After editing `.env`, run:  
`run_artisan.php?token=veritytrade-setup-2024&cmd=config:cache`

## 3. Email Routing (cPanel)

If the mailbox is on the same server, **Email Routing** must not send that domain to a remote server:

1. **cPanel → Email Deliverability** or **Email Routing**
2. For **veritytrade.ng** (or your domain), set to **Local** (or “Automatically detect”) so mail for `noreply@veritytrade.ng` is delivered locally. If it’s set to **Remote** but the mailbox is local, the server can fail to send.

## 4. Update feature flags (optional)

The `mail_from_address` and `mail_from_name` in feature flags override .env. To use .env defaults, either:
- Don’t change them in Admin → Feature Flags, or
- Set them to match your .env values

## 5. Test

1. Register a new user
2. Check inbox (and spam) for the 6-digit verification code
3. Verify and log in

---

## 6. Troubleshooting: “Webmail works but app emails don’t send”

**Symptom:** You can send from Webmail, but verification emails from the site never arrive (or you see errors in `storage/logs/laravel.log`).

### A. “550 Outgoing mail from … has been suspended”

The host has **suspended outgoing SMTP** for that address (often after bounces or abuse checks).

- **Fix:** Open a support ticket: ask them to **unsuspend outgoing mail** for `noreply@veritytrade.ng` (or your sending address).
- Until they do, the app cannot send mail from that address, even if Webmail still works (Webmail may use a different path).

### B. “535 Incorrect authentication data”

- **MAIL_USERNAME** must be the **full address**: `noreply@veritytrade.ng`
- **MAIL_PASSWORD** must be the **exact** password for that email account (copy/paste from cPanel → Email Accounts; no extra spaces).
- After changing `.env`, run:  
  `run_artisan.php?token=veritytrade-setup-2024&cmd=config:cache`

### C. Use the same settings as “Connect Devices”

Copy **Outgoing Server**, **Port**, and **Security** from cPanel → **Connect Devices** into `.env` (see section 2 above). Then run `config:cache`.

### D. Deliverability (SPF / DKIM)

If mail “sends” but goes to spam or is rejected by Gmail/Outlook, set up **SPF** and **DKIM** for your domain (cPanel → **Email Deliverability** or **Authentication**). That authorizes your server to send for `@veritytrade.ng`.
