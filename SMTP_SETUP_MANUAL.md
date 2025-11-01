# 📧 SMTP Email Setup - Manual PHPMailer Installation

## ✅ Current Status
- ✅ PHPMailer installed at: `/public_html/phpmailer/`
- ⏳ Email configuration needed
- ⏳ cPanel email account needed

---

## 🚀 Quick Setup (10 Minutes)

### Step 1: Create Email Account in cPanel (3 minutes)

1. **Login to cPanel**:
   - URL: https://kejalink.co.ke:2083
   - Username: `kejalink`
   - Password: (your cPanel password)

2. **Create Email Account**:
   - Go to **Email Accounts** section
   - Click **+ Create**
   - Fill in:
     ```
     Email: noreply
     Domain: kejalink.co.ke (should be auto-selected)
     Password: [Click "Generate" for strong password]
     Storage: 250 MB (default is fine)
     ```
   - Click **Create**
   - **IMPORTANT**: Copy the password! You'll need it in Step 2

3. **Get SMTP Settings**:
   - After creating, click **Connect Devices** next to the email
   - Note down (or keep this page open):
     ```
     SMTP Server: mail.kejalink.co.ke
     Port: 587 (TLS) or 465 (SSL)
     Username: noreply@kejalink.co.ke
     Password: [the one you just created]
     ```

---

### Step 2: Configure Email Settings (2 minutes)

1. **Upload the email config file**:
   - Upload `php-backend/email-config.php` to `/public_html/api/email-config.php`

2. **Edit the configuration**:
   ```bash
   # Via SSH or cPanel File Manager
   nano /home/kejalink/public_html/api/email-config.php
   ```

3. **Update these lines** (around line 13-16):
   ```php
   define('SMTP_HOST', 'mail.kejalink.co.ke'); // ✅ Already correct
   define('SMTP_PORT', 587); // ✅ Use 587 for TLS
   define('SMTP_ENCRYPTION', 'tls'); // ✅ Use 'tls'
   define('SMTP_USERNAME', 'noreply@kejalink.co.ke'); // ✅ Your email
   define('SMTP_PASSWORD', 'YOUR_EMAIL_PASSWORD_HERE'); // ⚠️ CHANGE THIS!
   ```

4. **Replace `YOUR_EMAIL_PASSWORD_HERE`** with the password from Step 1

5. **Save and exit**:
   - In nano: `Ctrl+O` (save), `Enter`, `Ctrl+X` (exit)
   - In File Manager: Click **Save Changes**

---

### Step 3: Update PHPMailer Paths (2 minutes)

The email-config.php file already has the correct paths for your setup:
```php
require_once __DIR__ . '/../../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../phpmailer/src/Exception.php';
```

This assumes:
- email-config.php is at: `/public_html/api/email-config.php`
- phpmailer is at: `/public_html/phpmailer/`

**Verify your paths**:
```bash
cd /home/kejalink/public_html
ls -la api/email-config.php  # Should exist
ls -la phpmailer/src/PHPMailer.php  # Should exist
```

If phpmailer is in a different location, update the paths in email-config.php.

---

### Step 4: Upload Updated Backend (2 minutes)

Upload the updated auth.php file:
- **Local file**: `php-backend/api/auth.php`
- **Upload to**: `/public_html/api/api/auth.php`

Via cPanel File Manager:
1. Navigate to `/public_html/api/api/`
2. Delete old `auth.php`
3. Upload new `auth.php`

---

### Step 5: Test Email Sending (5 minutes)

#### Test 1: Check File Paths
```bash
cd /home/kejalink/public_html
php -r "require_once 'api/email-config.php'; echo 'Config loaded successfully';"
```
**Expected**: "Config loaded successfully"

#### Test 2: Send Test Email

Create a test file: `/public_html/test-email.php`
```php
<?php
require_once __DIR__ . '/api/email-config.php';

$to = 'YOUR_EMAIL@gmail.com'; // Replace with your email
$name = 'Test User';
$resetLink = 'https://kejalink.co.ke/reset-password?token=test123';

$result = sendPasswordResetEmail($to, $name, $resetLink);

if ($result) {
    echo "✅ Email sent successfully! Check your inbox.\n";
} else {
    echo "❌ Email failed to send. Check error logs.\n";
}
?>
```

