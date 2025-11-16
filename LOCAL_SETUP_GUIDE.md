# KejaLink Local Development Setup Guide

## Prerequisites

Install one of the following local server environments:

### Option 1: XAMPP (Recommended for Linux)
```bash
# Download XAMPP for Linux from https://www.apachefriends.org/
# Or install via terminal:
wget https://downloadsapachefriends.global.ssl.fastly.net/8.2.12/xampp-linux-x64-8.2.12-0-installer.run
chmod +x xampp-linux-x64-8.2.12-0-installer.run
sudo ./xampp-linux-x64-8.2.12-0-installer.run
```

### Option 2: Install PHP & MySQL Separately
```bash
# Install PHP 8.x with required extensions
sudo apt update
sudo apt install php php-cli php-mysql php-mbstring php-xml php-curl php-json php-zip

# Install MySQL Server
sudo apt install mysql-server

# Install Apache (optional, can use PHP built-in server)
sudo apt install apache2
```

---

## Step 1: Create Local Database

### Using XAMPP phpMyAdmin:
1. Start XAMPP: `sudo /opt/lampp/lampp start`
2. Open browser: `http://localhost/phpmyadmin`
3. Click "New" to create database
4. Database name: `kejalink_local`
5. Collation: `utf8mb4_unicode_ci`
6. Click "Create"

### Using Command Line:
```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE kejalink_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user (optional, for security)
CREATE USER 'kejalink_dev'@'localhost' IDENTIFIED BY 'dev_password_123';
GRANT ALL PRIVILEGES ON kejalink_local.* TO 'kejalink_dev'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Step 2: Import Database Schema

### Via phpMyAdmin:
1. Select `kejalink_local` database
2. Click "Import" tab
3. Choose file: `/home/dennis-muchai/KejaLink/mysql_schema.sql`
4. Click "Go"

### Via Command Line:
```bash
cd /home/dennis-muchai/KejaLink
mysql -u root -p kejalink_local < mysql_schema.sql

# Also run the password reset migration
mysql -u root -p kejalink_local < php-backend/migrations/001_add_password_reset_tokens.sql
```

---

## Step 3: Configure Local Backend

Create a local config file:

```bash
cd /home/dennis-muchai/KejaLink/php-backend
cp config.php config.local.php
```

Edit `config.local.php` with your local database credentials:

```php
<?php
// LOCAL DEVELOPMENT CONFIG - DO NOT COMMIT TO GIT!

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (php_sapi_name() !== 'cli') {
    header('Access-Control-Allow-Origin: http://localhost:5173'); // Vite dev server
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=UTF-8');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// LOCAL DATABASE CONFIGURATION
define('DB_HOST', 'localhost');
define('DB_NAME', 'kejalink_local');
define('DB_USER', 'root'); // or 'kejalink_dev' if you created a user
define('DB_PASS', ''); // your MySQL root password or 'dev_password_123'
define('DB_CHARSET', 'utf8mb4');

// JWT CONFIGURATION (use a different secret for local dev)
define('JWT_SECRET', 'local-dev-secret-key-not-for-production');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRY', 604800);

// LOCAL APPLICATION URL
define('APP_URL', 'http://localhost:5173');
?>
```

**Important:** Add `config.local.php` to your `.gitignore`!

---

## Step 4: Start Local PHP Server

### Option A: PHP Built-in Server (Simplest)
```bash
cd /home/dennis-muchai/KejaLink/php-backend
php -S localhost:8080
```

Your API will be available at: `http://localhost:8080/api/`

### Option B: Using XAMPP
1. Copy entire `php-backend` folder to `/opt/lampp/htdocs/kejalink-api/`
2. Rename `config.local.php` to `config.php` in that directory
3. Access at: `http://localhost/kejalink-api/api/`

---

## Step 5: Update Frontend API URL

Create a `.env.local` file in the project root:

```bash
cd /home/dennis-muchai/KejaLink
```

Create `.env.local`:

```env
# Google API Keys (get these from your .env file)
VITE_GEMINI_API_KEY=your_gemini_key_here
VITE_GOOGLE_MAPS_API_KEY=your_maps_key_here

# LOCAL BACKEND URL (use the URL from Step 4)
VITE_API_BASE_URL=http://localhost:8080
```

Update `services/apiClient.ts` to use environment variable:

```typescript
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'https://kejalink.co.ke';
```

---

## Step 6: Test Local Setup

### Test Backend Connection:
```bash
# Test database connection
curl http://localhost:8080/test-endpoints.php

# Test auth endpoint
curl -X POST http://localhost:8080/api/auth.php \
  -H "Content-Type: application/json" \
  -d '{"action":"register","email":"test@example.com","password":"test123","full_name":"Test User"}'
```

### Start Frontend Dev Server:
```bash
cd /home/dennis-muchai/KejaLink
npm run dev
```

Visit `http://localhost:5173` - your app should now use the local backend!

---

## Step 7: Create Test Data

### Create Agent User:
```sql
-- Login to MySQL
mysql -u root -p kejalink_local

-- Insert test agent
INSERT INTO users (id, email, password_hash, full_name, role, is_verified_agent) 
VALUES (
  UUID(), 
  'agent@test.com', 
  '$2y$10$abcdefghijklmnopqrstuvwxyz', -- Password: "password123"
  'Test Agent', 
  'agent', 
  TRUE
);
```

Or register via the UI and manually update the `role` to 'agent' in phpMyAdmin.

---

## Common Issues & Solutions

### Issue: "Connection refused" on port 8080
**Solution:** Port may be in use. Use different port:
```bash
php -S localhost:9000
# Update VITE_API_BASE_URL to http://localhost:9000
```

### Issue: "Access denied for user 'root'@'localhost'"
**Solution:** Check MySQL password or reset it:
```bash
sudo mysql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_password';
FLUSH PRIVILEGES;
EXIT;
```

### Issue: CORS errors in browser console
**Solution:** Ensure `config.local.php` has correct CORS headers for `http://localhost:5173`

### Issue: Frontend still hitting production API
**Solution:** Clear `.env` cache and rebuild:
```bash
rm -rf node_modules/.vite
npm run dev
```

---

## Folder Structure After Setup

```
KejaLink/
├── php-backend/
│   ├── config.php (production - DO NOT EDIT)
│   ├── config.local.php (local dev - NOT COMMITTED)
│   └── api/
│       ├── auth.php
│       ├── listings.php
│       └── upload.php
├── .env (production keys - NOT COMMITTED)
├── .env.local (local dev override - NOT COMMITTED)
└── services/
    └── apiClient.ts (updated to read env var)
```

---

## Development Workflow

1. **Start MySQL**: `sudo /opt/lampp/lampp startmysql` (if using XAMPP)
2. **Start PHP server**: `cd php-backend && php -S localhost:8080`
3. **Start Vite dev server**: `npm run dev` (in another terminal)
4. **Code & test**: Changes auto-reload on both frontend and backend
5. **Stop servers**: Ctrl+C in each terminal

---

## Deployment to Production

When you're ready to deploy changes:

1. Test locally first
2. Commit code changes (NOT config files)
3. Upload to HostAfrica cPanel via File Manager
4. Use production `config.php` on server (already configured)

---

## Security Notes

✅ **Never commit these files:**
- `config.local.php`
- `.env.local`
- Any file with real passwords/API keys

✅ **Add to `.gitignore`:**
```
php-backend/config.local.php
.env.local
```

✅ **Use different secrets for local vs production**
