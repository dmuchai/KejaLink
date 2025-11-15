# Gmail & Yahoo Mail Delivery Fix Guide

## Current Issue
Emails deliver successfully to:
- ✅ Non-Gmail addresses (Outlook, custom domains, etc.)
- ❌ Gmail accounts
- ❌ Yahoo Mail accounts

## Root Cause
Gmail and Yahoo have stricter email authentication requirements:
1. Valid SPF records
2. Valid DKIM signatures
3. Valid reverse DNS (PTR record)
4. Good sender reputation

---

## Step 1: Verify SPF/DKIM Configuration

### Check in cPanel:

1. **Login to cPanel:**
   ```
   https://da23.host-ww.net:2083
   ```

2. **Go to Email Deliverability:**
   - Search for "Email Deliverability" in cPanel
   - Click on it
   - Find your domain: **kejalink.co.ke**

3. **Check Status:**
   You should see:
   - ✅ SPF: Valid
   - ✅ DKIM: Valid
   - ✅ Reverse DNS: Valid (or check with host)

If you see red X marks, click "Manage" to fix them.

---

## Step 2: Verify DNS Records (External Check)

### Check SPF Record:
```bash
# Run this command on your local machine:
nslookup -type=TXT kejalink.co.ke
```

**Expected result:**
```
kejalink.co.ke  text = "v=spf1 a mx ip4:YOUR_SERVER_IP ~all"
```

### Check DKIM Record:
```bash
# Run this command (replace 'default' with your selector if different):
nslookup -type=TXT default._domainkey.kejalink.co.ke
```

**Expected result:**
```
default._domainkey.kejalink.co.ke  text = "v=DKIM1; k=rsa; p=MII..."
```

---

## Step 3: Update PHPMailer Configuration

The current configuration might need some tweaks for better Gmail/Yahoo compatibility.

### Issues to Fix:

1. **Missing DKIM signing in PHPMailer**
2. **Missing proper email headers**
3. **Potentially using wrong SMTP port**

Let me create an improved email-config.php with these fixes...

---

## Step 4: Improved Email Configuration

### Enhanced Settings for Gmail/Yahoo:

1. **Add DKIM signing** (if you have DKIM private key)
2. **Add proper headers** (List-Unsubscribe, etc.)
3. **Try port 587 with TLS** instead of 465 with SSL
4. **Add error logging** for better debugging

### Additional PHPMailer Options:

```php
// For better Gmail compatibility:
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

// Add these headers:
$mail->addCustomHeader('X-Mailer', 'KejaLink Password Reset System');
$mail->addCustomHeader('Precedence', 'bulk');
$mail->Sender = 'noreply@kejalink.co.ke'; // Bounce address
```

---

## Step 5: Alternative Solutions (Quick Fixes)

### Option A: Try Different SMTP Port

**Current:** Port 465 (SSL)
**Try:** Port 587 (TLS)

Benefits of port 587:
- ✅ Better compatibility with Gmail/Yahoo
- ✅ More modern encryption (STARTTLS)
- ✅ Less likely to be blocked by ISPs

### Option B: Use Webmail Domain

Instead of:
```
From: noreply@kejalink.co.ke
```

Try a subdomain:
```
From: noreply@mail.kejalink.co.ke
```

This can help with reputation scoring.

### Option C: Warm Up Your Email Domain

Gmail/Yahoo might be blocking because it's a new domain. Solutions:
1. Send test emails gradually (start with 10/day, increase slowly)
2. Use a transactional email service (SendGrid, Mailgun, AWS SES)
3. Wait 24-48 hours for DNS propagation

---

## Step 6: Check Email Logs

### On the server, check logs:

```bash
# Email delivery logs:
tail -f /var/log/maillog

# Or:
tail -f /var/log/exim_mainlog

# PHP error logs:
tail -f ~/domains/kejalink.co.ke/logs/kejalink.co.ke.error.log
```

Look for:
- "550 5.7.26" - Authentication required
- "550 5.7.1" - Sender blocked
- "421" - Temporary failure (DNS issues)
- "DKIM verification failed"

---

## Step 7: Test Email Authentication

### Use Online Tools:

1. **Mail-Tester:**
   - Go to: https://www.mail-tester.com
   - Get a test email address
   - Send password reset to that address
   - Check your score (should be 10/10)

2. **MXToolbox:**
   - Go to: https://mxtoolbox.com/SuperTool.aspx
   - Enter: kejalink.co.ke
   - Check SPF, DKIM, DMARC records

3. **Google Admin Toolbox:**
   - Go to: https://toolbox.googleapps.com/apps/checkmx/
   - Enter: kejalink.co.ke
   - Verify MX records and authentication

---

## Step 8: Recommended Immediate Actions

### 1. Enable SMTP Debug Mode Temporarily

In email-config.php, change:
```php
define('EMAIL_DEBUG', 0);
```

To:
```php
define('EMAIL_DEBUG', 2); // Shows SMTP connection details
```

Then test sending to Gmail and check error logs for specific errors.

### 2. Try Port 587 with TLS

Would you like me to create an updated email-config.php that uses:
- Port 587 instead of 465
- TLS instead of SSL
- Better error handling
- Additional headers for Gmail/Yahoo

### 3. Contact Your Hosting Provider

Ask HostAfrica support:
1. "Is port 587 (SMTP with STARTTLS) enabled?"
2. "Are SPF and DKIM records properly configured for kejalink.co.ke?"
3. "Is the server IP blacklisted by Gmail/Yahoo?"
4. "What's the reverse DNS (PTR) record for my server IP?"

---

## Expected Timeline

### DNS Propagation:
- **SPF/DKIM records:** 24-48 hours to fully propagate globally
- **Current status:** If configured today, should work by Nov 2-3

### Immediate Workarounds:
1. Use port 587 instead of 465 (can help immediately)
2. Add proper email headers (can help immediately)
3. Use transactional email service (works immediately)

---

## Alternative: Use Transactional Email Service (Recommended)

For production reliability, consider using:

### Free Tier Options:
1. **SendGrid:** 100 emails/day free
2. **Mailgun:** 5,000 emails/month free (first 3 months)
3. **AWS SES:** $0.10 per 1,000 emails (after free tier)
4. **Brevo (Sendinblue):** 300 emails/day free

These services have:
- ✅ Pre-warmed IP addresses
- ✅ Built-in DKIM/SPF
- ✅ High deliverability to Gmail/Yahoo
- ✅ Email analytics
- ✅ Bounce handling

---

## Decision Matrix

| Solution | Cost | Time | Reliability | Difficulty |
|----------|------|------|-------------|------------|
| Wait for DNS (current) | Free | 24-48h | Medium | Easy |
| Try port 587/TLS | Free | 5 min | Medium | Easy |
| Contact host support | Free | 1-2h | High | Easy |
| Use SendGrid/Mailgun | Free tier | 30 min | Very High | Medium |
| Use AWS SES | ~$0.10/1k | 1h | Very High | Hard |

---

## What Would You Like to Do?

**Option 1:** Wait 24-48 hours for DNS propagation (easiest, free)

**Option 2:** Update email-config.php to use port 587/TLS (quick fix, might help)

**Option 3:** Set up SendGrid or Mailgun (most reliable, professional)

**Option 4:** Contact HostAfrica support to verify configuration

Let me know which option you prefer, and I'll help you implement it!
