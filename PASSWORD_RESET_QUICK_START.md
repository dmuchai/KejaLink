# 🔐 Password Reset Feature - Quick Reference

## ✅ What You Can Do Now

### 1. **Show/Hide Password** 👁️
- **Where**: Login page, Register page, Reset password page
- **How**: Click the eye icon next to password field
- **Benefit**: Users can verify they typed password correctly

### 2. **Forgot Password** 📧
- **Where**: Login page → "Forgot Password?" link
- **Flow**:
  1. User enters email
  2. Receives reset link via email (valid for 1 hour)
  3. Clicks link → enters new password
  4. Redirected to login

### 3. **Secure Password Reset** 🔒
- Time-limited tokens (expire in 1 hour)
- One-time use (can't reuse same link)
- Secure random token generation
- No email enumeration (doesn't reveal if email exists)

---

## 📱 User Experience

### Password Visibility Toggle
```
Login Page:
┌─────────────────────────────┐
│ Email: user@example.com     │
├─────────────────────────────┤
│ Password: ••••••••  [👁️]   │  ← Click eye to reveal
├─────────────────────────────┤
│      Forgot Password?       │  ← New link!
└─────────────────────────────┘
```

### Forgot Password Flow
```
Step 1: Request Reset
┌─────────────────────────────┐
│   Forgot Password?          │
├─────────────────────────────┤
│ Email: user@example.com     │
│ [Send Reset Link]           │
└─────────────────────────────┘
        ↓
Step 2: Email Sent
┌─────────────────────────────┐
│   ✉️ Check Your Email       │
├─────────────────────────────┤
│ We've sent reset link to    │
│ user@example.com            │
└─────────────────────────────┘
        ↓
Step 3: Email Received
┌─────────────────────────────┐
│ Subject: Password Reset     │
├─────────────────────────────┤
│ Hi User,                    │
│ Click to reset password:    │
│ [Reset Password]            │
│ Link expires in 1 hour      │
└─────────────────────────────┘
        ↓
Step 4: Set New Password
┌─────────────────────────────┐
│   Reset Password            │
├─────────────────────────────┤
│ New Password: ••••  [👁️]   │
│ Confirm: ••••  [👁️]        │
│ [Reset Password]            │
└─────────────────────────────┘
        ↓
Step 5: Success!
┌─────────────────────────────┐
│   ✅ Success!               │
├─────────────────────────────┤
│ Password reset successful   │
│ Redirecting to login...     │
└─────────────────────────────┘
```

---

## 🚀 Deployment Checklist

### Before Deploying:
- [x] Code committed to GitHub ✅
- [x] Frontend built (`npm run build`) ✅
- [ ] Database migration run (see below)
- [ ] Backend auth.php uploaded
- [ ] Frontend deployed
- [ ] Email account created (`noreply@kejalink.co.ke`)
- [ ] Tested end-to-end

### Quick Deploy Steps:

**1. Database (Run in phpMyAdmin):**
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

**2. Backend (via cPanel File Manager):**
- Upload `php-backend/api/auth.php` to `/public_html/api/api/auth.php`

**3. Frontend (via cPanel File Manager):**
- Upload all files from `dist/` to `/public_html/`

**4. Email (cPanel → Email Accounts):**
- Create email: `noreply@kejalink.co.ke`
- Or configure SMTP service (see full docs)

---

## 🧪 Testing

### Test 1: Password Visibility
1. Go to https://kejalink.co.ke/auth
2. Type password in field
3. Click eye icon
4. **Expected**: Password text becomes visible

### Test 2: Forgot Password
1. Click "Forgot Password?" link
2. Enter registered email
3. Check inbox for reset email
4. **Expected**: Email received within 1 minute

### Test 3: Reset Password
1. Click link in email
2. Enter new password (min 6 characters)
3. Confirm password
4. Submit form
5. **Expected**: Redirected to login, can login with new password

### Test 4: Security
1. Try using reset link twice
   - **Expected**: "This link has already been used"
2. Try invalid token
   - **Expected**: "Invalid reset token"
3. Wait >1 hour and try token
   - **Expected**: "This link has expired"

---

## 📊 Key Files Changed

**Frontend:**
- `components/Input.tsx` - Password toggle
- `pages/AuthPage.tsx` - Forgot password link
- `pages/ForgotPasswordPage.tsx` - NEW
- `pages/ResetPasswordPage.tsx` - NEW
- `services/apiClient.ts` - Reset API methods
- `App.tsx` - New routes

**Backend:**
- `php-backend/api/auth.php` - 3 new endpoints
- `php-backend/migrations/001_add_password_reset_tokens.sql` - NEW table

**Documentation:**
- `PASSWORD_RESET_DEPLOYMENT.md` - Full deployment guide

---

## 🆘 Quick Troubleshooting

**Email not arriving?**
- Check spam folder
- Verify email account exists in cPanel
- Check error log: `tail -f ~/domains/kejalink.co.ke/logs/kejalink.co.ke.error.log`

**Reset link not working?**
- Check if token expired (valid for 1 hour)
- Verify database table exists
- Test validation endpoint directly:
  ```bash
  curl "https://kejalink.co.ke/api/api/auth.php?action=validate-reset-token&token=YOUR_TOKEN"
  ```

**Password toggle not showing?**
- Clear browser cache
- Check if lucide-react is installed: `npm list lucide-react`
- Verify dist/ was deployed

---

## 📞 Need Help?

See full documentation: `PASSWORD_RESET_DEPLOYMENT.md`

**Common Issues:**
- 404 on reset page → Check App.tsx routes deployed
- Email delivery → Check cPanel Email Deliverability settings
- Token errors → Verify database migration ran successfully

---

**Version**: 2.0.0  
**Last Updated**: November 1, 2025  
**Status**: ✅ Committed & Pushed to GitHub
