# HostAfrica Shared Hosting Migration Guide

## Overview

This guide provides comprehensive instructions for migrating your Kejalink application from Supabase to HostAfrica shared hosting with cPanel. The migration involves replacing Supabase's PostgreSQL database, authentication, and storage with equivalent services available on shared hosting.

---

## Table of Contents

1. [Architecture Changes](#architecture-changes)
2. [Prerequisites](#prerequisites)
3. [Database Migration](#database-migration)
4. [Backend API Setup](#backend-api-setup)
5. [Authentication Implementation](#authentication-implementation)
6. [File Storage Setup](#file-storage-setup)
7. [Frontend Updates](#frontend-updates)
8. [Deployment Steps](#deployment-steps)
9. [Testing Checklist](#testing-checklist)

---

## Architecture Changes

### Current Architecture (Supabase)
- **Database**: PostgreSQL (managed by Supabase)
- **Authentication**: Supabase Auth
- **File Storage**: Supabase Storage
- **API**: Supabase Auto-generated REST API
- **Frontend**: React SPA

### New Architecture (HostAfrica)
- **Database**: MySQL/MariaDB (cPanel managed)
- **Authentication**: Custom JWT-based auth with PHP backend
- **File Storage**: cPanel File Manager + PHP upload handler
- **API**: Custom REST API (PHP)
- **Frontend**: React SPA (same)

---

## Prerequisites

### HostAfrica cPanel Requirements
- ✅ Shared hosting account with cPanel
- ✅ MySQL database access
- ✅ PHP 7.4+ support
- ✅ SSH access (optional but recommended)
- ✅ Domain configured: kejalink.co.ke

### Local Development
- ✅ Node.js v16+
- ✅ MySQL Workbench or phpMyAdmin
- ✅ Git

---

## Database Migration

### Step 1: Export Supabase Data

1. **Access Supabase Dashboard**
   - Go to your Supabase project
   - Navigate to Database > SQL Editor

2. **Export Schema and Data**
   ```sql
   -- Export users table
   SELECT * FROM users;
   
   -- Export property_listings table
   SELECT * FROM property_listings;
   
   -- Export property_images table
   SELECT * FROM property_images;
   ```

3. **Save as CSV files** for each table

### Step 2: Create MySQL Database in cPanel

1. **Login to cPanel** (https://kejalink.co.ke:2083)

2. **Navigate to MySQL Databases**
   - Click "MySQL Databases"
   - Create new database: `kejalink_db`
   - Create database user: `kejalink_user`
   - Set strong password
   - Add user to database with ALL PRIVILEGES

3. **Note your database credentials**:
   ```
   Database Name: kejalink_db
   Database User: kejalink_user
   Database Password: [your-secure-password]
   Database Host: localhost
   ```

### Step 3: Create Database Schema

Use phpMyAdmin (accessible via cPanel) or MySQL CLI:

```sql
-- Users table
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    role ENUM('tenant', 'agent') DEFAULT 'tenant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Property listings table
CREATE TABLE property_listings (
    id VARCHAR(36) PRIMARY KEY,
    agent_id VARCHAR(36) NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    property_type ENUM('apartment', 'house', 'bedsitter', 'studio', 'commercial') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    location VARCHAR(200) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    bedrooms INT,
    bathrooms INT,
    area_sqft INT,
    amenities JSON,
    status ENUM('available', 'rented', 'pending') DEFAULT 'available',
    views INT DEFAULT 0,
    saves INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_agent (agent_id),
    INDEX idx_status (status),
    INDEX idx_location (location),
    INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Property images table
CREATE TABLE property_images (
    id VARCHAR(36) PRIMARY KEY,
    listing_id VARCHAR(36) NOT NULL,
    image_url TEXT NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES property_listings(id) ON DELETE CASCADE,
    INDEX idx_listing (listing_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sessions table for JWT tokens
CREATE TABLE sessions (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 4: Import Data

1. **Convert Supabase UUIDs** to match MySQL format if needed
2. **Import CSV data** via phpMyAdmin:
   - Select database
   - Click "Import" tab
   - Upload CSV files
   - Map columns correctly

---

## Backend API Setup

### Directory Structure

Create the following structure in your cPanel File Manager under `public_html/api/`:

```
public_html/
├── api/
│   ├── config/
│   │   ├── database.php
│   │   └── config.php
│   ├── models/
│   │   ├── User.php
│   │   ├── Listing.php
│   │   └── Image.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── ListingController.php
│   │   └── ImageController.php
│   ├── middleware/
│   │   └── AuthMiddleware.php
│   ├── utils/
│   │   └── JWT.php
│   └── index.php
└── uploads/
    └── property_images/
```

### Configuration Files

**api/config/database.php**
```php
<?php
class Database {
    private $host = "localhost";
    private $db_name = "kejalink_db";
    private $username = "kejalink_user";
    private $password = "YOUR_PASSWORD_HERE";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>
```

**api/config/config.php**
```php
<?php
// CORS Headers
header("Access-Control-Allow-Origin: https://kejalink.co.ke");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// JWT Secret Key - CHANGE THIS TO A SECURE RANDOM STRING
define('JWT_SECRET', 'your-super-secret-jwt-key-change-this');
define('JWT_EXPIRY', 86400); // 24 hours

// Upload settings
define('UPLOAD_DIR', '../uploads/property_images/');
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Base URL
define('BASE_URL', 'https://kejalink.co.ke');
?>
```

**api/utils/JWT.php**
```php
<?php
class JWT {
    
    public static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public static function decode($jwt) {
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) {
            return false;
        }
        
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if ($base64UrlSignature !== $signatureProvided) {
            return false;
        }
        
        return json_decode($payload, true);
    }
    
    public static function isValid($jwt) {
        $payload = self::decode($jwt);
        if (!$payload) {
            return false;
        }
        
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return true;
    }
}
?>
```

**api/middleware/AuthMiddleware.php**
```php
<?php
require_once '../utils/JWT.php';

class AuthMiddleware {
    
    public static function authenticate() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No authorization token provided']);
            exit();
        }
        
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
        
        if (!JWT::isValid($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            exit();
        }
        
        $payload = JWT::decode($token);
        return $payload;
    }
}
?>
```

**api/controllers/AuthController.php**
```php
<?php
require_once '../config/database.php';
require_once '../utils/JWT.php';

class AuthController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function register() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->email) || !isset($data->password) || !isset($data->full_name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        // Check if user exists
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $data->email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'User already exists']);
            return;
        }
        
        // Create user
        $userId = $this->generateUUID();
        $passwordHash = password_hash($data->password, PASSWORD_BCRYPT);
        $role = isset($data->role) ? $data->role : 'tenant';
        
        $query = "INSERT INTO users (id, email, password_hash, full_name, phone_number, role) 
                  VALUES (:id, :email, :password_hash, :full_name, :phone_number, :role)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id', $userId);
        $stmt->bindParam(':email', $data->email);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':full_name', $data->full_name);
        $stmt->bindParam(':phone_number', $data->phone_number);
        $stmt->bindParam(':role', $role);
        
        if ($stmt->execute()) {
            $token = $this->createToken($userId, $data->email, $role);
            http_response_code(201);
            echo json_encode([
                'user' => [
                    'id' => $userId,
                    'email' => $data->email,
                    'full_name' => $data->full_name,
                    'role' => $role
                ],
                'token' => $token
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed']);
        }
    }
    
    public function login() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->email) || !isset($data->password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing email or password']);
            return;
        }
        
        $query = "SELECT id, email, password_hash, full_name, role FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $data->email);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($data->password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }
        
        $token = $this->createToken($user['id'], $user['email'], $user['role']);
        
        echo json_encode([
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'role' => $user['role']
            ],
            'token' => $token
        ]);
    }
    
    private function createToken($userId, $email, $role) {
        $payload = [
            'user_id' => $userId,
            'email' => $email,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + JWT_EXPIRY
        ];
        return JWT::encode($payload);
    }
    
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>
```

**api/controllers/ListingController.php**
```php
<?php
require_once '../config/database.php';
require_once '../middleware/AuthMiddleware.php';

class ListingController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function getListings() {
        $query = "SELECT l.*, 
                  (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', i.id, 'image_url', i.image_url, 'display_order', i.display_order))
                   FROM property_images i 
                   WHERE i.listing_id = l.id 
                   ORDER BY i.display_order) as images
                  FROM property_listings l 
                  WHERE l.status = 'available'
                  ORDER BY l.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse JSON images
        foreach ($listings as &$listing) {
            $listing['images'] = json_decode($listing['images'], true) ?? [];
        }
        
        echo json_encode($listings);
    }
    
    public function getListing($id) {
        // Increment view count
        $updateQuery = "UPDATE property_listings SET views = views + 1 WHERE id = :id";
        $updateStmt = $this->db->prepare($updateQuery);
        $updateStmt->bindParam(':id', $id);
        $updateStmt->execute();
        
        $query = "SELECT l.*, 
                  (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', i.id, 'image_url', i.image_url, 'display_order', i.display_order))
                   FROM property_images i 
                   WHERE i.listing_id = l.id 
                   ORDER BY i.display_order) as images
                  FROM property_listings l 
                  WHERE l.id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Listing not found']);
            return;
        }
        
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);
        $listing['images'] = json_decode($listing['images'], true) ?? [];
        
        echo json_encode($listing);
    }
    
    public function createListing() {
        $user = AuthMiddleware::authenticate();
        
        if ($user['role'] !== 'agent') {
            http_response_code(403);
            echo json_encode(['error' => 'Only agents can create listings']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"));
        
        $listingId = $this->generateUUID();
        
        $query = "INSERT INTO property_listings 
                  (id, agent_id, title, description, property_type, price, location, 
                   latitude, longitude, bedrooms, bathrooms, area_sqft, amenities, status)
                  VALUES 
                  (:id, :agent_id, :title, :description, :property_type, :price, :location,
                   :latitude, :longitude, :bedrooms, :bathrooms, :area_sqft, :amenities, :status)";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':id', $listingId);
        $stmt->bindParam(':agent_id', $user['user_id']);
        $stmt->bindParam(':title', $data->title);
        $stmt->bindParam(':description', $data->description);
        $stmt->bindParam(':property_type', $data->property_type);
        $stmt->bindParam(':price', $data->price);
        $stmt->bindParam(':location', $data->location);
        $stmt->bindParam(':latitude', $data->latitude);
        $stmt->bindParam(':longitude', $data->longitude);
        $stmt->bindParam(':bedrooms', $data->bedrooms);
        $stmt->bindParam(':bathrooms', $data->bathrooms);
        $stmt->bindParam(':area_sqft', $data->area_sqft);
        $amenitiesJson = json_encode($data->amenities);
        $stmt->bindParam(':amenities', $amenitiesJson);
        $status = 'available';
        $stmt->bindParam(':status', $status);
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['id' => $listingId, 'message' => 'Listing created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create listing']);
        }
    }
    
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>
```

**api/index.php** (Router)
```php
<?php
require_once 'config/config.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/ListingController.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api', '', $path);

