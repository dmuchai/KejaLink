## 📧 SMTP Email Configuration Guide

This guide shows how to set up professional email delivery for password reset emails using SMTP instead of PHP's basic `mail()` function.

---

## Why Use SMTP?

❌ **PHP mail()** (Default):
- Often blocked by spam filters
- No authentication
- Limited deliverability
- May not work on all servers

✅ **SMTP** (Recommended):
- Better deliverability
- Professional authentication
- Delivery tracking
- Works reliably across all servers

---

## Option 1: Gmail SMTP (Good for Testing)

### Step 1: Create App Password
1. Go to your Google Account: https://myaccount.google.com/
2. Click **Security** → **2-Step Verification** (enable if not already)
3. Click **App passwords**
4. Select **Mail** and **Other (Custom name)**
5. Name it "KejaLink Password Reset"
6. Click **Generate**
7. **Copy the 16-character password** (you'll need this)

### Step 2: Install PHPMailer
SSH into your server or use cPanel Terminal:

```bash
cd ~/public_html/api
composer require phpmailer/phpmailer
```

If composer isn't installed:
```bash
# Install composer first
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer

# Then install PHPMailer
composer require phpmailer/phpmailer
```

### Step 3: Update auth.php

Replace the `sendPasswordResetEmail()` function in `php-backend/api/auth.php`:

```php
/**
 * Send password reset email using Gmail SMTP
 */
function sendPasswordResetEmail($email, $name, $resetLink) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com';          // CHANGE THIS
        $mail->Password   = 'your-16-char-app-password';     // CHANGE THIS
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Email Settings
        $mail->setFrom('your-email@gmail.com', 'KejaLink');  // CHANGE THIS
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - KejaLink';
        
        // Email Body
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .button { 
                        display: inline-block; 
                        padding: 12px 24px; 
                        background-color: #10b981; 
                        color: white !important; 
                        text-decoration: none; 
                        border-radius: 5px; 
                        margin: 20px 0; 
                    }
                    .footer { 
                        margin-top: 30px; 
                        padding-top: 20px; 
                        border-top: 1px solid #ddd; 
                        font-size: 12px; 
                        color: #666; 
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Password Reset Request</h2>
                    <p>Hi {$name},</p>
                    <p>We received a request to reset your password for your KejaLink account. Click the button below to reset your password:</p>
                    <a href='{$resetLink}' class='button'>Reset Password</a>
                    <p>Or copy and paste this link into your browser:</p>
                    <p>{$resetLink}</p>
                    <p><strong>This link will expire in 1 hour.</strong></p>
                    <p>If you didn't request a password reset, you can safely ignore this email.</p>
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

---

## Option 2: cPanel Email SMTP (Best for Production)

### Step 1: Create Email Account
1. Login to cPanel → **Email Accounts**
2. Create: `noreply@kejalink.co.ke`
3. Set strong password
4. Note the password

### Step 2: Get SMTP Settings
In cPanel → **Email Accounts** → Click on the email → **Connect Devices**
- **Server**: Usually `mail.kejalink.co.ke` or `kejalink.co.ke`
- **Port**: 587 (STARTTLS) or 465 (SSL)
- **Username**: `noreply@kejalink.co.ke`
- **Password**: (the one you set)

### Step 3: Update auth.php

```php
/**
 * Send password reset email using cPanel SMTP
 */
function sendPasswordResetEmail($email, $name, $resetLink) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'mail.kejalink.co.ke';           // Or kejalink.co.ke
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@kejalink.co.ke';        // Your cPanel email
        $mail->Password   = 'your-cpanel-email-password';    // CHANGE THIS
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;                              // Or 465 for SSL
        
        // For SSL instead of STARTTLS:
        // $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        // $mail->Port       = 465;
        
        // Email Settings
        $mail->setFrom('noreply@kejalink.co.ke', 'KejaLink');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - KejaLink';
        
        // Email Body (same as above)
        $mail->Body = "..."; // Use the HTML from above
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email send failed: {$mail->ErrorInfo}");
        return false;
    }
}
```

---

## Option 3: SendGrid (Professional/High Volume)

### Step 1: Create SendGrid Account
1. Sign up: https://sendgrid.com/
2. Free tier: 100 emails/day
3. Create API Key: Settings → API Keys → Create API Key
4. Copy the API key

### Step 2: Install SendGrid Library
```bash
cd ~/public_html/api
composer require sendgrid/sendgrid
```

### Step 3: Update auth.php

```php
/**
 * Send password reset email using SendGrid
 */
