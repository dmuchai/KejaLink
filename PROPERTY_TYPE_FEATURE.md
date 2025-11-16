# Property Type Feature - Production Deployment

## Overview
Added "Property Type" filter feature with options: Apartment, Studio, Bedsitter, Bungalow, Maisonette, Townhouse.

## Database Migration Required

### Step 1: Run Migration on Production Database

**Option A: Via phpMyAdmin (HostAfrica cPanel)**
1. Log into cPanel → phpMyAdmin
2. Select your `kejalink` database
3. Go to the SQL tab
4. Copy and paste the contents of `php-backend/migrations/add_property_type_options.sql`
5. Click "Go" to execute

**Option B: Via SSH/CLI**
```bash
cd public_html/api
mysql -u your_db_user -p your_db_name < migrations/add_property_type_options.sql
```

### Step 2: Deploy Updated Files

Upload these files to production:
- `php-backend/api/listings.php` (updated with property_type filter)
- Frontend `dist/` folder (entire rebuilt bundle)

### Step 3: Verify

1. Test creating a new listing - should see "Property Type" dropdown
2. Test search - should see "Property Type" filter in search bar
3. Existing listings without property_type will show as NULL (optional field)

## Feature Details

### Frontend Changes
- **Search Bar**: New "Property Type" dropdown filter
- **Create/Edit Listing Form**: Required "Property Type" field
- **TypeScript Types**: Added `PropertyType` union type
- **Constants**: Added `PropertyTypes` array for dropdown options

### Backend Changes
- **Database**: `property_listings.property_type` ENUM column
- **API**: `GET /api/listings.php?property_type=apartment` filter support
- **Validation**: Property type must be one of: apartment, studio, bedsitter, bungalow, maisonette, townhouse

### API Usage Examples

**Filter by property type:**
```
GET /api/listings.php?property_type=apartment
```

**Combined filters:**
```
GET /api/listings.php?property_type=studio&bedrooms=1&county=Nairobi
```

**Create listing with property type:**
```json
POST /api/listings.php
{
  "title": "Modern Studio in Kilimani",
  "property_type": "studio",
  "bedrooms": 0,
  ...
}
```

## Notes
- Property type is **required** for new listings created via the form
- Existing listings may have NULL property_type (backward compatible)
- Case-insensitive matching in the database
- Index added for better filtering performance