// Route handling
switch ($path) {
    case '/auth/register':
        if ($method === 'POST') {
            $controller = new AuthController();
            $controller->register();
        }
        break;
        
    case '/auth/login':
        if ($method === 'POST') {
            $controller = new AuthController();
            $controller->login();
        }
        break;
        
    case '/listings':
        $controller = new ListingController();
        if ($method === 'GET') {
            $controller->getListings();
        } elseif ($method === 'POST') {
            $controller->createListing();
        }
        break;
        
    default:
        if (preg_match('/^\/listings\/([a-f0-9-]+)$/', $path, $matches)) {
            $controller = new ListingController();
            if ($method === 'GET') {
                $controller->getListing($matches[1]);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
        }
        break;
}
?>
```

---

## File Storage Setup

### Step 1: Create Upload Directory

In cPanel File Manager:
1. Navigate to `public_html/`
2. Create directory: `uploads/property_images/`
3. Set permissions to 755

### Step 2: Create Upload Handler

**api/controllers/ImageController.php**
```php
<?php
require_once '../config/database.php';
require_once '../middleware/AuthMiddleware.php';

class ImageController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function uploadImage() {
        $user = AuthMiddleware::authenticate();
        
        if (!isset($_FILES['image']) || !isset($_POST['listing_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing image or listing_id']);
            return;
        }
        
        $file = $_FILES['image'];
        $listingId = $_POST['listing_id'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Upload error']);
            return;
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            http_response_code(400);
            echo json_encode(['error' => 'File too large']);
            return;
        }
        
        if (!in_array($file['type'], ALLOWED_TYPES)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid file type']);
            return;
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $this->generateUUID() . '.' . $extension;
        $filepath = UPLOAD_DIR . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save file']);
            return;
        }
        
        // Save to database
        $imageId = $this->generateUUID();
        $imageUrl = BASE_URL . '/uploads/property_images/' . $filename;
        
        $query = "INSERT INTO property_images (id, listing_id, image_url, display_order) 
                  VALUES (:id, :listing_id, :image_url, 
                  (SELECT COALESCE(MAX(display_order), 0) + 1 FROM property_images WHERE listing_id = :listing_id2))";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $imageId);
        $stmt->bindParam(':listing_id', $listingId);
        $stmt->bindParam(':image_url', $imageUrl);
        $stmt->bindParam(':listing_id2', $listingId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'id' => $imageId,
                'image_url' => $imageUrl,
                'message' => 'Image uploaded successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save image metadata']);
        }
    }
    
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>
```

---

## Frontend Updates

### Step 1: Create New API Service

Create `services/apiClient.ts`:

```typescript
const API_BASE_URL = 'https://kejalink.co.ke/api';

