# KejaLink Migration Guide: Supabase → HostAfrica cPanel
## Complete Step-by-Step Migration Plan

**Date Started:** October 30, 2025  
**Engineer:** Senior Full-Stack Developer  
**Timeline:** ~2-3 weeks  

---

## 📋 Table of Contents
1. [Current Architecture Assessment](#phase-1-assessment)
2. [Database Migration](#phase-2-database)
3. [PHP Backend API](#phase-3-backend-api)
4. [Authentication System](#phase-4-authentication)
5. [File Storage](#phase-5-file-storage)
6. [Frontend Integration](#phase-6-frontend)
7. [Data Migration](#phase-7-data-migration)
8. [Testing & Deployment](#phase-8-deployment)

---

## Phase 1: Current Architecture Assessment ✅

### What We're Migrating FROM (Supabase):

**1. Authentication:**
- Supabase Auth (email/password)
- JWT tokens managed by Supabase
- Session persistence & auto-refresh
- User metadata storage

**2. Database (PostgreSQL):**
- **users** table (4 columns core: id, email, full_name, role)
- **property_listings** table (15 columns, JSONB for location/amenities)
- **property_images** table (image URLs, display order, AI scan data)
- **saved_listings** table (user favorites)
- UUID primary keys
- Foreign key constraints
- Triggers for auto-updating timestamps

**3. Storage:**
- Supabase Storage bucket: `listing-images`
- Public read access
- Authenticated write access
- Direct URL access to images

**4. Security:**
- Row Level Security (RLS) policies
- Public read, authenticated write
- Agent-only listing creation/editing

### What We're Migrating TO (HostAfrica):

**1. Authentication:**
- ✅ PHP-based JWT authentication
- ✅ bcrypt password hashing
- ✅ Session management via cookies/localStorage
- ✅ Custom user registration/login endpoints

**2. Database (MySQL/MariaDB):**
- ✅ Convert PostgreSQL → MySQL schema
- ✅ Replace UUID with CHAR(36) or auto-increment IDs
- ✅ Convert JSONB → JSON columns
- ✅ Replicate foreign keys & indexes

**3. Storage:**
- ✅ cPanel File Manager storage
- ✅ PHP upload handling
- ✅ Public `/uploads/` directory
- ✅ File validation & security

**4. Security:**
- ✅ API authentication middleware
- ✅ CORS configuration
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ File upload validation

---

## Phase 2: Database Migration Setup 🚀

### Step 2.1: Access cPanel MySQL

1. **Login to HostAfrica cPanel**
   - URL: `https://kejalink.co.ke:2083` or provided by HostAfrica
   - Username: Your cPanel username
   - Password: Your cPanel password

2. **Navigate to MySQL Databases**
   - cPanel → Databases section → "MySQL Databases"

3. **Create New Database**
   ```
   Database Name: kejalink_db
   ```
   - cPanel will prefix it with your username (e.g., `cpanelusername_kejalink_db`)
   - **Note down the FULL database name**

4. **Create Database User**
   ```
   Username: kejalink_user
   Password: [Generate strong password - save it!]
   ```
   - **Note down the FULL username** (e.g., `cpanelusername_kejalink_user`)

5. **Grant ALL PRIVILEGES**
   - Add user to database
   - Check "ALL PRIVILEGES"
   - Click "Make Changes"

### Step 2.2: Convert Schema from PostgreSQL to MySQL

I'll create the MySQL schema for you. Key differences:
- `UUID` → `CHAR(36)` with default UUID generation
- `TIMESTAMP WITH TIME ZONE` → `DATETIME` 
- `JSONB` → `JSON`
- `TEXT` → `VARCHAR` or `TEXT`
- `gen_random_uuid()` → `UUID()`
- Remove PostgreSQL-specific features

### Step 2.3: Create MySQL Schema File

Let me create the converted MySQL schema:

