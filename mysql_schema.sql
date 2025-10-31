-- ============================================
-- KEJALINK MYSQL DATABASE SCHEMA
-- ============================================
-- For HostAfrica cPanel MySQL/MariaDB
-- Run this in phpMyAdmin after creating the database
-- ============================================

-- ============================================
-- 1. USERS TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    role ENUM('tenant', 'agent', 'admin') DEFAULT 'tenant',
    is_verified_agent BOOLEAN DEFAULT FALSE,
    profile_picture_url TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (email),
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. PROPERTY_LISTINGS TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS property_listings (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    agent_id CHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    property_type ENUM('apartment', 'house', 'bedsitter', 'studio', 'commercial'),
    price DECIMAL(12, 2) NOT NULL,
    location JSON NOT NULL COMMENT 'Stores: {address, city, county, latitude, longitude}',
    bedrooms INT,
    bathrooms INT,
    area_sq_ft INT,
    amenities JSON COMMENT 'Array of amenities',
    status ENUM('available', 'rented', 'pending', 'unavailable') DEFAULT 'available',
    is_featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    saves INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_listings_agent (agent_id),
    INDEX idx_listings_status (status),
    INDEX idx_listings_price (price),
    INDEX idx_listings_created (created_at DESC),
    FULLTEXT INDEX idx_listings_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. PROPERTY_IMAGES TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS property_images (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    listing_id CHAR(36) NOT NULL,
    url TEXT NOT NULL,
    display_order INT DEFAULT 0,
    ai_scan JSON COMMENT 'Stores AI scan results: {status, scanned_at, is_appropriate}',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES property_listings(id) ON DELETE CASCADE,
    INDEX idx_images_listing (listing_id),
    INDEX idx_images_order (listing_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. SAVED_LISTINGS TABLE
-- ============================================

CREATE TABLE IF NOT EXISTS saved_listings (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    listing_id CHAR(36) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES property_listings(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_listing (user_id, listing_id),
    INDEX idx_saved_user (user_id),
    INDEX idx_saved_listing (listing_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. SESSIONS TABLE (for JWT/Auth tokens)
-- ============================================

CREATE TABLE IF NOT EXISTS user_sessions (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36) NOT NULL,
    token_hash VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of JWT token',
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_token (token_hash),
    INDEX idx_sessions_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. CREATE SAMPLE ADMIN USER (Optional)
-- ============================================

-- Password: Admin@123 (bcrypt hash - you should change this!)
INSERT INTO users (id, email, password_hash, full_name, role, is_verified_agent)
VALUES (
    UUID(),
    'admin@kejalink.co.ke',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin User',
    'admin',
    TRUE
) ON DUPLICATE KEY UPDATE email=email;

-- ============================================
-- 7. VERIFICATION QUERIES
-- ============================================

-- Check tables were created
SHOW TABLES;

-- Check users table structure
DESCRIBE users;

-- Check property_listings table structure
DESCRIBE property_listings;

-- Verify admin user was created
SELECT id, email, full_name, role FROM users WHERE role = 'admin';

-- ============================================
-- NOTES:
-- ============================================
-- 1. Run this entire script in phpMyAdmin
-- 2. Make sure your MySQL version supports JSON (5.7.8+)
-- 3. Change the admin password after first login
-- 4. UTF8MB4 charset supports emojis and all unicode characters
-- 5. InnoDB engine supports foreign keys and transactions
-- ============================================
