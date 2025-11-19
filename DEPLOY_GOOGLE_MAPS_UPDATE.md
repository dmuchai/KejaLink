# Deploy Google Maps & Property Type Updates to HostAfrica

**Date**: November 16, 2025  
**Deployment Type**: Frontend + Backend Update  
**Changes**: Google Maps integration, Property Type badges, coordinate support

---

## 🎯 What's Being Deployed

### Frontend Changes
✅ Google Maps Places Autocomplete integration  
✅ Property Type badges on listing cards and detail pages  
✅ Updated type definitions for coordinates (latitude/longitude)  
✅ Fixed Vite proxy configuration  
✅ Map components support new coordinate fields  

### Backend Changes
✅ Backend normalization for location data (area→neighborhood)  
✅ Support for latitude/longitude in location JSON  

### Build Info
- **Build Time**: November 16, 2025 23:09  
- **Package**: `kejalink-frontend-20251116_230924.zip`
- **Size**: ~500KB (compressed)

---

## 📋 Pre-Deployment Checklist

- [x] Frontend built successfully (`npm run build`)
- [x] Google Maps API key embedded in build
- [x] Deployment package created
- [x] Backend file changes identified
- [ ] Backup current production files
- [ ] Test locally before deploying

---

## 🚀 Deployment Steps

### Step 1: Backup Current Production

**Via cPanel File Manager**:
1. Login to cPanel: https://kejalink.co.ke:2083
2. Navigate to File Manager
3. Go to `/public_html/`
4. **Backup these files**:
   - Right-click on `public_html` → Compress → Create Archive
   - Name: `backup-before-maps-update-20251116.zip`
   - Download to your local machine (just in case)

### Step 2: Deploy Frontend

**Option A: Upload ZIP (Recommended)**

1. **Login to cPanel File Manager**
2. **Navigate to** `/public_html/`
3. **Upload** `kejalink-frontend-20251116_230924.zip`
4. **Right-click on ZIP** → Extract
5. **Confirm overwrite** when prompted
6. **Delete the ZIP file** after extraction
7. **Verify files updated**:
   - Check `index.html` timestamp
   - Check `assets/` folder has new files

**Option B: Upload Individual Files (If ZIP fails)**

1. **Login to cPanel File Manager**
2. **Navigate to** `/public_html/`
3. **Delete old assets**:
   - Delete `assets/` folder (old CSS/JS files)
4. **Upload from** `deploy-package/`:
   - ✅ `index.html` (overwrite)
   - ✅ `assets/` folder (new)
   - ✅ `favicon.ico` (overwrite)
   - ✅ `vite.svg` (overwrite)
   - ✅ `images/` folder (if updated)

**⚠️ CRITICAL: Do NOT delete these folders**:
- ❌ `api/` (backend PHP files)
- ❌ `phpmailer/` (email functionality)
- ❌ `uploads/` (user uploaded images)
- ❌ `.htaccess` (routing rules)

### Step 3: Update Backend File

**Update**: `php-backend/api/listings.php`

1. **Navigate to** `/public_html/api/`
2. **Edit** `listings.php` (use cPanel Code Editor or download/upload)
3. **Find** the `getListings()` function (around line 147)
4. **Add after** the line `$listing['images'] = json_decode($listing['images'] ?? '[]', true) ?? [];`:

```php
// Normalize location: convert 'area' to 'neighborhood' for frontend consistency
if (isset($listing['location']['area']) && !isset($listing['location']['neighborhood'])) {
    $listing['location']['neighborhood'] = $listing['location']['area'];
}
// Also convert 'city' to 'neighborhood' if neighborhood is not set
if (isset($listing['location']['city']) && !isset($listing['location']['neighborhood'])) {
    $listing['location']['neighborhood'] = $listing['location']['city'];
}
```

5. **Find** the `getListingById()` function (around line 226)
6. **Add the same code** after `$listing['images'] = $images;`:

