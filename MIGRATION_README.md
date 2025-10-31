# KejaLink Migration Package
## Complete Migration from Supabase to HostAfrica cPanel

This package contains everything you need to migrate the KejaLink application from Supabase to HostAfrica shared hosting.

---

## 📦 Package Contents

### Documentation Files
1. **`MIGRATION_SUMMARY.md`** - Overview and current status
2. **`DEPLOYMENT_GUIDE.md`** - Step-by-step deployment instructions
3. **`MIGRATION_STEP_BY_STEP.md`** - Detailed migration guide
4. **This file** - Quick start guide

### Database Files
5. **`mysql_schema.sql`** - MySQL database schema (ready to import)
6. **`EXPORT_DATA_FROM_SUPABASE.sql`** - Script to export existing data

### Backend Code
7. **`php-backend/`** directory containing:
   - `config.php` - Configuration and database connection
   - `auth.php` - JWT authentication helper
   - `api/auth.php` - Authentication endpoints
   - `api/listings.php` - Listings CRUD endpoints
   - `api/upload.php` - Image upload handler

---

## 🚀 Quick Start (5 Steps)

### Step 1: Read the Documentation (15 minutes)
```bash
1. Start with: MIGRATION_SUMMARY.md (overview)
2. Then read: DEPLOYMENT_GUIDE.md (detailed steps)
```

