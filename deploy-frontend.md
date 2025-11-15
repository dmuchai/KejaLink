# Frontend Deployment Instructions

## What Changed
Fixed the API URL duplication issue:
- **Before:** `https://kejalink.co.ke/api` + `/api/auth.php` = `/api/api/auth.php` ❌
- **After:** `https://kejalink.co.ke` + `/api/auth.php` = `/api/auth.php` ✅

## Latest Build Information
- **Build Date:** November 1, 2025
- **Build Status:** ✅ Successful
- **API Base URL:** `https://kejalink.co.ke`
- **Includes:** .htaccess for proper SPA routing

## Files to Upload

Upload the entire **`dist/`** folder contents to your server at:
```
/home/kejalink/domains/kejalink.co.ke/public_html/
```

**IMPORTANT:** The new build includes a `.htaccess` file that's critical for password reset links to work!

### Using cPanel File Manager:

1. **Backup Current Files** (Optional but recommended):
   - Compress current `public_html` files
   - Download as backup

2. **Navigate to public_html**:
   - Go to cPanel → File Manager
   - Navigate to `/home/kejalink/domains/kejalink.co.ke/public_html/`

3. **Delete OLD frontend files** (Keep the `api` folder and `phpmailer` folder!):
   - Select and delete:
     - `index.html`
     - `assets/` folder (the old one)
     - `favicon.ico`
     - `vite.svg`
   - **DO NOT DELETE**:
     - `api/` folder
     - `phpmailer/` folder
     - `.htaccess` (if exists)

4. **Upload NEW files from `dist/` folder**:
   - Click "Upload" button
   - Select all files from your local `dist/` folder:
     - `index.html`
     - `assets/` folder (new one)
     - `favicon.ico`
     - `vite.svg`
   - Wait for upload to complete

5. **Verify Structure**:
   Your `public_html/` should look like:
   ```
   public_html/
   ├── api/                    ← Keep this
   ├── assets/                 ← New from dist/
   ├── phpmailer/              ← Keep this
   ├── index.html              ← New from dist/
   ├── favicon.ico
   └── vite.svg
   ```

## Testing After Deployment

1. **Visit Homepage**:
   ```
   https://kejalink.co.ke
   ```
   Should load without errors

2. **Test Login**:
   - Go to login page
   - Try logging in with existing account
   - Should work successfully

3. **Test Forgot Password**:
   - Go to login page
   - Click "Forgot Password?"
   - Enter your email
   - Should see success message (no 500 error!)
   - Check email for reset link
   - Click reset link
   - Set new password
   - Login with new password

4. **Check Browser Console**:
   - Press F12 (Developer Tools)
   - Look for any errors
   - API calls should go to `/api/auth.php` (not `/api/api/auth.php`)

## Expected Results

✅ No more `/api/api/` double path errors
✅ Password reset flow works end-to-end
✅ All API calls work correctly
✅ Login/logout works
✅ Property listings load

## If Something Goes Wrong

1. **Clear Browser Cache**:
   - Hard refresh: `Ctrl+F5` (Windows/Linux) or `Cmd+Shift+R` (Mac)

2. **Check API endpoint**:
   ```bash
   curl https://kejalink.co.ke/api/auth.php
   ```
   Should return JSON (not 404)

3. **Verify files uploaded**:
   - Check that `index.html` exists in `public_html/`
   - Check that `assets/` folder has new timestamp
   - Check that `api/` folder still exists

## Update JWT_SECRET (While You're At It)

Since you're deploying, also update the JWT secret:

1. Upload `php-backend/update-jwt-secret.php` to `/public_html/api/`
2. Visit: `https://kejalink.co.ke/api/update-jwt-secret.php`
3. Follow instructions on screen
4. Delete the script after running

This will make your authentication more secure.

---

**Need Help?** Check error logs in cPanel → Error Log if something doesn't work.
