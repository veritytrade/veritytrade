# Enable PHP GD Extension (Required for Invoice PDF)

DomPDF needs the **GD extension** to render images (logo, icons) in the invoice PDF.

## XAMPP on Windows

1. Open `php.ini` (in `D:\APP\xampp\php\php.ini` or via XAMPP Control Panel → Apache → Config → PHP (php.ini))
2. Find this line:
   ```
   ;extension=gd
   ```
3. Remove the semicolon to enable it:
   ```
   extension=gd
   ```
4. Restart Apache from XAMPP Control Panel

## Verify

Run in terminal: `php -m | findstr gd`
You should see `gd` in the output.

## Alternative (if GD fails)

If you cannot enable GD, use **JPEG format only** for logo and icons:
- Save as `logo.jpg` and `location.jpg`, `email.jpg`, `phone.jpg`
- JPEG images may work without GD in some DomPDF setups