interface ApiResponse<T> {
  data?: T;
  error?: string;
}

class ApiClient {
  private token: string | null = null;

  constructor() {
    // Load token from localStorage
    this.token = localStorage.getItem('auth_token');
  }

  setToken(token: string) {
    this.token = token;
    localStorage.setItem('auth_token', token);
  }

  clearToken() {
    this.token = null;
    localStorage.removeItem('auth_token');
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<ApiResponse<T>> {
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      ...options.headers,
    };

    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`;
    }

    try {
      const response = await fetch(`${API_BASE_URL}${endpoint}`, {
        ...options,
        headers,
      });

      const data = await response.json();

      if (!response.ok) {
        return { error: data.error || 'Request failed' };
      }

      return { data };
    } catch (error) {
      return { error: 'Network error' };
    }
  }

  async get<T>(endpoint: string): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, { method: 'GET' });
  }

  async post<T>(endpoint: string, body: any): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, {
      method: 'POST',
      body: JSON.stringify(body),
    });
  }

  async uploadImage(listingId: string, file: File): Promise<ApiResponse<any>> {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('listing_id', listingId);

    const headers: HeadersInit = {};
    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`;
    }

    try {
      const response = await fetch(`${API_BASE_URL}/images/upload`, {
        method: 'POST',
        headers,
        body: formData,
      });

      const data = await response.json();

      if (!response.ok) {
        return { error: data.error || 'Upload failed' };
      }

      return { data };
    } catch (error) {
      return { error: 'Network error' };
    }
  }
}

