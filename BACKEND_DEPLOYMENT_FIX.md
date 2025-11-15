# PHP Backend Deployment Checklist

## Problem Found
The backend files are in the wrong locations on the server!

### Current (WRONG) Structure:
```
public_html/
  └── api/
      ├── auth.php
      ├── config.php        ← WRONG! Should be in parent
      └── email-config.php
```

### Correct Structure Needed:
```
public_html/
  ├── config.php            ← Move here from api/
  ├── auth.php              ← Upload this (helper functions)
  └── api/
      ├── auth.php          ← API endpoint (keep here)
      ├── listings.php      ← MISSING - upload this
      ├── upload.php        ← MISSING - upload this
      └── email-config.php  ← Already correct ✅
```

## Files to Upload

### Step 1: Upload to `/public_html/` (root)

From your local `php-backend/` folder, upload these files to `/public_html/`:

1. **config.php**
   - Local: `php-backend/config.php`
   - Server: `/home/kejalink/domains/kejalink.co.ke/public_html/config.php`
   - Contains: Database credentials, JWT secret, upload paths

2. **auth.php** (helper file with authentication functions)
   - Local: `php-backend/auth.php`
   - Server: `/home/kejalink/domains/kejalink.co.ke/public_html/auth.php`
   - Contains: JWT functions, password hashing, authentication helpers

### Step 2: Upload to `/public_html/api/`

From your local `php-backend/api/` folder, upload these files to `/public_html/api/`:

1. **listings.php**
   - Local: `php-backend/api/listings.php`
   - Server: `/public_html/api/listings.php`
   - Handles: Get/Create/Update/Delete property listings

2. **upload.php**
   - Local: `php-backend/api/upload.php`
   - Server: `/public_html/api/upload.php`
   - Handles: Image uploads for properties

3. **auth.php** (API endpoint - replace existing)
   - Local: `php-backend/api/auth.php`
   - Server: `/public_html/api/auth.php`
   - Handles: Register/Login/Logout/Password Reset endpoints

### Step 3: Move existing config.php

**IMPORTANT:** You have `config.php` in `/public_html/api/` - it needs to be moved!

Using cPanel File Manager:
1. Go to `/public_html/api/`
2. Select `config.php`
3. Click "Move" button
4. Move to: `/public_html/config.php` (parent directory)
5. Confirm

OR delete it from `/api/` and upload the new one to `/public_html/`

## Upload Methods

### Method 1: cPanel File Manager (Easiest)

1. Login to cPanel: https://da23.host-ww.net:2083
2. Open **File Manager**
3. Navigate to target directory
4. Click **Upload** button
5. Select files from your computer
6. Wait for upload to complete

### Method 2: FTP Client (FileZilla, etc.)

```
Host: ftp.kejalink.co.ke
Username: kejalink
Password: [your password]
Port: 21

Upload files to correct directories as listed above
```

## Verification Steps

After uploading, visit this test page:
```
https://kejalink.co.ke/api/test-endpoints.php
```

You should see:
- ✅ auth.php: EXISTS
- ✅ listings.php: EXISTS  ← Should change from ❌ to ✅
- ✅ upload.php: EXISTS    ← Should change from ❌ to ✅
- ✅ config.php: EXISTS
- ✅ email-config.php: EXISTS

Then test the forgot password button - it should return valid JSON!

## Common Issues

### Issue: "Class 'Database' not found"
**Cause:** config.php is in wrong location
**Fix:** Move config.php to /public_html/ (parent of api/)

### Issue: "Call to undefined function generateJWT()"
**Cause:** auth.php (helper file) is missing from /public_html/
**Fix:** Upload auth.php to /public_html/ (parent of api/)

### Issue: "listings.php not found"
**Cause:** Missing API files
**Fix:** Upload listings.php and upload.php to /public_html/api/

## After Deployment

1. **Test API endpoints** using test-endpoints.php
2. **Update JWT_SECRET** using update-jwt-secret.php (security!)
3. **Deploy frontend** with new build (dist/ folder)
4. **Test password reset** end-to-end
5. **Delete test files** (test-endpoints.php, update-jwt-secret.php, diagnose-paths.php)

## Summary

**Root Cause:** Files uploaded to wrong directories. PHP files use `../` to reference parent directory, so structure matters!

**Files to Upload:**
- 2 files to `/public_html/` (root)
- 3 files to `/public_html/api/`

**Expected Result:** All API endpoints return valid JSON responses! ✨