Run the test:
```bash
php /home/kejalink/public_html/test-email.php
```

Or visit: https://kejalink.co.ke/test-email.php

**Expected**: 
- Message: "✅ Email sent successfully!"
- Email arrives in your inbox within 1-2 minutes

#### Test 3: Check Error Logs (if email doesn't arrive)
```bash
tail -n 50 ~/domains/kejalink.co.ke/logs/kejalink.co.ke.error.log
```

---

## 🔧 Troubleshooting

### Issue: "Config loaded successfully" but email not sending

**Solution 1: Check SMTP credentials**
```bash
# Test SMTP connection
telnet mail.kejalink.co.ke 587
```
Should connect. If not, SMTP server might be blocked.

**Solution 2: Verify email account**
- Login to webmail: https://kejalink.co.ke:2096
- Use: noreply@kejalink.co.ke / your_password
- If login fails, recreate email account

### Issue: SSL Certificate Error

Add this to email-config.php (line 60):
```php
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
```

### Issue: Email goes to spam

1. **Add SPF Record** (in cPanel → Zone Editor):
   ```
   Type: TXT
   Name: @
   Value: v=spf1 a mx ip4:YOUR_SERVER_IP ~all
   ```

2. **Add DKIM** (cPanel → Email Deliverability):
   - Click "Manage" next to kejalink.co.ke
   - Install suggested DNS records

### Issue: Port 587 blocked

Try port 465 with SSL:
```php
define('SMTP_PORT', 465);
define('SMTP_ENCRYPTION', 'ssl');
```

---

## 📋 Quick Reference

### Email Configuration Summary
```
Location: /public_html/api/email-config.php
SMTP Host: mail.kejalink.co.ke
SMTP Port: 587 (TLS) or 465 (SSL)
Username: noreply@kejalink.co.ke
Password: [Your cPanel email password]
```

### PHPMailer Location
```
Installation: /public_html/phpmailer/
Required files:
  - phpmailer/src/PHPMailer.php
  - phpmailer/src/SMTP.php
  - phpmailer/src/Exception.php
```

### Files to Upload
```
✅ php-backend/email-config.php → /public_html/api/email-config.php
✅ php-backend/api/auth.php → /public_html/api/api/auth.php
```

---

## ✅ Verification Checklist

Before going live:
- [ ] Email account created: noreply@kejalink.co.ke
- [ ] email-config.php uploaded and configured
- [ ] SMTP password updated in email-config.php
- [ ] auth.php uploaded (with email-config.php require)
- [ ] Test email sent successfully
- [ ] Email received (check inbox and spam)
- [ ] Password reset tested end-to-end
- [ ] Test email file deleted (security)

---

## 🔐 Security Notes

1. **Protect email-config.php**:
   ```bash
   chmod 600 /home/kejalink/public_html/api/email-config.php
   ```

2. **Don't commit credentials to Git**:
   - email-config.php is already in .gitignore ✅
   - Never push passwords to repository

3. **Delete test file**:
   ```bash
   rm /home/kejalink/public_html/test-email.php
   ```

4. **Monitor email logs**:
   ```bash
   tail -f ~/domains/kejalink.co.ke/logs/kejalink.co.ke.error.log
   ```

---

## 🎯 Next Steps

1. ✅ Complete this setup guide
2. Test forgot password flow: https://kejalink.co.ke/forgot-password
3. Monitor first few password reset emails
4. Consider adding welcome emails (function already in email-config.php)
5. Set up email delivery monitoring

---

## 📞 Need Help?

**Common Commands**:
```bash
# Test PHP config
php -i | grep mail

# Check email queue
mailq

# View email logs
tail -f ~/domains/kejalink.co.ke/logs/kejalink.co.ke.error.log
```

**cPanel Email Tools**:
- Email Deliverability: Check DNS records
- Email Routing: Ensure "Local Mail Exchanger" is selected
- Track Delivery: View sent emails

---

**Last Updated**: November 1, 2025  
**Status**: Ready for deployment with manual PHPMailer installation