export const apiClient = new ApiClient();
```

### Step 2: Replace Supabase Services

Update `services/authService.ts`:

```typescript
import { apiClient } from './apiClient';

export interface User {
  id: string;
  email: string;
  full_name: string;
  role: 'tenant' | 'agent';
}

export const authService = {
  async login(email: string, password: string) {
    const response = await apiClient.post<{ user: User; token: string }>(
      '/auth/login',
      { email, password }
    );

    if (response.data) {
      apiClient.setToken(response.data.token);
    }

    return response;
  },

  async register(userData: {
    email: string;
    password: string;
    full_name: string;
    phone_number?: string;
    role?: 'tenant' | 'agent';
  }) {
    const response = await apiClient.post<{ user: User; token: string }>(
      '/auth/register',
      userData
    );

    if (response.data) {
      apiClient.setToken(response.data.token);
    }

    return response;
  },

  logout() {
    apiClient.clearToken();
  },

  getCurrentUser(): User | null {
    const token = localStorage.getItem('auth_token');
    if (!token) return null;

    // Decode JWT to get user info
    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      return {
        id: payload.user_id,
        email: payload.email,
        full_name: payload.full_name || '',
        role: payload.role,
      };
    } catch {
      return null;
    }
  },
};
```

Update `services/listingService.ts`:

```typescript
import { apiClient } from './apiClient';
import type { PropertyListing } from '../types';

