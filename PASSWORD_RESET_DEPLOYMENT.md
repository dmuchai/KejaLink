# Password Reset Feature - Deployment Guide

## 🔐 Overview
This update adds complete password reset functionality to KejaLink:
- **Password Visibility Toggle**: Show/hide password while typing
- **Forgot Password Flow**: Request password reset via email
- **Secure Reset Tokens**: One-time use, time-limited reset links
- **Email Notifications**: Automated password reset emails

---

## 📋 What's New

### Frontend Changes
1. **Input Component** (`components/Input.tsx`)
   - Added `showPasswordToggle` prop
   - Eye/EyeOff icons to reveal/hide password

2. **AuthPage** (`pages/AuthPage.tsx`)
   - "Forgot Password?" link on login form
   - Password visibility toggle enabled

3. **New Pages**:
   - `pages/ForgotPasswordPage.tsx` - Request password reset
   - `pages/ResetPasswordPage.tsx` - Set new password with token validation

4. **API Client** (`services/apiClient.ts`)
   - `authAPI.forgotPassword(email)`
   - `authAPI.validateResetToken(token)`
   - `authAPI.resetPassword(token, newPassword)`

5. **Routes** (`App.tsx`)
   - `/forgot-password` - Request reset
   - `/reset-password?token=xxx` - Reset with token

### Backend Changes
1. **Database Migration** (`php-backend/migrations/001_add_password_reset_tokens.sql`)
   - New `password_reset_tokens` table

2. **Auth API** (`php-backend/api/auth.php`)
   - `POST /api/auth.php?action=forgot-password` - Generate reset token & send email
   - `GET /api/auth.php?action=validate-reset-token&token=xxx` - Validate token
   - `POST /api/auth.php?action=reset-password` - Update password

---

## 🚀 Deployment Steps

### Step 1: Database Migration
Run this SQL in cPanel phpMyAdmin:

```sql
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    token CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 2: Upload Backend Files
Via cPanel File Manager:
1. Navigate to `/public_html/api/api/`
2. **Replace** `auth.php` with the updated version from `php-backend/api/auth.php`

### Step 3: Configure Email (IMPORTANT!)
The default PHP `mail()` function is used. For production, you should:

**Option A: Use cPanel Email (Simple)**
- Emails will be sent from `noreply@kejalink.co.ke`
- Make sure this email exists in cPanel → Email Accounts
- No code changes needed

**Option B: Use SMTP Service (Recommended)**
Install PHPMailer and update `sendPasswordResetEmail()` function:
```bash
composer require phpmailer/phpmailer
```

### Step 4: Deploy Frontend
```bash
# Build
npm run build

# Upload dist/ contents to /public_html/ via cPanel File Manager
```

### Step 5: Test
1. **Test Password Visibility Toggle**:
   - Go to https://kejalink.co.ke/auth
   - Click eye icon on password field
   - Password should toggle between hidden/visible

2. **Test Forgot Password**:
   - Click "Forgot Password?" link
   - Enter registered email
   - Check email for reset link
   - Click link → should go to reset password page
   - Enter new password → should redirect to login

3. **Test Token Security**:
   - Try using same reset link twice → should fail
   - Try using expired token (wait 1 hour) → should fail
   - Try invalid token → should fail

---

## 🔧 Configuration

### Email Settings
Edit `php-backend/api/auth.php` line 360-380 to customize:
- **From Address**: Change `noreply@kejalink.co.ke`
- **Subject**: Modify email subject
- **Template**: Update HTML email template
- **Token Expiry**: Currently 1 hour (line 268)

### Security Settings
- **Token Length**: 64 characters (line 267) - DO NOT REDUCE
- **Token Expiry**: 1 hour (line 268) - Adjust as needed
- **Password Min Length**: 6 characters (line 403) - Consider increasing

---

## 🧹 Maintenance

### Clean Up Old Tokens
Run this periodically in phpMyAdmin:
```sql
DELETE FROM password_reset_tokens 
WHERE expires_at < NOW() OR used = TRUE;
```

Or create a cron job in cPanel:
```bash
# Run daily at 2 AM
0 2 * * * mysql -u kejalink_user -pKD6CAXdeAfpvdHxHzRYq kejalink_db -e "DELETE FROM password_reset_tokens WHERE expires_at < NOW() OR used = TRUE;"
```

---

## 🐛 Troubleshooting

### Email Not Sending
1. Check cPanel → Email Deliverability
2. Verify SPF/DKIM records configured
3. Check spam folder
4. Review error logs: `tail -f ~/domains/kejalink.co.ke/logs/kejalink.co.ke.error.log`

### Token Validation Fails
1. Check system time: `date` (server time must be accurate)
2. Verify token in database:
   ```sql
   SELECT * FROM password_reset_tokens WHERE token = 'YOUR_TOKEN' ORDER BY created_at DESC LIMIT 1;
   ```
3. Check if token expired or used

### Frontend Errors
1. Clear browser cache
2. Check browser console for errors
3. Verify routes in App.tsx
4. Check API endpoints returning correct status codes

---

## 📊 Database Schema

```
password_reset_tokens
├── id (CHAR(36)) - UUID primary key
├── user_id (CHAR(36)) - Foreign key to users.id
├── token (CHAR(64)) - Unique reset token
├── expires_at (DATETIME) - Token expiration
├── used (BOOLEAN) - Whether token has been used
└── created_at (DATETIME) - When token was created
```

---

## 🔐 Security Features

✅ **Secure Token Generation**: Uses `random_bytes(32)` for cryptographic randomness
✅ **Time-Limited Tokens**: Expire after 1 hour
✅ **One-Time Use**: Tokens marked as used after successful reset
✅ **No Email Enumeration**: Always returns success even if email doesn't exist
✅ **Password Hashing**: BCrypt with `password_hash()`
✅ **Token Invalidation**: Old tokens invalidated when new one requested

---

## 📝 User Flow

```
1. User clicks "Forgot Password?" on login page
   ↓
2. Enters email address → POST /api/auth.php?action=forgot-password
   ↓
3. Backend generates token, saves to DB, sends email
   ↓
4. User receives email with reset link: /reset-password?token=xxx
   ↓
5. Clicks link → Frontend validates token via GET /api/auth.php?action=validate-reset-token
   ↓
6. User enters new password → POST /api/auth.php?action=reset-password
   ↓
7. Backend updates password, marks token as used
   ↓
8. User redirected to login with success message
```

---

## ✅ Checklist

Before marking as complete:
- [ ] Database migration run successfully
- [ ] `auth.php` uploaded to production
- [ ] Frontend built and deployed
- [ ] Email account `noreply@kejalink.co.ke` created in cPanel
- [ ] Password visibility toggle working
- [ ] Forgot password flow tested end-to-end
- [ ] Reset token validation working
- [ ] Password successfully updated
- [ ] Email received with reset link
- [ ] Token security tested (expired, used, invalid)

---

## 📞 Support

If issues arise:
1. Check error logs: `~/domains/kejalink.co.ke/logs/kejalink.co.ke.error.log`
2. Review browser console errors
3. Test API endpoints directly with cURL:
   ```bash
   # Test forgot password
   curl -X POST https://kejalink.co.ke/api/api/auth.php?action=forgot-password \
     -H "Content-Type: application/json" \
     -d '{"email":"test@example.com"}'
   ```

---

**Last Updated**: November 1, 2025  
**Version**: 2.0.0  
**Author**: Dennis Muchai
