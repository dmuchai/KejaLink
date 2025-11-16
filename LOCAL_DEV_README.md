# 🏠 KejaLink Local Development - Quick Start

## TL;DR - Get Running in 5 Minutes

```bash
# 1. Install MySQL (if not already installed)
sudo apt install mysql-server

# 2. Set up the database automatically
./setup-local-db.sh

# 3. Configure backend
cd php-backend
cp config.local.example.php config.local.php
# Edit config.local.php with your MySQL credentials (default: kejalink_dev / dev_password_123)

# 4. Start PHP server (in one terminal)
php -S localhost:8080

# 5. Configure frontend (in another terminal)
cd ..
cp .env.local.example .env.local
# Edit .env.local and add your Google API keys

# 6. Start frontend dev server
npm run dev

# 7. Visit http://localhost:5173
```

## What You Get

- ✅ **Local MySQL database** with full schema
- ✅ **PHP backend API** running on `http://localhost:8080`
- ✅ **React frontend** running on `http://localhost:5173`
- ✅ **Hot reload** on both frontend and backend code changes
- ✅ **Same features** as production (auth, listings, images, etc.)

## File Structure

```
KejaLink/
├── php-backend/
│   ├── config.local.php ← YOUR LOCAL CONFIG (create this)
│   ├── config.local.example.php ← Template to copy
│   └── api/
│       ├── auth.php
│       ├── listings.php
│       └── upload.php
│
├── .env.local ← YOUR LOCAL ENV VARS (create this)
├── .env.local.example ← Template to copy
│
├── setup-local-db.sh ← Run this to create database
└── LOCAL_SETUP_GUIDE.md ← Full detailed instructions
```

## Common Commands

### Start Development
```bash
# Terminal 1: Backend
cd php-backend && php -S localhost:8080

# Terminal 2: Frontend
npm run dev
```

### Database Management
```bash
# Access MySQL CLI
mysql -u kejalink_dev -p kejalink_local

# Re-import schema (if needed)
mysql -u root -p kejalink_local < mysql_schema.sql

# Check tables
mysql -u kejalink_dev -p kejalink_local -e "SHOW TABLES;"
```

### Reset Database
```bash
# Drop and recreate
mysql -u root -p -e "DROP DATABASE kejalink_local; CREATE DATABASE kejalink_local;"
mysql -u root -p kejalink_local < mysql_schema.sql
mysql -u root -p kejalink_local < php-backend/migrations/001_add_password_reset_tokens.sql
```

## API Endpoints (Local)

All endpoints are at `http://localhost:8080/api/`

- `POST /api/auth.php?action=register` - Register user
- `POST /api/auth.php?action=login` - Login
- `POST /api/auth.php?action=refresh` - Refresh token
- `GET /api/listings.php` - Get all listings
- `POST /api/listings.php` - Create listing (agent only)
- `PUT /api/listings.php` - Update listing
- `DELETE /api/listings.php` - Delete listing
- `POST /api/upload.php` - Upload images

## Testing the Setup

### 1. Test Backend Connection
```bash
curl http://localhost:8080/test-endpoints.php
```

Should return database connection status.

### 2. Test User Registration
```bash
curl -X POST http://localhost:8080/api/auth.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "register",
    "email": "agent@test.com",
    "password": "Test123!",
    "full_name": "Test Agent"
  }'
```

Should return: `{"success": true, "token": "..."}` 

### 3. Visit Frontend
Open `http://localhost:5173` - you should see the KejaLink homepage.

Try:
- Registering a new account
- Creating a listing (need agent role)
- Searching for properties

## Making Yourself an Agent

After registering, update your role in the database:

```bash
mysql -u kejalink_dev -p kejalink_local

UPDATE users SET role = 'agent', is_verified_agent = TRUE 
WHERE email = 'your@email.com';
```

Or use phpMyAdmin if you have it installed.

## Troubleshooting

### Port 8080 already in use
```bash
# Use different port
php -S localhost:9000

# Update .env.local
VITE_API_BASE_URL=http://localhost:9000
```

### CORS errors
Check `config.local.php` has:
```php
header('Access-Control-Allow-Origin: http://localhost:5173');
```

### Database connection fails
- Check MySQL is running: `sudo systemctl status mysql`
- Verify credentials in `config.local.php`
- Try connecting manually: `mysql -u kejalink_dev -p`

### Frontend still uses production API
- Make sure `.env.local` exists
- Check `VITE_API_BASE_URL=http://localhost:8080`
- Restart dev server: `npm run dev`
- Check browser console for "API Base URL:" log

## Development Workflow

1. **Make changes** to React components or PHP files
2. **Frontend auto-reloads** (Vite HMR)
3. **Backend changes** take effect immediately (PHP is interpreted)
4. **Test** in browser at `http://localhost:5173`
5. **Commit** changes when ready (don't commit config files!)

## Deployment

When you're ready to deploy:

```bash
# Build production frontend
npm run build

# Upload dist/ to HostAfrica cPanel public_html/
# Backend already on server, no changes needed
```

Production uses `php-backend/config.php` (already configured on server).

## Need Help?

- 📖 Full guide: `LOCAL_SETUP_GUIDE.md`
- 🔍 Check logs: Browser console + terminal output
- 💾 Database: Use phpMyAdmin or MySQL CLI
- 🐛 Issues: Check CORS headers and API URLs

---

**Happy coding! 🚀**
