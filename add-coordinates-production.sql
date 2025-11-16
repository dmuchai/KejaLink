-- SQL Script to Add Coordinates to Existing Production Listings
-- Run this in phpMyAdmin on HostAfrica cPanel
-- Database: kejalink_db

-- INSTRUCTIONS:
-- 1. Login to cPanel → phpMyAdmin
-- 2. Select kejalink_db database
-- 3. Click "SQL" tab
-- 4. Copy and paste this entire script
-- 5. Click "Go" to execute

-- First, let's see what we have
SELECT id, title, location FROM property_listings;

-- Update Kasarani Mwiki Rd listing (approximate coordinates for Kasarani, Nairobi)
UPDATE property_listings 
SET location = JSON_SET(
    location,
    '$.latitude', -1.2396,
    '$.longitude', 36.8936
)
WHERE JSON_EXTRACT(location, '$.address') LIKE '%Kasarani%'
AND (JSON_EXTRACT(location, '$.latitude') IS NULL OR JSON_EXTRACT(location, '$.latitude') = '');

-- Update Kangemi Road listing (approximate coordinates for Kangemi, Nairobi)
UPDATE property_listings 
SET location = JSON_SET(
    location,
    '$.latitude', -1.2686,
    '$.longitude', 36.7457
)
WHERE JSON_EXTRACT(location, '$.address') LIKE '%Kangemi%'
AND (JSON_EXTRACT(location, '$.latitude') IS NULL OR JSON_EXTRACT(location, '$.latitude') = '');

-- Update Githurai listing (if exists - approximate coordinates)
UPDATE property_listings 
SET location = JSON_SET(
    location,
    '$.latitude', -1.1522,
    '$.longitude', 36.8936
)
WHERE JSON_EXTRACT(location, '$.address') LIKE '%Githurai%'
AND (JSON_EXTRACT(location, '$.latitude') IS NULL OR JSON_EXTRACT(location, '$.latitude') = '');

-- Update Kinoo listing (if exists - approximate coordinates)
UPDATE property_listings 
SET location = JSON_SET(
    location,
    '$.latitude', -1.2396,
    '$.longitude', 36.6822
)
WHERE JSON_EXTRACT(location, '$.address') LIKE '%Kinoo%'
AND (JSON_EXTRACT(location, '$.latitude') IS NULL OR JSON_EXTRACT(location, '$.latitude') = '');

-- Verify the updates
SELECT 
    id,
    title,
    JSON_EXTRACT(location, '$.address') as address,
    JSON_EXTRACT(location, '$.latitude') as latitude,
    JSON_EXTRACT(location, '$.longitude') as longitude
FROM property_listings;

-- Notes:
-- These are approximate coordinates for the neighborhoods
-- For more accurate coordinates, use the UI autocomplete to re-save each listing
-- Coordinates format: latitude (North/South), longitude (East/West)