```php
// Normalize location: convert 'area' to 'neighborhood' for frontend consistency
if (isset($listing['location']['area']) && !isset($listing['location']['neighborhood'])) {
    $listing['location']['neighborhood'] = $listing['location']['area'];
}
// Also convert 'city' to 'neighborhood' if neighborhood is not set
if (isset($listing['location']['city']) && !isset($listing['location']['neighborhood'])) {
    $listing['location']['neighborhood'] = $listing['location']['city'];
}
```

7. **Save the file**

**OR** - Upload the entire updated file:
- Upload `php-backend/api/listings.php` from your local repo
- Replace `/public_html/api/listings.php`

### Step 4: Verify .htaccess Configuration

**Check** `/public_html/.htaccess` contains these rules:

```apache
# Enable RewriteEngine
RewriteEngine On

# API routes - don't rewrite these
RewriteCond %{REQUEST_URI} ^/api
RewriteRule ^(.*)$ - [L]

# Uploads - don't rewrite these
RewriteCond %{REQUEST_URI} ^/uploads
RewriteRule ^(.*)$ - [L]

# React Router - redirect all other requests to index.html
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.html [L]
```

If not present, create/update the file.

---

## 🧪 Testing Checklist

### 1. Basic Functionality
- [ ] Visit https://kejalink.co.ke
- [ ] Homepage loads correctly
- [ ] Navigation works (click around)
- [ ] Login/Register works
- [ ] Images display properly

### 2. Google Maps Features
- [ ] Navigate to Listings page
- [ ] **Map displays** with markers for properties
- [ ] Click on a listing to view details
- [ ] **Single property map** shows on detail page
- [ ] Login as agent
- [ ] Click "List a Property"
- [ ] **Address autocomplete works** (start typing "Nairobi" or "Westlands")
- [ ] Select an address from dropdown
- [ ] **County and neighborhood auto-fill**
- [ ] Submit the form (with other required fields)
- [ ] Check if new listing appears on map

### 3. Property Type Badges
- [ ] View listings on homepage/listings page
- [ ] **Property Type badges** appear below price (blue badges)
- [ ] Click on a bedsitter/apartment/studio listing
- [ ] **Property Type badge** shows on detail page
- [ ] Filter by Property Type works
- [ ] Edit an existing listing
- [ ] **Property Type persists** in edit form

### 4. Backend API
- [ ] Check browser console for errors
- [ ] Verify API calls return 200 status
- [ ] Check listings have `latitude` and `longitude` in response
- [ ] Check `neighborhood` field is present in location data

### 5. Mobile Testing
- [ ] Open site on mobile device
- [ ] Maps display correctly
- [ ] Autocomplete works on mobile
- [ ] Property Type badges visible

---

## 🐛 Troubleshooting

### Map Not Displaying
**Symptom**: Shows "Location not available" instead of map  
**Root Cause**: Listing doesn't have latitude/longitude coordinates in database

**Checks**:
1. Open browser console (F12) → Look for listing data
2. Check if `latitude: undefined` and `longitude: undefined`
3. Existing listings created before autocomplete won't have coordinates

**Solution**:
See `FIX_PRODUCTION_COORDINATES.md` for detailed instructions.

**Quick Fix**:
- Login as agent → Edit the listing
- Use address autocomplete → Re-select the address from dropdown
- Save → Coordinates will be added automatically

**Bulk Fix** (for many listings):
- Use SQL script in `add-coordinates-production.sql`
- Run in phpMyAdmin to add approximate coordinates
- Then refine specific listings via UI if needed

---

### Map Loading Forever
**Symptom**: "Loading map..." spinner forever (but listing HAS coordinates)  
**Checks**:
1. Open browser console (F12) → Check for errors
2. Look for "Google Maps" or "API key" errors
3. Verify API key in source: View Page Source → Search for "maps.googleapis.com"
4. Check API key is present and valid in the script tag

**Solution**:
- API key might be invalid or quota exceeded
- Check Google Cloud Console: https://console.cloud.google.com/apis/credentials
- Ensure Maps JavaScript API and Places API are enabled

