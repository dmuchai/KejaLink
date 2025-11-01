📧 **SMTP Email Setup - Quick Test Guide**

## Test on Your Server

You've installed PHPMailer manually in `/public_html/phpmailer/`.

### Step 1: Upload email-config.php

1. **Copy the file** from your local repo:
   ```
   /home/dennis-muchai/rentify-houses-kenya/php-backend/api/email-config.php
   ```

2. **Upload to server**:
   ```
   /home/kejalink/domains/kejalink.co.ke/public_html/api/email-config.php
   ```

3. **Edit credentials** (via SSH or cPanel File Editor):
   ```php
   define('SMTP_HOST', 'mail.kejalink.co.ke');
   define('SMTP_USERNAME', 'noreply@kejalink.co.ke');
   define('SMTP_PASSWORD', 'YOUR_ACTUAL_PASSWORD'); // ← Change this!
   ```

### Step 2: Test the Configuration

Run this on your server (via SSH):

```bash
cd /home/kejalink/domains/kejalink.co.ke/public_html
php -r "require_once 'api/email-config.php'; echo 'Config loaded successfully\n';"
```

**Expected output:**
```
Config loaded successfully
```

**If you see errors**, check:
- PHPMailer path is correct
- Files exist: `ls phpmailer/src/PHPMailer.php`

### Step 3: Test Email Sending

Create a test file: `/home/kejalink/domains/kejalink.co.ke/public_html/test-email.php`

```php
<?php
require_once 'api/email-config.php';

$testEmail = 'your-email@example.com'; // ← Your email
$testName = 'Test User';
$testLink = 'https://kejalink.co.ke/reset-password?token=test123';

echo "Sending test email to {$testEmail}...\n";

if (sendPasswordResetEmail($testEmail, $testName, $testLink)) {
    echo "✅ Email sent successfully!\n";
    echo "Check your inbox (and spam folder)\n";
} else {
    echo "❌ Email failed to send\n";
    echo "Check error log for details\n";
}
?>
```

Run the test:
```bash
cd /home/kejalink/domains/kejalink.co.ke/public_html
php test-email.php
```

### Step 4: Check Errors (if needed)

If email fails, check logs:
```bash
tail -f ~/domains/kejalink.co.ke/logs/kejalink.co.ke.error.log
```

### Common Issues & Solutions

#### Issue 1: "Failed to open stream"
```
Solution: Check PHPMailer path in email-config.php
Current path: __DIR__ . '/../phpmailer/src/PHPMailer.php'
Verify: ls -la /home/kejalink/domains/kejalink.co.ke/public_html/phpmailer/src/
```

#### Issue 2: "SMTP connect() failed"
```
Solution: Check SMTP settings
- SMTP_HOST: mail.kejalink.co.ke (usually correct)
- SMTP_PORT: Try 587 (TLS) or 465 (SSL)
- SMTP_SECURE: Try 'tls' or 'ssl'
```

#### Issue 3: "Authentication failed"
```
Solution: Check email credentials in cPanel
1. Go to cPanel → Email Accounts
2. Verify noreply@kejalink.co.ke exists
3. Update password in email-config.php
```

#### Issue 4: Email goes to spam
```
Solution: Configure SPF/DKIM records in cPanel
1. cPanel → Email Deliverability
2. Click "Manage" next to kejalink.co.ke
3. Install recommended records
```

### Step 5: Enable in auth.php

Once email test works, update `auth.php` to use it:

```php
// In php-backend/api/auth.php
// Add at the top (after config.php):
require_once __DIR__ . '/email-config.php';

// The sendPasswordResetEmail() function is now available!
// It's already called in handleForgotPassword()
```

### Step 6: Test Full Flow

1. Go to: https://kejalink.co.ke/forgot-password
2. Enter your email
3. Click "Send Reset Link"
4. Check inbox (and spam)
5. Click link in email
6. Reset password
7. Try logging in

### Quick Command Reference

```bash
# Test config loads
php -r "require_once 'api/email-config.php'; echo 'OK\n';"

# Check PHPMailer exists
ls -la phpmailer/src/PHPMailer.php

# Test email sending
php test-email.php

# Watch logs
tail -f ~/domains/kejalink.co.ke/logs/kejalink.co.ke.error.log

# Check email accounts
# (Do this in cPanel → Email Accounts)
```

### Debugging Mode

To see detailed SMTP communication, edit `email-config.php`:

```php
define('EMAIL_DEBUG', 2); // Change from 0 to 2

// Levels:
// 0 = Off
// 1 = Client commands
// 2 = Client commands + server responses
// 3 = Connection info
// 4 = Low-level data
```

Then check error log for detailed output.

---

**Need Help?**

Common cPanel SMTP settings:
- Host: `mail.kejalink.co.ke` or `mail.yourdomain.com`
- Port: `465` (SSL) or `587` (TLS)
- Username: Full email address
- Password: Email account password from cPanel

Remember to set `EMAIL_DEBUG` back to 0 in production!
