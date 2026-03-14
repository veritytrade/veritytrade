# Email Setup for VerityTrade

Email verification and other system emails use SMTP. On cPanel, use the built-in email.

## 1. Create email in cPanel

1. **cPanel → Email Accounts**
2. **Create** → New email: `noreply@veritytrade.ng` (or your domain)
3. Set a strong password
4. Save

## 2. Add to .env on server

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.veritytrade.ng
MAIL_PORT=587
MAIL_ENCRYPTION=starttls
MAIL_USERNAME=noreply@veritytrade.ng
MAIL_PASSWORD=your_email_password
MAIL_FROM_ADDRESS=noreply@veritytrade.ng
MAIL_FROM_NAME="VerityTrade"
```

**Note:** If `mail.veritytrade.ng` fails, try:
- `localhost`
- Your server hostname (shown in cPanel)
- Or check cPanel Email → **Connect Devices** for the correct server

## 3. Update feature flags (optional)

The `mail_from_address` and `mail_from_name` in feature flags override .env. To use .env defaults, either:
- Don’t change them in Admin → Feature Flags, or
- Set them to match your .env values

## 4. Test

1. Register a new user
2. Check inbox (and spam) for the 6-digit verification code
3. Verify and log in

---

## 5. Troubleshooting: “Webmail works but app emails don’t send”

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

In cPanel → **Email Accounts** → **Connect Devices** (or **Set Up Mail Client**), note:

- **Outgoing server**, **Port** (465 or 587), **Security** (SSL/TLS).
- Set in `.env`:
  - **Port 465:** `MAIL_PORT=465`, `MAIL_ENCRYPTION=null`, and in many setups `MAIL_SCHEME=smtps`
  - **Port 587:** `MAIL_PORT=587`, `MAIL_ENCRYPTION=starttls`
- Then run `config:cache` again.