export const listingService = {
  async getListings() {
    return apiClient.get<PropertyListing[]>('/listings');
  },

  async getListing(id: string) {
    return apiClient.get<PropertyListing>(`/listings/${id}`);
  },

  async createListing(listingData: Partial<PropertyListing>) {
    return apiClient.post<{ id: string }>('/listings', listingData);
  },

  async uploadImage(listingId: string, file: File) {
    return apiClient.uploadImage(listingId, file);
  },
};
```

### Step 3: Update Environment Variables

Update `.env`:

```env
VITE_API_BASE_URL=https://kejalink.co.ke/api
VITE_GOOGLE_MAPS_API_KEY=your-google-maps-key
```

---

## Deployment Steps

### Step 1: Build Frontend

```bash
npm run build
```

This creates a `dist/` folder with your production build.

### Step 2: Upload to cPanel

1. **Login to cPanel File Manager**
2. **Navigate to `public_html/`**
3. **Upload `dist/` contents**:
   - Upload all files from `dist/` to `public_html/`
   - Ensure `index.html` is in root

### Step 3: Configure .htaccess

Create `public_html/.htaccess`:

```apache
# Enable RewriteEngine
RewriteEngine On

# API routes - don't rewrite these
RewriteCond %{REQUEST_URI} ^/api
RewriteRule ^(.*)$ - [L]

# Uploads - don't rewrite these
RewriteCond %{REQUEST_URI} ^/uploads
RewriteRule ^(.*)$ - [L]

# React Router - redirect all other requests to index.html
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.html [L]

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

### Step 4: Test the Deployment

1. Visit https://kejalink.co.ke
2. Test user registration
3. Test login
4. Test creating a listing
5. Test uploading images

---

## Testing Checklist

- [ ] Database connection working
- [ ] User registration successful
- [ ] User login successful
- [ ] JWT authentication working
- [ ] Listings display correctly
- [ ] Listing details page loads
- [ ] Create listing form works
- [ ] Image upload successful
- [ ] Search and filters work
- [ ] Agent dashboard loads
- [ ] Mobile responsive
- [ ] HTTPS working
- [ ] CORS configured correctly

---

## Security Considerations

1. **Change JWT Secret**: Update `JWT_SECRET` in `config.php` to a secure random string
2. **Database Security**: Use strong passwords for MySQL user
3. **File Upload Validation**: Validate file types and sizes on server
4. **SQL Injection Prevention**: Always use prepared statements (already implemented)
5. **HTTPS**: Ensure SSL certificate is installed (HostAfrica provides free Let's Encrypt)
6. **CORS**: Restrict to your domain only
7. **Rate Limiting**: Consider implementing rate limiting for API endpoints

---

## Maintenance & Monitoring

### Backup Strategy
- **Database**: Use cPanel to schedule daily database backups
- **Files**: Backup `uploads/` directory weekly
- **Code**: Keep git repository updated

### Monitoring
- Check error logs in cPanel > Error Log
- Monitor disk space usage
- Track API response times

### Updates
- Keep PHP updated via cPanel
- Update frontend dependencies regularly
- Monitor security advisories

---

## Troubleshooting

### Common Issues

**Issue**: CORS errors
- **Solution**: Check `Access-Control-Allow-Origin` in `config.php`

**Issue**: Database connection failed
- **Solution**: Verify credentials in `database.php`, check if MySQL service is running

**Issue**: Images not uploading
- **Solution**: Check directory permissions (755 for uploads folder)

**Issue**: 500 Internal Server Error
- **Solution**: Check PHP error logs in cPanel

**Issue**: React routes return 404
- **Solution**: Verify `.htaccess` is correctly configured

---

## Additional Resources

- [cPanel Documentation](https://docs.cpanel.net/)
- [PHP PDO Tutorial](https://www.php.net/manual/en/book.pdo.php)
- [JWT Best Practices](https://tools.ietf.org/html/rfc8725)
- [React Production Build](https://reactjs.org/docs/optimizing-performance.html)

---

## Support

For HostAfrica-specific issues:
- Support Portal: https://support.hostafrica.co.za
- Email: support@hostafrica.com

For application issues, refer to your development documentation or contact your development team.
