#!/bin/bash

# Frontend Deployment Script for KejaLink
# Prepares files for upload to production server

echo "================================"
echo "KejaLink Frontend Deployment"
echo "================================"
echo ""

# Check if dist folder exists
if [ ! -d "dist" ]; then
    echo "❌ Error: dist/ folder not found!"
    echo "Run 'npm run build' first"
    exit 1
fi

echo "✅ Found dist/ folder"
echo ""

# Create deployment package
DEPLOY_DIR="deploy-package"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
PACKAGE_NAME="kejalink-frontend-${TIMESTAMP}"

echo "📦 Creating deployment package..."
rm -rf "$DEPLOY_DIR"
mkdir -p "$DEPLOY_DIR"

# Copy dist contents
cp -r dist/* "$DEPLOY_DIR/"

echo "✅ Package created in: $DEPLOY_DIR/"
echo ""

# Create archive for easy upload
echo "📦 Creating ZIP archive..."
cd "$DEPLOY_DIR" || exit
zip -r "../${PACKAGE_NAME}.zip" .
cd ..

echo "✅ Archive created: ${PACKAGE_NAME}.zip"
echo ""

# Show contents
echo "📋 Package contents:"
ls -lh "$DEPLOY_DIR"
echo ""

# Show instructions
echo "================================"
echo "📤 UPLOAD INSTRUCTIONS"
echo "================================"
echo ""
echo "Option 1: Upload ZIP via cPanel"
echo "  1. Login to cPanel File Manager"
echo "  2. Navigate to /public_html/"
echo "  3. Upload: ${PACKAGE_NAME}.zip"
echo "  4. Right-click → Extract"
echo "  5. Delete the ZIP file"
echo ""
echo "Option 2: Upload Individual Files"
echo "  1. Login to cPanel File Manager"
echo "  2. Navigate to /public_html/"
echo "  3. Upload files from: $DEPLOY_DIR/"
echo "     - index.html (replace existing)"
echo "     - assets/ folder (replace existing)"
echo "     - favicon.ico"
echo "     - vite.svg"
echo ""
echo "⚠️  IMPORTANT: Do NOT delete these folders:"
echo "  - api/"
echo "  - phpmailer/"
echo "  - uploads/"
echo ""
echo "================================"
echo "✅ Deployment package ready!"
echo "================================"