### Step 2: Prepare HostAfrica (30 minutes)
1. Login to cPanel (https://kejalink.co.ke:2083)
2. Create MySQL database
3. Create database user
4. Import `mysql_schema.sql` via phpMyAdmin

### Step 3: Deploy PHP Backend (20 minutes)
1. Zip the `php-backend` folder
2. Upload to cPanel File Manager (`/public_html/api/`)
3. Extract the zip file
4. Edit `config.php` with your database credentials
5. Create `uploads/` folder with proper permissions (755)

### Step 4: Test API (15 minutes)
```bash
# Test registration
curl -X POST https://kejalink.co.ke/api/auth.php?action=register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@kejalink.co.ke","password":"Test123","full_name":"Test User","role":"tenant"}'

# Test login
curl -X POST https://kejalink.co.ke/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@kejalink.co.ke","password":"Test123"}'

# Test get listings
curl https://kejalink.co.ke/api/listings.php
```

### Step 5: Update Frontend (2-3 hours)
1. Create `src/services/apiClient.ts` (template in DEPLOYMENT_GUIDE.md)
2. Replace Supabase calls with API calls
3. Update authentication flow
4. Test all features
5. Build and deploy

---

## 📋 Checklist

### Pre-Migration
- [ ] Read all documentation
- [ ] Backup current Supabase data
- [ ] Have cPanel login credentials ready
- [ ] Verify PHP version (7.4+ required)
- [ ] Check MySQL version (5.7+ for JSON support)

### Database Setup
- [ ] Created MySQL database in cPanel
- [ ] Created database user with ALL PRIVILEGES
- [ ] Imported `mysql_schema.sql` successfully
- [ ] Verified tables were created (5 tables)
- [ ] Verified admin user exists

### Backend Deployment
- [ ] Uploaded PHP files to `/public_html/api/`
- [ ] Updated `config.php` with correct credentials
- [ ] Changed `JWT_SECRET` to random string
- [ ] Updated `UPLOAD_URL` to your domain
- [ ] Created `uploads/` directory
- [ ] Set proper file permissions (755 for folders, 644 for files)
- [ ] Created `.htaccess` file

### API Testing
- [ ] Test database connection (`test.php`)
- [ ] Test user registration
- [ ] Test user login
- [ ] Test get listings
- [ ] Test create listing (as agent)
- [ ] Test image upload
- [ ] Test update listing
- [ ] Test delete listing

### Frontend Integration
- [ ] Created `apiClient.ts`
- [ ] Updated `useAuth.tsx`
- [ ] Updated `listingService.ts`
- [ ] Replaced all Supabase imports
- [ ] Updated image upload logic
- [ ] Tested authentication flow
- [ ] Tested listing operations
- [ ] Tested search/filters

### Data Migration
- [ ] Exported users from Supabase
- [ ] Exported listings from Supabase
- [ ] Exported images from Supabase
- [ ] Downloaded images from Supabase Storage
- [ ] Imported users to MySQL
- [ ] Imported listings to MySQL
- [ ] Imported images to MySQL
- [ ] Uploaded images to server
- [ ] Sent password reset emails to users

### Final Deployment
- [ ] Built production frontend (`npm run build`)
- [ ] Uploaded to `/public_html/`
- [ ] Configured SSL certificate
- [ ] Tested live site
- [ ] Verified all features work
- [ ] Set up error logging
- [ ] Configured automatic backups

---

## 🔧 Configuration Required

### In `php-backend/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'YOUR_DATABASE_NAME_HERE'); // ← CHANGE THIS
define('DB_USER', 'YOUR_DATABASE_USER_HERE'); // ← CHANGE THIS
define('DB_PASS', 'YOUR_DATABASE_PASSWORD_HERE'); // ← CHANGE THIS
define('JWT_SECRET', 'GENERATE_RANDOM_STRING_HERE'); // ← CHANGE THIS
define('UPLOAD_URL', 'https://kejalink.co.ke/api/uploads/'); // ← VERIFY THIS
```

### Generate JWT Secret:
```bash
# On Linux/Mac:
openssl rand -base64 32

# Or use: https://www.random.org/strings/
```

---

## 📊 API Endpoints Summary

### Authentication
- `POST /api/auth.php?action=register` - Register new user
- `POST /api/auth.php?action=login` - Login user
- `GET /api/auth.php?action=profile` - Get current user (requires auth)

### Listings
- `GET /api/listings.php` - Get all listings (supports filters)
- `GET /api/listings.php?id={id}` - Get single listing
- `POST /api/listings.php` - Create listing (requires agent auth)
- `PUT /api/listings.php?id={id}` - Update listing (requires owner auth)
- `DELETE /api/listings.php?id={id}` - Delete listing (requires owner auth)

### File Upload
- `POST /api/upload.php` - Upload image (requires auth)

---

## 🐛 Troubleshooting

### "Database connection failed"
**Solution**: Check database credentials in `config.php`

### "500 Internal Server Error"
**Solution**: 
1. Check PHP error logs in cPanel
2. Verify file permissions
3. Check `.htaccess` syntax

### "CORS error in browser"
**Solution**: Verify CORS headers in `config.php` are set correctly

### "Upload failed"
**Solution**: 
1. Check `uploads/` folder permissions (755)
2. Verify `upload_max_filesize` in `.htaccess`

### "Unauthorized" errors
**Solution**: Check JWT token is being sent in Authorization header

---

## 📞 Support

### Documentation
- **Main Guide**: `DEPLOYMENT_GUIDE.md`
- **Summary**: `MIGRATION_SUMMARY.md`
- **PHP PDO**: https://www.php.net/manual/en/book.pdo.php
- **JWT**: https://jwt.io/

### HostAfrica
- Login: https://kejalink.co.ke:2083
- Support: Contact via cPanel support ticket system

---

## ⏱️ Estimated Timeline

| Phase | Time Estimate |
|-------|---------------|
| Database Setup | 30 minutes |
| Backend Deployment | 30 minutes |
| API Testing | 30 minutes |
| Frontend Integration | 3-4 hours |
| Data Migration | 2-3 hours |
| Testing | 2-3 hours |
| **Total** | **8-12 hours** |

---

## ✅ Success Criteria

Your migration is complete when:
- ✅ Users can register and login
- ✅ Agents can create/edit/delete listings
- ✅ Images can be uploaded
- ✅ Listings are searchable and filterable
- ✅ All existing data is migrated
- ✅ HTTPS/SSL is working
- ✅ No console errors in browser
- ✅ No PHP errors in logs

---

## 🎯 Next Steps

1. **Read** `MIGRATION_SUMMARY.md` for overview
2. **Follow** `DEPLOYMENT_GUIDE.md` step by step
3. **Test** each component before moving forward
4. **Deploy** when all tests pass
5. **Monitor** for 24-48 hours after launch

---

**Good luck with your migration!** 🚀

If you need help, review the documentation files or check the troubleshooting section.
