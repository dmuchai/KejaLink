#!/bin/bash
# Upload fixed PHP files to production server

echo "🚀 Uploading fixed files to kejalink.co.ke..."

# Replace these with your actual cPanel credentials
SERVER="kejalink@da23.host-ww.net"
REMOTE_PATH="~/public_html/api"

echo ""
echo "📝 Files to upload:"
echo "  1. php-backend/config.php (display_errors fixed)"
echo "  2. php-backend/api/listings.php (null handling fixed)"
echo ""

# If you have SSH key setup:
echo "Option 1 - Using SCP (if you have SSH access):"
echo "scp php-backend/config.php $SERVER:$REMOTE_PATH/config.php"
echo "scp php-backend/api/listings.php $SERVER:$REMOTE_PATH/api/listings.php"
echo ""

# Alternative: Create a zip file for manual upload
echo "Option 2 - Creating ZIP file for cPanel upload:"
cd php-backend
zip -r ../production-fix.zip config.php api/listings.php
cd ..
echo "✅ Created production-fix.zip"
echo ""
echo "Upload via cPanel File Manager:"
echo "1. Go to https://kejalink.co.ke:2083"
echo "2. Open File Manager"
echo "3. Navigate to /public_html/api/"
echo "4. Upload and extract production-fix.zip"
echo "5. Overwrite existing files"
echo ""

echo "🔍 After uploading, test with:"
echo "curl -s https://kejalink.co.ke/api/api/listings.php | jq"
echo ""
echo "Expected: Clean JSON response with no PHP errors"
