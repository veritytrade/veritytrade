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

## 6. Email not sending – first checks

If no emails are sent (verification, password reset, etc.):

1. **Check the log**  
   Open `storage/logs/laravel.log` (in your Laravel folder, e.g. `veritytrade/storage/logs/laravel.log`). Search for **"Failed to send"** – the line below will show the real error (e.g. `535 Incorrect authentication data`, `550 suspended`, `Connection refused`).

2. **Confirm .env is used**  
   The app only sends via SMTP when `MAIL_MAILER=smtp`. If `MAIL_MAILER=log` or it's missing, mail is written to the log file and not sent. Set `MAIL_MAILER=smtp` and all of: `MAIL_HOST`, `MAIL_PORT`, `MAIL_ENCRYPTION`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`.

3. **Reload config after .env changes**  
   After any change to `.env`, run:  
   `run_artisan.php?token=...&cmd=config:clear` then `config:cache`.  
   Otherwise the app keeps using the old mail settings.

4. **Match cPanel "Connect Devices"**  
   Use the same Outgoing server, Port, and Security (SSL/TLS) as in cPanel → Email Accounts → Connect Devices. Username = full email (e.g. `noreply@veritytrade.ng`), password = that account's password.

5. **Use the exact error from the log**  
   Once you see the message in `laravel.log` (e.g. 535, 550, Connection refused), use section 7 below to fix that specific error.

---

## 7. Troubleshooting: “Webmail works but app emails don’t send”

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

### E. “Reverse DNS (PTR) Problems Exist” / “Problems Exist (DMARC and Reverse DNS)”

cPanel → **Email Deliverability** may show that **Reverse DNS (PTR)** is not set for the server IP that sends your domain’s mail. Many hosts use a shared hostname (e.g. `standard12.doveserver.com`) and never set a PTR for your IP, so mail is more likely to be marked as spam or rejected.

**You cannot fix PTR yourself** – only the **hosting provider** (or the company that controls the IP) can add the PTR record at their DNS (e.g. pdns01.spinservers.com / pdns02.spinservers.com).

**What to send your host (support ticket):**

- “My domain veritytrade.ng sends mail from IP **147.124.219.219**. cPanel Email Deliverability reports: **Reverse DNS (PTR) Problems Exist** for this IP. Please add the suggested **PTR record** for this IP (the exact value is shown in cPanel → Email Deliverability → Reverse DNS). This will improve deliverability and reduce spam scoring.”
- If cPanel shows a **suggested PTR value** (e.g. `mail.veritytrade.ng` or a hostname), include that: “Suggested PTR value: [copy from cPanel].”

After they add the PTR, DMARC/Reverse DNS warnings in Email Deliverability may clear. You can still use **SPF** and **DKIM** in cPanel for your domain in the meantime.
