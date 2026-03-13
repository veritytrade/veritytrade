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
