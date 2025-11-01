<?php
/**
 * Email Configuration EXAMPLE for KejaLink
 * Copy this file to email-config.php and fill in your actual credentials
 * 
 * IMPORTANT: email-config.php is in .gitignore and should NOT be committed
 */

// ============================================
// EMAIL CONFIGURATION
// ============================================

// SMTP Server Settings
define('SMTP_HOST', 'mail.yourdomain.com');      // Your SMTP server
define('SMTP_PORT', 465);                         // 587 for TLS, 465 for SSL
define('SMTP_SECURE', 'ssl');                     // 'tls' or 'ssl'
define('SMTP_AUTH', true);                        // Enable SMTP authentication

// Email Account Credentials
define('SMTP_USERNAME', 'noreply@yourdomain.com'); // Your email address
define('SMTP_PASSWORD', 'YOUR_EMAIL_PASSWORD');    // Your email password - CHANGE THIS!

// Email From Details
define('EMAIL_FROM_ADDRESS', 'noreply@yourdomain.com');
define('EMAIL_FROM_NAME', 'KejaLink');

// Email Reply-To (optional)
define('EMAIL_REPLY_TO', 'support@yourdomain.com');
define('EMAIL_REPLY_NAME', 'KejaLink Support');

// Email Settings
define('EMAIL_CHARSET', 'UTF-8');
define('EMAIL_DEBUG', 0); // 0 = off, 1 = client, 2 = server, 3 = connection, 4 = lowlevel

// ============================================
// PHPMAILER AUTOLOAD
// ============================================

// Path to PHPMailer (relative to this file)
// This assumes PHPMailer is in /public_html/phpmailer/
// and this file is in /public_html/api/
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send password reset email using PHPMailer
 */
function sendPasswordResetEmail($to, $name, $resetLink) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        if (EMAIL_DEBUG > 0) {
            $mail->SMTPDebug = EMAIL_DEBUG;
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer [$level]: $str");
            };
        }
        
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = EMAIL_CHARSET;
        
        // Recipients
        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->addReplyTo(EMAIL_REPLY_TO, EMAIL_REPLY_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - KejaLink';
        
        // Email body
        $mail->Body = getPasswordResetEmailHTML($name, $resetLink);
        $mail->AltBody = getPasswordResetEmailText($name, $resetLink);
        
        // Send email
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Get HTML email template for password reset
 */
function getPasswordResetEmailHTML($name, $resetLink) {
    $currentYear = date('Y');
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 KejaLink</h1>
        </div>
        <div class="content">
            <h2>Password Reset Request</h2>
            <p>Hi <strong>{$name}</strong>,</p>
            <p>Click the button below to reset your password:</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{$resetLink}" class="button">Reset My Password</a>
            </div>
            <p>This link expires in 1 hour.</p>
        </div>
        <div class="footer">
            <p>© {$currentYear} KejaLink. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Get plain text email template
 */
function getPasswordResetEmailText($name, $resetLink) {
    return "Hi {$name},\n\nReset your password: {$resetLink}\n\nThis link expires in 1 hour.\n\n© " . date('Y') . " KejaLink";
}

?>