function sendPasswordResetEmail($email, $name, $resetLink) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $sendgrid = new \SendGrid('YOUR_SENDGRID_API_KEY'); // CHANGE THIS
    
    $emailContent = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .button { 
                    display: inline-block; 
                    padding: 12px 24px; 
                    background-color: #10b981; 
                    color: white !important; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 20px 0; 
                }
                .footer { 
                    margin-top: 30px; 
                    padding-top: 20px; 
                    border-top: 1px solid #ddd; 
                    font-size: 12px; 
                    color: #666; 
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Password Reset Request</h2>
                <p>Hi {$name},</p>
                <p>We received a request to reset your password for your KejaLink account.</p>
                <a href='{$resetLink}' class='button'>Reset Password</a>
                <p>Or copy and paste this link: {$resetLink}</p>
                <p><strong>This link will expire in 1 hour.</strong></p>
                <p>If you didn't request this, please ignore this email.</p>
                <div class='footer'>
                    <p>© " . date('Y') . " KejaLink. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    $emailObj = new \SendGrid\Mail\Mail();
    $emailObj->setFrom("noreply@kejalink.co.ke", "KejaLink");
    $emailObj->setSubject("Password Reset - KejaLink");
    $emailObj->addTo($email, $name);
    $emailObj->addContent("text/html", $emailContent);
    
    try {
        $response = $sendgrid->send($emailObj);
        return $response->statusCode() === 202;
    } catch (Exception $e) {
        error_log("SendGrid Error: " . $e->getMessage());
        return false;
    }
}
```

---

## 🔒 Security Best Practices

### Don't Hardcode Credentials!

Create `php-backend/email-config.php` (add to .gitignore):

```php
<?php
// Email Configuration - DO NOT COMMIT THIS FILE
define('SMTP_HOST', 'mail.kejalink.co.ke');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@kejalink.co.ke');
define('SMTP_PASSWORD', 'your-secure-password'); // CHANGE THIS
define('SMTP_FROM_EMAIL', 'noreply@kejalink.co.ke');
define('SMTP_FROM_NAME', 'KejaLink');
?>
```

Then update auth.php:

```php
require_once __DIR__ . '/../email-config.php';

function sendPasswordResetEmail($email, $name, $resetLink) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        // ... rest of code
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}
```

Add to `.gitignore`:
```
php-backend/email-config.php
```

---

## 🧪 Testing

### Test Script
Create `test-email.php` in your project root:

```php
<?php
require_once 'php-backend/config.php';
require_once 'php-backend/api/auth.php';

// Test email sending
$testEmail = 'your-email@example.com'; // CHANGE THIS
$testName = 'Test User';
$testLink = 'https://kejalink.co.ke/reset-password?token=test123';

echo "Sending test email to {$testEmail}...\n";

$result = sendPasswordResetEmail($testEmail, $testName, $testLink);

if ($result) {
    echo "✅ Email sent successfully!\n";
    echo "Check your inbox (and spam folder)\n";
} else {
    echo "❌ Email failed to send\n";
    echo "Check error logs for details\n";
}
?>
```

Run it:
```bash
php test-email.php
```

---

## 🐛 Troubleshooting

### Gmail SMTP Issues

**"Username and Password not accepted"**
- Make sure 2-Step Verification is enabled
- Use App Password, not your regular password
- Check for typos in credentials

**Connection timeout**
- Server might be blocking port 587
- Try port 465 with SSL instead
- Check firewall settings

### cPanel SMTP Issues

**"Could not connect to SMTP host"**
- Use `localhost` or `127.0.0.1` instead of domain name
- Check if mail service is running in cPanel
- Try different ports: 587, 465, or 25

**Authentication failed**
- Verify email account exists in cPanel
- Check password is correct
- Try recreating the email account

### SendGrid Issues

**"Unauthorized"**
- Check API key is correct
- Verify API key has mail send permissions
- Make sure sender email is verified

---

## 📊 Comparison

| Feature | PHP mail() | Gmail SMTP | cPanel SMTP | SendGrid |
|---------|-----------|------------|-------------|----------|
| **Setup Difficulty** | Easy | Medium | Medium | Medium |
| **Deliverability** | Poor | Good | Good | Excellent |
| **Daily Limit** | Server dependent | 500 | Unlimited* | 100 (free) |
| **Cost** | Free | Free | Included | Free tier |
| **Tracking** | No | No | No | Yes |
| **Best For** | Testing | Development | Production | High volume |

*Subject to hosting plan limits

---

## ✅ Recommended Setup

**For Development:**
- Use Gmail SMTP (easy setup, good for testing)

**For Production:**
- Use cPanel SMTP (reliable, no external dependencies)
- Or SendGrid for high volume + analytics

---

## 📝 Quick Setup (Copy-Paste Ready)

### cPanel SMTP (Recommended)

1. Create email in cPanel: `noreply@kejalink.co.ke`
2. Install PHPMailer:
   ```bash
   cd ~/public_html/api && composer require phpmailer/phpmailer
   ```
3. Replace `sendPasswordResetEmail()` in `php-backend/api/auth.php` with the cPanel version above
4. Update credentials in the function
5. Test with `test-email.php`

**Total time: ~10 minutes** ⏱️

---

**Last Updated**: November 1, 2025  
**Author**: Dennis Muchai
