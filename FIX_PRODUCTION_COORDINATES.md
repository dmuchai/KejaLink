# Fix "Location Not Available" on Production (kejalink.co.ke)

## Problem
Existing listings show "Location not available" because they don't have latitude/longitude coordinates in the database.

## Why This Happened
- Listings were created before Google Maps autocomplete was implemented
- Old listings only have address text, no coordinates
- Maps need latitude/longitude to display markers

## Solutions (Choose One)

---

## ✅ OPTION 1: Manual Update via UI (RECOMMENDED - Most Accurate)

This is the safest and most accurate method since it uses Google's geocoding.

### Steps:

1. **Login to kejalink.co.ke** as an agent
   - Use your agent credentials

2. **Go to your Dashboard**
   - Click on your profile or "Dashboard" link

3. **For each listing without a map**:
   - Click "Edit" button on the listing
   - Find the **Address** field (now has autocomplete)
   - **Clear the address** field
   - **Start typing** the same address (e.g., "Kasarani Mwiki")
   - **Select from dropdown** - this is crucial!
   - County and neighborhood will auto-fill
   - Click **"Update Listing"** or **"Save"**

4. **Verify**:
   - Go back to the listing detail page
   - Map should now display with a marker
   - Check console (F12) - should see latitude/longitude values

### Example for Your Listings:

**Kasarani listing**:
- Edit listing
- Clear address field
- Type "Kasarani Mwiki Rd, Nairobi"
- Select from autocomplete dropdown
- Save

**Kangemi listing**:
- Edit listing  
- Clear address field
- Type "Kangemi Road, Nairobi"
- Select from autocomplete dropdown
- Save

**Repeat for all other listings**

---

## ⚡ OPTION 2: Database Update via phpMyAdmin (Faster but Approximate)

Use this if you have many listings and want a quick fix with approximate coordinates.

### Steps:

1. **Login to cPanel** at https://kejalink.co.ke:2083

2. **Open phpMyAdmin**
   - Find "Databases" section
   - Click "phpMyAdmin"

3. **Select Database**
   - Click on `kejalink_db` in left sidebar

4. **Open SQL Tab**
   - Click "SQL" tab at the top

5. **Run Coordinate Update Script**
   - Open the file: `add-coordinates-production.sql`
   - Copy the entire contents
   - Paste into the SQL textarea
   - Click "Go" button

6. **Verify Results**
   - Should see "4 rows affected" or similar
   - Scroll down to see the SELECT results
   - Check latitude and longitude columns have values

7. **Test on Website**
   - Visit kejalink.co.ke
   - Navigate to a listing
   - Map should now display

### Approximate Coordinates Used:

| Location | Latitude | Longitude |
|----------|----------|-----------|
| Kasarani  | -1.2396  | 36.8936   |
| Kangemi   | -1.2686  | 36.7457   |
| Githurai  | -1.1522  | 36.8936   |
| Kinoo     | -1.2396  | 36.6822   |

**Note**: These are neighborhood-level coordinates. For exact property locations, use Option 1.

---

## 🔍 How to Verify Coordinates Were Added

### Method 1: Browser Console
1. Visit a listing page on kejalink.co.ke
2. Press F12 to open Developer Tools
3. Go to "Console" tab
4. Look for the "Fetched Listing" log
5. Expand the `location` object
6. Check for `latitude` and `longitude` values (should be numbers, not undefined)

### Method 2: Database Query
In phpMyAdmin, run:
```sql
SELECT 
    title,
    JSON_EXTRACT(location, '$.address') as address,
    JSON_EXTRACT(location, '$.latitude') as lat,
    JSON_EXTRACT(location, '$.longitude') as lng
FROM property_listings;
```

Should show coordinates for all listings.

---

## 📊 Expected Results

### Before Fix:
- ❌ Maps show "Location not available"
- ❌ Console shows `latitude: undefined, longitude: undefined`
- ❌ No markers on map

### After Fix:
- ✅ Maps display with red markers
- ✅ Console shows `latitude: -1.2396, longitude: 36.8936` (or similar)
- ✅ Clicking marker shows property info
- ✅ Multiple listings show multiple markers

---

## 🐛 Troubleshooting

### Map still shows "Location not available"
- **Check**: Clear browser cache (Ctrl+Shift+R)
- **Check**: Verify coordinates in database (see verification methods above)
- **Check**: Console for errors (F12)
- **Solution**: Try Option 1 (manual update) - it's more reliable

### Autocomplete not working when editing
- **Check**: Browser console for Google Maps errors
- **Check**: API key is valid (already embedded in build)
- **Solution**: May need to wait a few seconds for Google Maps to load

### SQL script error in phpMyAdmin
- **Error**: "JSON_EXTRACT function not found"
  - **Solution**: Your MySQL version is too old. Use Option 1 instead.
- **Error**: "No rows affected"
  - **Solution**: Address pattern doesn't match. Manually update or use Option 1.

---

## 🎯 Recommended Approach

**For 2-5 listings**: Use **Option 1** (Manual via UI)
- Takes 2 minutes per listing
- Most accurate coordinates
- Uses Google's geocoding
- Safer for production

**For 10+ listings**: Use **Option 2** (SQL Script)
- Quick bulk update
- Approximate coordinates
- Then optionally refine specific listings via UI

---

## 📝 After Adding Coordinates

### For Future Listings
✅ New listings will automatically get coordinates when:
- Agent uses the address autocomplete
- Selects an address from the dropdown
- Saves the listing

### To Improve Existing Coordinates
- Edit any listing
- Use autocomplete to re-select address
- Save - will get more precise coordinates

---

## 🎉 Success Checklist

- [ ] All listings show maps with markers
- [ ] Console shows latitude/longitude values
- [ ] Can click markers to see property info
- [ ] New listings created with autocomplete appear on map immediately
- [ ] Mobile view shows maps correctly

---

**Files Included**:
- `add-coordinates-production.sql` - SQL script for Option 2
- This guide for reference

**Last Updated**: November 16, 2025