### Autocomplete Not Working
**Symptom**: No dropdown when typing address  
**Checks**:
1. Browser console → Look for "Places API" errors
2. Check network tab for blocked requests

**Solution**:
- Places API might not be enabled
- API key might have domain restrictions
- Check Google Cloud Console API settings

### Property Type Not Showing
**Symptom**: No blue badges on listings  
**Checks**:
1. Inspect listing data in browser console
2. Check if `propertyType` field is present

**Solution**:
- Backend might not be returning property_type
- Clear browser cache (Ctrl+Shift+R)
- Check database has property_type values

### 500 Internal Server Error
**Symptom**: White screen or API errors  
**Checks**:
1. Check cPanel Error Logs (Home → Errors)
2. Look for PHP syntax errors

**Solution**:
- Backend file might have syntax error
- Re-upload `listings.php` from local repo
- Check file permissions (644 for PHP files)

### Coordinates Not Saving
**Symptom**: New listings don't appear on map  
**Checks**:
1. Submit form with autocomplete
2. Check browser console for errors
3. Check if address was selected from dropdown (not just typed)

**Solution**:
- User must SELECT from autocomplete dropdown to capture coordinates
- Manually typed addresses won't have coordinates
- Can edit listing later to add coordinates

---

## 📊 Expected Results

### Before Deployment
- ❌ Maps show "Location not available"
- ❌ No autocomplete for address input
- ❌ Property Type field in database but not displayed
- ❌ Coordinates missing from existing listings

### After Deployment
- ✅ Maps display with markers for all listings with coordinates
- ✅ Address autocomplete with Kenya restriction
- ✅ County and neighborhood auto-populate
- ✅ Property Type badges visible on cards and detail pages
- ✅ New listings automatically get coordinates when using autocomplete
- ✅ Backend normalizes location data for consistency

---

## 🔄 Rollback Plan

If something goes wrong:

1. **Restore Frontend**:
   - Go to cPanel File Manager
   - Extract `backup-before-maps-update-20251116.zip`
   - Confirm overwrite

2. **Restore Backend**:
   - Revert `api/listings.php` changes
   - Remove the location normalization code

3. **Clear Browser Cache**:
   - Ctrl+Shift+R (hard refresh)
   - Or clear all cache for kejalink.co.ke

---

## 📝 Post-Deployment

### Monitor for Issues
- [ ] Check error logs daily for first 3 days
- [ ] Monitor Google Maps API usage in Cloud Console
- [ ] Watch for user feedback on new features

### Update Existing Listings (Optional)
- [ ] Login as agent
- [ ] Edit old listings one by one
- [ ] Use autocomplete to add coordinates
- [ ] Save → listings will now appear on map

### Documentation
- [ ] Update internal team docs with new features
- [ ] Document Google Maps API key location (.env)
- [ ] Note any production-specific configurations

---

## 📞 Support

**If deployment fails**:
1. Check error logs in cPanel
2. Restore from backup
3. Review troubleshooting section above
4. Check GitHub commit: `ff5bc90` for reference

**Google Maps Issues**:
- Check API key quotas: https://console.cloud.google.com
- Ensure billing enabled (if quota exceeded)
- Verify domain restrictions allow kejalink.co.ke

**HostAfrica Support**:
- Portal: https://support.hostafrica.co.za
- Email: support@hostafrica.com

---

## ✅ Deployment Complete

Once all tests pass:
- [ ] Mark deployment as successful
- [ ] Document any issues encountered
- [ ] Update team on new features
- [ ] Monitor for 24-48 hours

**Deployed by**: [Your Name]  
**Deployment Date**: __________  
**Deployment Time**: __________  
**Status**: ⏳ Pending / ✅ Success / ❌ Failed  
**Notes**: ___________________

---

**Package Location**: `/home/dennis-muchai/KejaLink/kejalink-frontend-20251116_230924.zip`  
**Backend File**: `/home/dennis-muchai/KejaLink/php-backend/api/listings.php`
