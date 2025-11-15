# 🚀 SMTP Email Setup - Quick Start (5 Minutes)

Follow these steps to set up professional email delivery for password reset emails.

---

## ⚡ Quick Setup (Recommended: cPanel Email)

### Step 1: Create Email Account (2 minutes)

1. Login to cPanel: https://kejalink.co.ke:2083
2. Go to **Email Accounts**
3. Click **+ Create**
4. Enter:
   - Email: `noreply`
   - Domain: `kejalink.co.ke`
   - Password: (generate strong password)
   - Quota: 250 MB (default is fine)
5. Click **Create**
6. **Copy the password** - you'll need it

### Step 2: Install PHPMailer (1 minute)

Via cPanel Terminal or SSH:

```bash
cd ~/public_html/api
composer require phpmailer/phpmailer
```

If composer not found:
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
mv composer.phar composer
./composer require phpmailer/phpmailer
```

### Step 3: Configure Email Settings (1 minute)

1. Copy the example config:
```bash
cp php-backend/email-config.example.php php-backend/email-config.php
```

2. Edit the config:
```bash
nano php-backend/email-config.php
```

3. Update these lines:
```php
define('SMTP_USERNAME', 'noreply@kejalink.co.ke');
define('SMTP_PASSWORD', 'YOUR_PASSWORD_FROM_STEP_1'); // Paste password here
```

4. Save and exit (Ctrl+X, then Y, then Enter)

### Step 4: Update auth.php (1 minute)

Replace the `sendPasswordResetEmail()` function in `php-backend/api/auth.php`:

```php
/**
 * Send password reset email using SMTP
 */
function sendPasswordResetEmail($email, $name, $resetLink) {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../email-config.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl' 
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS 
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Email Settings
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - KejaLink';
        
        // Email Body
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
                    .content { background: white; padding: 30px; border-radius: 8px; }
                    .button { 
                        display: inline-block; 
                        padding: 12px 24px; 
                        background-color: #10b981; 
                        color: white !important; 
                        text-decoration: none; 
                        border-radius: 5px; 
                        margin: 20px 0; 
                        font-weight: bold;
                    }
                    .footer { 
                        margin-top: 30px; 
                        padding-top: 20px; 
                        border-top: 1px solid #ddd; 
                        font-size: 12px; 
                        color: #666; 
                        text-align: center;
                    }
                    .warning {
                        background: #fef3c7;
                        border-left: 4px solid #f59e0b;
                        padding: 10px 15px;
                        margin: 15px 0;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='content'>
                        <h2>🔐 Password Reset Request</h2>
                        <p>Hi <strong>{$name}</strong>,</p>
                        <p>We received a request to reset your password for your KejaLink account.</p>
                        <p style='text-align: center;'>
                            <a href='{$resetLink}' class='button'>Reset Password</a>
                        </p>
                        <div class='warning'>
                            <strong>⏰ This link will expire in 1 hour.</strong>
                        </div>
                        <p>Or copy and paste this link:</p>
                        <p style='word-break: break-all; background: #f3f4f6; padding: 10px;'>{$resetLink}</p>
                        <p style='color: #666;'>If you didn't request this, please ignore this email.</p>
                    </div>
                    <div class='footer'>
                        <p>© " . date('Y') . " KejaLink. All rights reserved.</p>
                        <p>This is an automated email. Please do not reply.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email send failed: {$mail->ErrorInfo}");
        return false;
    }
}
```

### Step 5: Test It! (30 seconds)

```bash
php test-email.php your-email@example.com
```

You should see: ✅ Email sent successfully!

Check your inbox (and spam folder).

---

## 📋 Quick Checklist

- [ ] Created `noreply@kejalink.co.ke` email in cPanel
- [ ] Installed PHPMailer (`composer require phpmailer/phpmailer`)
- [ ] Created `email-config.php` from example
- [ ] Updated password in `email-config.php`
- [ ] Updated `sendPasswordResetEmail()` function in `auth.php`
- [ ] Tested with `test-email.php`
- [ ] Received test email successfully
- [ ] Added `email-config.php` to `.gitignore` ✅ (already done)

---

## 🐛 Troubleshooting

### "Could not connect to SMTP host"

Try changing `SMTP_HOST` in `email-config.php`:

```php
// Try these in order:
define('SMTP_HOST', 'localhost');           // Option 1
define('SMTP_HOST', '127.0.0.1');          // Option 2
define('SMTP_HOST', 'mail.kejalink.co.ke'); // Option 3
```

### "Authentication failed"

1. Double-check password in `email-config.php`
2. Make sure email exists in cPanel → Email Accounts
3. Try recreating the email account

### "Connection timeout"

Try different port in `email-config.php`:

```php
// Option 1: STARTTLS (Port 587)
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');

// Option 2: SSL (Port 465)
define('SMTP_PORT', 465);
define('SMTP_ENCRYPTION', 'ssl');

// Option 3: No encryption (Port 25) - Not recommended
define('SMTP_PORT', 25);
define('SMTP_ENCRYPTION', '');
```

### Email goes to spam

1. Check SPF/DKIM in cPanel → Email Deliverability
2. Click "Repair" if any issues found
3. Add to email HTML:
   ```html
   <!-- Add to email body -->
   <p>Please add noreply@kejalink.co.ke to your contacts.</p>
   ```

---

## 🎯 Alternative: Gmail SMTP (For Testing)

If cPanel email doesn't work, try Gmail:

1. **Get Gmail App Password**:
   - Go to https://myaccount.google.com/security
   - Enable 2-Step Verification
   - Go to App Passwords
   - Generate password for "Mail"
   - Copy the 16-character password

2. **Update `email-config.php`**:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-16-char-app-password');
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'KejaLink');
```

---

## ✅ Done!

Once test email works:
1. Upload updated `auth.php` to production
2. Upload `email-config.php` to production (via cPanel, NOT git)
3. Upload `vendor/` folder (PHPMailer dependencies)
4. Test forgot password flow on live site

---

**Need more help?** See full guide: `SMTP_SETUP_GUIDE.md`

**Total Time**: ~5 minutes ⏱️
