# Production Deployment Checklist - November 17, 2025

## 🎯 What's Being Deployed

### ✅ Frontend Changes (County Removal + New Google Maps API Key)
- Removed County field from entire application
- Updated to new Google Maps API key (old one was exposed)
- Simplified location to just Address + Neighborhood
- Fixed all TypeScript compilation errors
- Build successful!

### ✅ Backend Changes (Minor API Update)
- Removed `county` parameter from listings.php filter

---

## 📦 Deployment Package

**File**: `kejalink-frontend-20251117_214124.zip`  
**Build Time**: November 17, 2025 21:41  
**Size**: ~440KB  
**New Google Maps API Key**: `AIzaSyCA4w2xA4kjruzqInB2BQyLOzFbMNjHKZE`

---

## 🚀 Step-by-Step Deployment

### 1. Backup Current Production (5 mins)

**Via cPanel**:
1. Login: https://kejalink.co.ke:2083
2. File Manager → `/public_html/`
3. Right-click on `public_html` → Compress → ZIP
4. Name: `backup-before-county-removal-20251117.zip`
5. Download to local machine

### 2. Deploy Frontend (10 mins)

**Option A: Upload ZIP** (Recommended)
1. cPanel File Manager → `/public_html/`
2. Upload `kejalink-frontend-20251117_214124.zip`
3. Right-click ZIP → Extract
4. Confirm overwrite
5. Delete ZIP file
6. Verify `index.html` timestamp updated

**Option B: Manual Upload**
1. Delete old `/public_html/assets/` folder
2. Upload from `deploy-package/`:
   - `index.html` ✅
   - `assets/` folder ✅
   - `favicon.ico` ✅
   - `vite.svg` ✅

⚠️ **DO NOT DELETE**:
- ❌ `api/` folder
- ❌ `phpmailer/` folder  
- ❌ `uploads/` folder
- ❌ `.htaccess` file

### 3. Update Backend API (3 mins)

**File to Update**: `/public_html/api/listings.php`

**Changes**:
1. Navigate to `/public_html/api/`
2. Edit `listings.php` (Code Editor or download/upload)
3. **Find line ~84-87** (the county filter block):
```php
if (isset($_GET['county'])) {
    $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(l.location, '$.county')) = ?";
    $params[] = $_GET['county'];
}
```

4. **DELETE those 4 lines** (remove county filter)

5. **Update the comment at line ~43**:
```php
// OLD:
 * GET /api/listings.php?bedrooms=2&county=Nairobi&minPrice=50000&maxPrice=100000

// NEW:
 * GET /api/listings.php?bedrooms=2&minPrice=50000&maxPrice=100000&location=Kilimani
```

6. Save file

**OR** - Upload entire file:
- Upload `php-backend/api/listings.php` from local
- Replace `/public_html/api/listings.php`

### 4. Verify Google Maps API Key Restrictions (5 mins)

**Critical for Security!**

1. Go to: https://console.cloud.google.com/apis/credentials
2. Find your API key: `AIzaSyCA4w2xA4kjruzqInB2BQyLOzFbMNjHKZE`
3. Click Edit
4. **Application restrictions**:
   - Select: "HTTP referrers (web sites)"
   - Add referrers:
     - `kejalink.co.ke/*`
     - `*.kejalink.co.ke/*`
5. **API restrictions**:
   - Select: "Restrict key"
   - Enable only:
     - ✅ Maps JavaScript API
     - ✅ Places API
     - ✅ Geocoding API (if needed)
6. Save

---

## 🧪 Testing Checklist

### Basic Functionality (10 mins)
- [ ] Visit https://kejalink.co.ke
- [ ] Homepage loads correctly
- [ ] Images display properly
- [ ] Navigation works
- [ ] Login/Register works

### Form Testing (Key Changes!)
- [ ] Login as agent
- [ ] Click "List a Property"
- [ ] ✅ **Address field has Google autocomplete**
- [ ] ❌ **No County dropdown** (removed!)
- [ ] ❌ **No Neighborhood input field** (removed!)
- [ ] Select address from autocomplete
- [ ] Fill other fields (price, bedrooms, etc.)
- [ ] Submit form
- [ ] Verify new listing appears

