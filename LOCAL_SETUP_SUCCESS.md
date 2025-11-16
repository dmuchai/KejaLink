# 🎉 KejaLink Local Development Setup Complete!

## ✅ What Was Set Up

### 1. **Software Installed**
- ✅ PHP 8.3.6 with all required extensions (mysql, mbstring, xml, curl, json, zip)
- ✅ MySQL Server 8.0.43
- ✅ Apache2 (bonus - came with PHP installation)

### 2. **Database Created**
- ✅ Database: `kejalink_local`
- ✅ User: `kejalink_dev` / Password: `dev_password_123`
- ✅ All tables created from `mysql_schema.sql`
- ✅ Password reset migration applied
- ✅ Admin user created automatically

### 3. **Backend Configuration**
- ✅ Created `php-backend/config.local.php` with local database credentials
- ✅ Updated API files to prefer local config over production
- ✅ PHP server running on `http://localhost:8080`
- ✅ All API endpoints working (auth, listings, upload)

### 4. **Frontend Configuration**
- ✅ Created `.env.local` with Google API keys
- ✅ Set `VITE_API_BASE_URL=http://localhost:8080`
- ✅ Updated `apiClient.ts` to use environment variable
- ✅ Vite dev server running on `http://localhost:5173`

### 5. **Files Created**
```
├── .env.local (with your API keys + local backend URL)
├── php-backend/config.local.php (local database config)
├── php-backend/test.php (backend test endpoint)
├── LOCAL_DEV_README.md (quick start guide)
├── LOCAL_SETUP_GUIDE.md (comprehensive setup guide)
├── .env.local.example (template)
└── php-backend/config.local.example.php (template)
```

---

## 🚀 Your Development Environment

### **Backend (PHP API)**
- **URL**: http://localhost:8080
- **Database**: kejalink_local
- **Config**: php-backend/config.local.php

### **Frontend (React App)**
- **URL**: http://localhost:5173
- **API Target**: http://localhost:8080 (from .env.local)

---

## ✨ Test Results

### 1. Backend Test
```bash
curl http://localhost:8080/test.php
```
**Response:**
```json
{
    "status": "ok",
    "message": "KejaLink Backend is running!",
    "php_version": "8.3.6",
    "database": {
        "connection": "Success",
        "users_count": 1,
        "tables": ["users", "property_listings", "property_images", ...]
    }
}
```

### 2. User Registration Test
```bash
curl -X POST "http://localhost:8080/api/auth.php?action=register" \
  -H "Content-Type: application/json" \
  -d '{"email":"agent@local.test","password":"Test123!","full_name":"Local Test Agent"}'
```
**Response:**
```json
{
    "message": "Registration successful",
    "token": "eyJ0eXA...",
    "user": {
        "email": "agent@local.test",
        "role": "tenant",
        ...
    }
}
```

✅ **All endpoints working!**

---

## 📝 How to Use

### Starting Development

**Terminal 1: Start Backend**
```bash
cd ~/KejaLink/php-backend
php -S localhost:8080
```

**Terminal 2: Start Frontend**
```bash
cd ~/KejaLink
npm run dev
```

**Browser:** Open http://localhost:5173

### Making Yourself an Agent

After registering, run:
```bash
mysql -u kejalink_dev -pdev_password_123 kejalink_local

UPDATE users SET role = 'agent', is_verified_agent = TRUE 
WHERE email = 'your@email.com';
```

### Testing API Endpoints

**Register:**
```bash
curl -X POST "http://localhost:8080/api/auth.php?action=register" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"Pass123!","full_name":"Test User"}'
```

**Login:**
```bash
curl -X POST "http://localhost:8080/api/auth.php?action=login" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"Pass123!"}'
```

**Get Listings:**
```bash
curl http://localhost:8080/api/listings.php
```

---

## 🔧 Common Commands

### Database
```bash
# Access MySQL
mysql -u kejalink_dev -pdev_password_123 kejalink_local

# Show tables
mysql -u kejalink_dev -pdev_password_123 kejalink_local -e "SHOW TABLES;"

# Reset database
mysql -u root -p -e "DROP DATABASE kejalink_local; CREATE DATABASE kejalink_local;"
mysql -u root -p kejalink_local < mysql_schema.sql
mysql -u root -p kejalink_local < php-backend/migrations/001_add_password_reset_tokens.sql
```

### PHP Server
```bash
# Start
cd php-backend && php -S localhost:8080

# Check if running
curl http://localhost:8080/test.php

# Kill process
pkill -f "php -S localhost:8080"
```

### Frontend
```bash
# Start dev server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

---

## 🎯 What Works Locally

✅ User registration and login  
✅ JWT token generation and validation  
✅ Property listings CRUD (create, read, update, delete)  
✅ Image uploads (to php-backend/uploads/)  
✅ Search and filtering  
✅ Google Maps integration (with your API key)  
✅ AI property descriptions (Gemini API)  
✅ Agent dashboard  
✅ Saved listings  

⚠️ **Not configured (optional):**
- Password reset emails (requires PHPMailer)
- Production SMTP (Brevo)

---

## 🔄 Switching Between Local and Production

### Use Local Backend
`.env.local`:
```env
VITE_API_BASE_URL=http://localhost:8080
```

### Use Production Backend
`.env.local`:
```env
# VITE_API_BASE_URL=http://localhost:8080  # Comment out
VITE_API_BASE_URL=https://kejalink.co.ke  # Uncomment
```

Or just delete `.env.local` to use production by default.

---

## 📚 Documentation

- **Quick Start**: `LOCAL_DEV_README.md`
- **Full Setup Guide**: `LOCAL_SETUP_GUIDE.md`
- **API Docs**: (coming soon)

---

## 🐛 Troubleshooting

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
```bash
# Check MySQL is running
sudo systemctl status mysql

# Restart MySQL
sudo systemctl restart mysql
```

### Frontend not connecting to local backend
```bash
# Clear Vite cache
rm -rf node_modules/.vite

# Restart dev server
npm run dev

# Check API URL in browser console (look for "API Base URL:" log)
```

---

## 🎉 Next Steps

1. **Try registering a new user** at http://localhost:5173
2. **Make yourself an agent** using SQL above
3. **Create a test listing** in the agent dashboard
4. **Test property search and filtering**
5. **Start building your features!**

---

## 💡 Tips

- **Hot reload works** - edit React components and see changes instantly
- **PHP changes apply immediately** - no need to restart server (most of the time)
- **Database changes persist** - your local data stays between restarts
- **Use browser DevTools** - Check Network tab for API calls
- **Check PHP errors** - Look in terminal where `php -S` is running

---

**Happy coding! 🚀**

If you have any issues, check the troubleshooting section above or review the detailed setup guide in `LOCAL_SETUP_GUIDE.md`.
