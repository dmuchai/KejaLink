# Brevo (Sendinblue) Setup Guide for KejaLink Password Reset

## Why Brevo?
- **Free Tier**: 300 emails/day (sufficient for password resets)
- **Instant Gmail/Yahoo Delivery**: Pre-configured SPF/DKIM authentication
- **Professional**: Used by major companies for transactional emails
- **Setup Time**: 5-10 minutes

---

## Step 1: Create Brevo Account

1. Go to **https://www.brevo.com/**
2. Click **"Sign up free"**
3. Fill in your details:
   - Email: (your email)
   - Password: (create a strong password)
   - Company: KejaLink
4. Verify your email address (check inbox for verification email)
5. Complete the onboarding questions:
   - Business type: "Real Estate / Property Management"
   - Purpose: "Transactional emails"

---

## Step 2: Get SMTP Credentials

1. After logging in, click your **profile icon** (top-right corner)
2. Go to **"SMTP & API"** in the dropdown menu
3. Click on **"SMTP"** tab
4. You'll see your SMTP credentials:

```
Server: smtp-relay.brevo.com
Port: 587
Login: (your email used for signup)
SMTP Key: (click "Create a new SMTP key" to generate)
```

5. **IMPORTANT**: Click **"Create a new SMTP key"**
   - Name it: "KejaLink Password Reset"
   - Copy the key immediately (shown only once)
   - Save it somewhere safe (you'll need it in Step 3)

---

## Step 3: Update Your Email Configuration

I'll create an updated `email-config.php` file for you. You'll need to:

1. **Replace these values** in the file:
   - `SMTP_USERNAME`: Your Brevo login email
   - `SMTP_PASSWORD`: The SMTP key you just created

2. **Upload the new file** to your server at:
   ```
   /public_html/api/email-config.php
   ```

---

## Step 4: Test Email Delivery

After uploading the new configuration:

1. Go to **https://kejalink.co.ke/forgot-password**
2. Enter a **Gmail address** (yours or a test account)
3. Click "Send Reset Link"
4. Check the Gmail inbox (should arrive within seconds)
5. Repeat with a **Yahoo Mail address** to confirm

---

## Brevo Dashboard Features

After setup, you can monitor emails at https://app.brevo.com/:

- **Email Activity**: See all sent emails, delivery status, opens
- **Statistics**: Track email performance
- **Real-time Logs**: Debug any delivery issues
- **Sender Reputation**: Monitor your sending score

---

## Troubleshooting

### Email Not Arriving?
1. Check Brevo dashboard "Logs" section for delivery status
2. Verify SMTP credentials are correct in email-config.php
3. Check spam folder (first email might go there)

### SMTP Authentication Failed?
- Double-check the SMTP key (no extra spaces)
- Ensure you're using the SMTP key, not your account password

### Daily Limit Reached?
- Free tier: 300 emails/day
- Upgrade to paid plan if needed (starting at $25/month for 20,000 emails)

---

## Next Steps

1. Complete Steps 1-2 above to get your SMTP credentials
2. Share the credentials with me (in a secure way)
3. I'll update your email-config.php file
4. Upload to server and test

**Ready?** Let me know when you have your Brevo SMTP credentials!
