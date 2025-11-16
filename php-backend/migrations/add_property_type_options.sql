-- ============================================
-- MIGRATION: Update Property Type Options
-- ============================================
-- Add new property types: apartment, studio, bedsitter, bungalow, maisonette, townhouse
-- Run this in phpMyAdmin or MySQL CLI

-- Step 1: Update the ENUM column to include new property types
ALTER TABLE property_listings 
MODIFY COLUMN property_type ENUM(
    'apartment', 
    'studio', 
    'bedsitter', 
    'bungalow', 
    'maisonette', 
    'townhouse'
) COMMENT 'Property type classification';

-- Step 2: Optionally migrate any existing data (if needed)
-- If you have existing 'house' values, you might want to convert them to 'bungalow' or 'maisonette'
-- UPDATE property_listings SET property_type = 'bungalow' WHERE property_type = 'house';
-- UPDATE property_listings SET property_type = 'apartment' WHERE property_type = 'commercial';

-- Step 3: Add index for better filtering performance
-- Drop if exists, then create
ALTER TABLE property_listings ADD INDEX idx_listings_property_type (property_type);

-- Verify the change
SHOW COLUMNS FROM property_listings LIKE 'property_type';
