# Google Maps Places Autocomplete Implementation

## Overview
Successfully implemented Google Maps Places Autocomplete for location input in the listing form. This enables:
- Address autocomplete with Google Maps Places API
- Automatic capture of latitude/longitude coordinates
- Auto-population of county and city/neighborhood fields
- Restricted to Kenya addresses only

## Implementation Details

### 1. Components Created

#### PlacesAutocomplete Component (`components/PlacesAutocomplete.tsx`)
- Wrapper around Input component with Google Places Autocomplete
- Props:
  - `value`: Current address value
  - `onChange`: Callback when address text changes
  - `onPlaceSelect`: Callback when place is selected with coordinates
  - `label`, `placeholder`, `required`, `disabled`: Standard form props
- Features:
  - Restricted to Kenya (`componentRestrictions: { country: 'ke' }`)
  - Extracts: formatted_address, latitude, longitude, city, county
  - Returns structured place object: `{ address, latitude, longitude, city?, county? }`

### 2. Updated Files

#### index.html
- Added Google Maps JavaScript API script with Places library:
  ```html
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=%VITE_GOOGLE_MAPS_API_KEY%&libraries=places"></script>
  ```
- Uses `%VITE_GOOGLE_MAPS_API_KEY%` placeholder for environment variable

#### vite.config.ts
- Added `htmlPlugin()` to replace environment variables in HTML during build
- Transforms `%VARIABLE_NAME%` patterns with `process.env.VARIABLE_NAME`

#### types.ts
- Updated `PropertyListing.location` interface:
  ```typescript
  location: {
    address: string;
    county: string;
    neighborhood: string;
    latitude?: number;    // New: matches DB schema
    longitude?: number;   // New: matches DB schema
    lat?: number;         // Legacy support
    lng?: number;         // Legacy support
  }
  ```

#### components/agent/ListingFormModal.tsx
- Replaced standard `Input` for address with `PlacesAutocomplete`
- On place selection:
  - Updates `formData.location.address` with formatted address
  - Sets `latitude` and `longitude` from place geometry
  - Auto-fills `county` and `neighborhood` from address components
  - User can still manually edit county/neighborhood if needed

### 3. Database Schema
Already supports coordinates - confirmed in `mysql_schema.sql`:
```sql
location JSON NOT NULL COMMENT 'Stores: {address, city, county, latitude, longitude}'
```

### 4. API Support
Backend (`php-backend/api/listings.php`) already stores location as JSON, so no backend changes needed. The location JSON will now include:
```json
{
  "address": "123 Kimathi Street, Nairobi",
  "city": "Nairobi",
  "county": "Nairobi",
  "latitude": -1.286389,
  "longitude": 36.817223
}
```

## Testing Instructions

### 1. Development Environment
```bash
# Start frontend (port 5174 or 5173)
npm run dev

# Start backend (port 8888)
cd php-backend && php -S localhost:8888
```

### 2. Test Autocomplete
1. Navigate to http://localhost:5174
2. Login as agent or register new agent account
3. Click "List a Property" button in navbar
4. Start typing in the "Address" field:
   - Try: "Kimathi Street"
   - Try: "Westlands"
   - Try: "Karen"
5. Select an address from dropdown
6. Verify county and neighborhood auto-populate
7. Complete form and submit
8. Check database to confirm lat/lng saved:
   ```bash
   cd php-backend && php -r "
   require_once 'config.local.php';
   \$db = Database::getInstance()->getConnection();
   \$stmt = \$db->query(\"SELECT title, location FROM property_listings ORDER BY created_at DESC LIMIT 1\");
   \$listing = \$stmt->fetch(PDO::FETCH_ASSOC);
   echo 'Title: ' . \$listing['title'] . PHP_EOL;
   echo 'Location: ' . \$listing['location'] . PHP_EOL;
   "
   ```

### 3. Verify Coordinates
Expected location JSON format:
```json
{
  "address": "Kimathi Street, Nairobi",
  "city": "Nairobi", 
  "county": "Nairobi County",
  "latitude": -1.286389,
  "longitude": 36.817223
}
```

## Next Steps

### 1. Update Map Display
Currently `pages/ListingsPage.tsx` or similar page shows "No location data" because existing listings don't have coordinates.

**Update map component to:**
- Read `location.latitude` and `location.longitude` from listings
- Display markers on Google Map for each listing with coordinates
- Handle listings without coordinates gracefully (skip or show placeholder)

**Example map implementation:**
```typescript
// In your map component
const validListings = listings.filter(l => 
  l.location.latitude && l.location.longitude
);

validListings.forEach(listing => {
  new google.maps.Marker({
    position: { 
      lat: listing.location.latitude!, 
      lng: listing.location.longitude! 
    },
    map: map,
    title: listing.title,
  });
});
```

### 2. Migrate Existing Listings
Existing listings in database don't have coordinates. Options:
- **Manual update**: Agents edit each listing to add coordinates
- **Batch geocoding**: Run script to geocode existing addresses
- **Gradual migration**: Coordinates added when listing is next edited

### 3. Enhance Search
Use coordinates for:
- Distance-based search ("within 5km of location")
- Proximity sorting ("nearest to you")
- Map bounds filtering ("show only visible listings")

## Environment Variables

Required in `.env`:
```
VITE_GOOGLE_MAPS_API_KEY=your_api_key_here
```

**Important**: The API key should be stored in `.env` file (not committed to git).

Ensure your API key has:
- ✅ Maps JavaScript API enabled
- ✅ Places API enabled
- ✅ Geocoding API enabled (for reverse geocoding if needed)

## Troubleshooting

### Autocomplete Not Appearing
1. Check console for errors
2. Verify Google Maps API script loaded: `typeof google !== 'undefined'`
3. Confirm API key has Places API enabled
4. Check network tab for API calls to `places.googleapis.com`

### Coordinates Not Saving
1. Check browser console for errors in `onPlaceSelect`
2. Verify `formData.location` structure before submit
3. Check backend API response for errors
4. Query database directly to verify JSON structure

### TypeScript Errors
- Ensure `@types/google.maps` installed: `npm install --save-dev @types/google.maps`
- Add to `tsconfig.json` if needed:
  ```json
  {
    "compilerOptions": {
      "types": ["google.maps"]
    }
  }
  ```

## Files Changed

✅ New files:
- `components/PlacesAutocomplete.tsx`
- `GOOGLE_MAPS_AUTOCOMPLETE_SETUP.md` (this file)

✅ Modified files:
- `index.html` - Added Google Maps script
- `vite.config.ts` - Added HTML variable replacement plugin
- `types.ts` - Added latitude/longitude to location interface
- `components/agent/ListingFormModal.tsx` - Integrated PlacesAutocomplete
- `package.json` - Added @types/google.maps devDependency

✅ No backend changes required - location JSON already supports coordinates

## Deployment

When deploying to production:
1. Ensure `.env` has production Google Maps API key
2. Build: `npm run build`
3. Deploy `dist/` folder
4. Verify API key restrictions in Google Cloud Console
5. Test autocomplete on production site

## Success Criteria

✅ Address autocomplete works in listing form  
✅ Coordinates captured when address selected  
✅ Coordinates saved to database in location JSON  
⏳ Map displays markers using stored coordinates (next step)  
⏳ Existing listings migrated with coordinates (optional)
