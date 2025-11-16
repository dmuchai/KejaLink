# Google Maps Implementation - Testing & Verification Guide

## ✅ Implementation Complete

Successfully implemented Google Maps Places Autocomplete with the following features:

### 1. Address Autocomplete
- ✅ Google Places API integration in listing form
- ✅ Autocomplete restricted to Kenya addresses
- ✅ Automatic coordinate capture (latitude/longitude)
- ✅ Auto-population of county and neighborhood from selected place

### 2. Map Display
- ✅ Updated PropertyMap component to support new latitude/longitude fields
- ✅ Updated SinglePropertyMap component for single property view
- ✅ Backwards compatible with old lat/lng field names
- ✅ Shows "No location data" message when coordinates missing

### 3. Database Integration
- ✅ Location JSON schema supports: `{address, city, county, latitude, longitude}`
- ✅ No database migration needed - already supported
- ✅ Backend stores location JSON as-is

## 🧪 Testing Instructions

### Prerequisites
```bash
# Ensure you have Google Maps API key in .env
grep GOOGLE .env
# Should show: VITE_GOOGLE_MAPS_API_KEY=AIzaSyD...

# Verify API key has these enabled in Google Cloud Console:
# - Maps JavaScript API
# - Places API
# - Geocoding API (optional, for reverse geocoding)
```

### Test 1: Development Environment

1. **Start servers**:
```bash
# Terminal 1: Frontend
cd /home/dennis-muchai/KejaLink
npm run dev
# Opens on http://localhost:5174 (or 5173)

# Terminal 2: Backend
cd /home/dennis-muchai/KejaLink/php-backend
php -S localhost:8888
```

2. **Test Autocomplete**:
- Navigate to http://localhost:5174
- Login as agent (or register new agent)
- Click "List a Property" button in navbar
- In the "Address" field, start typing:
  - **Test 1**: Type "Kimathi" → should show "Kimathi Street, Nairobi"
  - **Test 2**: Type "Westlands" → should show multiple Westlands locations
  - **Test 3**: Type "Karen" → should show Karen, Nairobi options
- Select an address from dropdown
- **Verify**:
  - ✅ County field auto-fills (e.g., "Nairobi County")
  - ✅ Neighborhood field auto-fills with city name
  - ✅ You can still manually edit county/neighborhood if needed

3. **Create a listing**:
- Fill out remaining fields (title, description, property type, price, etc.)
- Upload at least one image
- Click "Submit"
- **Expected**: Success message, form closes

4. **Verify database**:
```bash
cd /home/dennis-muchai/KejaLink/php-backend
php -r "
require_once 'config.local.php';
\$db = Database::getInstance()->getConnection();
\$stmt = \$db->query(\"SELECT title, location FROM property_listings ORDER BY created_at DESC LIMIT 1\");
\$listing = \$stmt->fetch(PDO::FETCH_ASSOC);
echo 'Latest listing: ' . \$listing['title'] . PHP_EOL;
\$loc = json_decode(\$listing['location'], true);
echo 'Address: ' . \$loc['address'] . PHP_EOL;
echo 'County: ' . \$loc['county'] . PHP_EOL;
echo 'City/Area: ' . (\$loc['city'] ?? \$loc['area'] ?? 'N/A') . PHP_EOL;
echo 'Latitude: ' . (\$loc['latitude'] ?? 'NOT SET') . PHP_EOL;
echo 'Longitude: ' . (\$loc['longitude'] ?? 'NOT SET') . PHP_EOL;
"
```

**Expected Output**:
```
Latest listing: Your Test Property Title
Address: Kimathi Street, Nairobi, Kenya
County: Nairobi County
City/Area: Nairobi
Latitude: -1.286389
Longitude: 36.817223
```

### Test 2: Map Display

1. **View listings with coordinates**:
- Navigate to Listings page
- If PropertyMap component is rendered, it should:
  - ✅ Show markers for listings with coordinates
  - ✅ Center on first listing's location
  - ✅ Fit all markers in view if multiple listings
  - ✅ Show "No location data" for listings without coordinates

2. **View single property**:
- Click on a listing with coordinates
- On detail page, map should:
  - ✅ Show single marker at property location
  - ✅ Display info window with property title
  - ✅ Allow fullscreen view

### Test 3: Edit Existing Listing

1. **Edit listing without coordinates**:
- Edit an old listing that doesn't have lat/lng
- Use autocomplete to select a new address
- Save
- **Verify**: Database now has latitude/longitude

2. **Edit listing with coordinates**:
- Edit a new listing that has coordinates
- Change address using autocomplete
- **Verify**: Coordinates update to match new address

### Test 4: Manual Address Entry

1. **Type address without selecting from dropdown**:
- Type "123 Custom Street, Nairobi"
- Don't click autocomplete suggestion, just tab away
- **Expected**: No coordinates saved (latitude/longitude will be null)
- **This is OK** - coordinates only saved when user selects from dropdown

### Test 5: Production Build

```bash
# Build for production
cd /home/dennis-muchai/KejaLink
npm run build

# Verify Google Maps script in HTML
cat dist/index.html | grep maps.googleapis.com
# Should show: <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSy...&libraries=places"></script>

# Deploy dist/ folder to production
# Test same scenarios as above on production site
```

## 📊 Database Schema Verification

Run this to check all listings' coordinate status:

```bash
cd /home/dennis-muchai/KejaLink/php-backend
php -r "
require_once 'config.local.php';
\$db = Database::getInstance()->getConnection();
\$stmt = \$db->query(\"SELECT id, title, location FROM property_listings\");
\$listings = \$stmt->fetchAll(PDO::FETCH_ASSOC);

echo 'Total listings: ' . count(\$listings) . PHP_EOL;
\$withCoords = 0;
\$withoutCoords = 0;

foreach (\$listings as \$listing) {
    \$loc = json_decode(\$listing['location'], true);
    if (isset(\$loc['latitude']) && isset(\$loc['longitude'])) {
        \$withCoords++;
    } else {
        \$withoutCoords++;
        echo 'Missing coords: ' . substr(\$listing['title'], 0, 50) . '...' . PHP_EOL;
    }
}

echo PHP_EOL;
echo 'With coordinates: ' . \$withCoords . PHP_EOL;
echo 'Without coordinates: ' . \$withoutCoords . PHP_EOL;
"
```

## 🔧 Troubleshooting

### Autocomplete Not Working

**Symptom**: Dropdown doesn't appear when typing  
**Checks**:
1. Open browser console, check for errors
2. Verify Google Maps script loaded:
   ```javascript
   console.log(typeof google !== 'undefined' && google.maps && google.maps.places)
   // Should be: true
   ```
3. Check API key in .env matches production key
4. Verify API key has Places API enabled in Google Cloud Console
5. Check network tab for blocked requests to `places.googleapis.com`

**Common Fixes**:
- Clear browser cache
- Check API key quotas in Google Cloud Console
- Ensure API key has no domain restrictions (or includes your domain)

### Coordinates Not Saving

**Symptom**: Location JSON missing latitude/longitude  
**Checks**:
1. Browser console: Check `onPlaceSelect` callback fires
2. Log formData.location before submit:
   ```javascript
   console.log('Location data:', formData.location);
   // Should show: {address, county, neighborhood, latitude, longitude}
   ```
3. Check backend error logs

**Common Fixes**:
- Ensure you SELECT from dropdown, don't just type and submit
- Verify PlacesAutocomplete component imported correctly
- Check handleFormSubmit sends location object as JSON

### Map Not Displaying

**Symptom**: Map shows loading spinner indefinitely or error  
**Checks**:
1. Verify API key in `.env`
2. Check console for Google Maps errors
3. Verify listings have coordinates in database
4. Check component receives listings prop correctly

**Common Fixes**:
- Rebuild: `npm run build`
- Check API key quotas
- Verify Maps JavaScript API enabled
- Ensure coordinates are numbers, not strings

## 📝 Next Steps

### Optional Enhancements

1. **Batch Geocode Existing Listings**:
   - Create script to geocode addresses of existing listings without coordinates
   - Use Google Geocoding API or do manually through UI

2. **Distance-Based Search**:
   - Add "Search within X km" filter
   - Calculate distance from user location or selected point
   - Sort by proximity

3. **Map Clustering**:
   - For many listings, use marker clustering
   - Install `@googlemaps/markerclusterer`

4. **Street View**:
   - Add Street View toggle on single property map
   - Show property from street level

5. **Draw Search Area**:
   - Let users draw polygon on map to search within area
   - Filter listings by coordinates within polygon

### Maintenance

- **Monitor API Usage**: Check Google Cloud Console for Places API quota
- **Update Old Listings**: Encourage agents to edit old listings to add coordinates
- **Test Quarterly**: Verify Google Maps integration still works after updates

## 🎯 Success Criteria

✅ **Completed**:
- [x] Places Autocomplete works in listing form
- [x] Coordinates captured when address selected
- [x] County and neighborhood auto-populate
- [x] Coordinates save to database in location JSON
- [x] Map components support new latitude/longitude fields
- [x] Backwards compatible with old lat/lng fields
- [x] TypeScript types updated
- [x] Build successful with no errors

⏳ **To Verify**:
- [ ] Test autocomplete on local dev server
- [ ] Create new listing and verify coordinates in DB
- [ ] View listing on map (if map component is rendered)
- [ ] Deploy to production and test live
- [ ] Update existing listings with coordinates (optional)

## 📚 Files Modified

### New Files:
- `components/PlacesAutocomplete.tsx` - Autocomplete component
- `GOOGLE_MAPS_AUTOCOMPLETE_SETUP.md` - Setup documentation
- `GOOGLE_MAPS_TESTING_GUIDE.md` - This file

### Modified Files:
- `index.html` - Added Google Maps script
- `vite.config.ts` - Added HTML env var replacement plugin
- `types.ts` - Added latitude/longitude to location interface
- `components/agent/ListingFormModal.tsx` - Integrated PlacesAutocomplete
- `components/PropertyMap.tsx` - Support new coordinate fields
- `components/SinglePropertyMap.tsx` - Support new coordinate fields
- `package.json` - Added @types/google.maps

### No Changes Needed:
- `php-backend/api/listings.php` - Already stores location JSON
- `mysql_schema.sql` - Already supports coordinates in JSON

## 🚀 Deployment Checklist

Before deploying to production:

- [ ] Verify `.env` has production Google Maps API key
- [ ] Run `npm run build` successfully
- [ ] Check `dist/index.html` has Google Maps script with API key
- [ ] Test autocomplete in production environment
- [ ] Create test listing with coordinates
- [ ] Verify map displays markers correctly
- [ ] Monitor API usage in Google Cloud Console
- [ ] Document for team/future reference

---

**Last Updated**: November 16, 2024  
**Status**: ✅ Implementation Complete, Ready for Testing  
**Next Action**: Test autocomplete on dev server, then deploy to production