### Search & Filter Testing
- [ ] Go to Listings page
- [ ] ❌ **No County filter** (removed!)
- [ ] Search by location keyword works
- [ ] Filter by Property Type works
- [ ] Filter by Bedrooms works
- [ ] Filter by Price range works

### Display Testing
- [ ] Listing cards show: Neighborhood (not county)
- [ ] Listing detail page shows: Address, Neighborhood
- [ ] Agent dashboard shows listings correctly
- [ ] No references to "county" anywhere

### Google Maps Testing
- [ ] Map displays on listings page
- [ ] Markers show for properties with coordinates
- [ ] Single property map on detail page works
- [ ] Address autocomplete works (no API errors in console)

---

## 🐛 Troubleshooting

### Map Not Loading
**Check**:
1. Browser console (F12) for errors
2. Look for "Google Maps" or "API key" errors
3. View Page Source → Search for "maps.googleapis.com"
4. Verify API key is in the script tag

**Fix**:
- Check API key restrictions in Google Cloud Console
- Ensure Maps JavaScript API and Places API are enabled
- Check billing is enabled (if quota exceeded)

### Autocomplete Not Working
**Check**:
1. Network tab for blocked requests
2. Console for "Places API" errors

**Fix**:
- Enable Places API in Google Cloud Console
- Check domain restrictions allow kejalink.co.ke

### "County" Still Showing Somewhere
**Check**:
- Hard refresh browser: Ctrl+Shift+R
- Clear browser cache completely
- Check if old build is cached

**Fix**:
- Re-upload frontend files
- Clear CDN cache if using one

### 500 Internal Server Error
**Check**:
- cPanel Error Logs (Home → Errors)
- Look for PHP syntax errors in listings.php

**Fix**:
- Re-upload `listings.php` from local repo
- Check file permissions (644)

---

## 🔄 Rollback Plan

If something goes wrong:

1. **Restore Frontend**:
   - cPanel File Manager
   - Extract `backup-before-county-removal-20251117.zip`
   - Confirm overwrite

2. **Restore Backend**:
   - Re-add county filter to `listings.php`
   - Or restore from backup

3. **Clear Cache**:
   - Ctrl+Shift+R (hard refresh)
   - Clear all browser cache for kejalink.co.ke

---

## 📊 Expected Results

### ❌ Before Deployment
- County dropdown in listing form
- County filter in search
- County displayed on listings
- Old exposed Google Maps API key

### ✅ After Deployment
- Single "Location (Address)" field with autocomplete
- No county anywhere in the app
- Neighborhood auto-captured in background
- New secure Google Maps API key with restrictions
- Simplified, cleaner UX focused on Nairobi

---

## 📝 Post-Deployment

### Monitor (First 24 hours)
- [ ] Check error logs daily
- [ ] Monitor Google Maps API usage
- [ ] Watch for user feedback
- [ ] Verify no broken functionality

### Optional: Update Existing Listings
Existing listings don't have coordinates yet. To add them:

**Option 1**: Manual (agents edit)
- Login as agent
- Edit each old listing
- Use autocomplete to re-select address
- Save → coordinates added automatically

**Option 2**: Bulk fix (if many listings)
- Use SQL script: `add-coordinates-production.sql`
- Run in phpMyAdmin
- Adds approximate coordinates for Nairobi locations

---

## ✅ Deployment Complete!

Once all tests pass:
- [ ] Mark deployment as successful
- [ ] Document any issues encountered
- [ ] Update team on new simplified flow
- [ ] Monitor for 24-48 hours

**Deployed by**: Dennis Muchai  
**Deployment Date**: November 17, 2025  
**Deployment Time**: _________  
**Status**: ⏳ Pending / ✅ Success / ❌ Failed  
**Notes**: _____________________

---

## 🔐 Security Reminders

1. ✅ Old Google Maps API key has been revoked (via Google Cloud Console)
2. ✅ New API key embedded in build
3. ✅ New API key has domain restrictions
4. ✅ `.env` file is in `.gitignore`
5. ⏳ TODO: Clean Git history of exposed key (optional)

---

**Package Location**: `/home/dennis-muchai/KejaLink/kejalink-frontend-20251117_214124.zip`  
**Backend File**: `/home/dennis-muchai/KejaLink/php-backend/api/listings.php`
