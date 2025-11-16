<?php
/**
 * Listings API Endpoints
 * Handles CRUD operations for property listings
 */

// Load local config if it exists, otherwise use production config
if (file_exists(__DIR__ . '/../config.local.php')) {
    require_once __DIR__ . '/../config.local.php';
} else {
    require_once __DIR__ . '/../config.php';
}
require_once __DIR__ . '/../auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        if ($id) {
            getListingById($id);
        } else {
            getListings();
        }
        break;
    case 'POST':
        createListing();
        break;
    case 'PUT':
        if (!$id) errorResponse('Listing ID required');
        updateListing($id);
        break;
    case 'DELETE':
        if (!$id) errorResponse('Listing ID required');
        deleteListing($id);
        break;
    default:
        errorResponse('Method not allowed', 405);
}

/**
 * Get all listings with filters
 * GET /api/listings.php?bedrooms=2&county=Nairobi&minPrice=50000&maxPrice=100000
 */
function getListings() {
    try {
        $db = Database::getInstance()->getConnection();
        
        // Build query with filters
        $sql = "
            SELECT 
                l.*,
                u.id as agent_id,
                u.full_name as agent_name,
                u.email as agent_email,
                u.phone_number as agent_phone,
                u.is_verified_agent,
                u.profile_picture_url as agent_picture,
                (SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'id', pi.id,
                        'url', pi.url
                    )
                ) FROM property_images pi WHERE pi.listing_id = l.id ORDER BY pi.display_order) as images
            FROM property_listings l
            JOIN users u ON l.agent_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Apply filters
        if (isset($_GET['bedrooms'])) {
            $sql .= " AND l.bedrooms = ?";
            $params[] = (int)$_GET['bedrooms'];
        }
        
        if (isset($_GET['property_type'])) {
            $sql .= " AND l.property_type = ?";
            $params[] = $_GET['property_type'];
        }
        
        if (isset($_GET['county'])) {
            $sql .= " AND JSON_UNQUOTE(JSON_EXTRACT(l.location, '$.county')) = ?";
            $params[] = $_GET['county'];
        }
        
        if (isset($_GET['minPrice'])) {
            $sql .= " AND l.price >= ?";
            $params[] = (float)$_GET['minPrice'];
        }
        
        if (isset($_GET['maxPrice'])) {
            $sql .= " AND l.price <= ?";
            $params[] = (float)$_GET['maxPrice'];
        }
        
        if (isset($_GET['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $_GET['status'];
        } else {
            // Default: only show available listings
            $sql .= " AND l.status = 'available'";
        }
        
        if (isset($_GET['agent_id'])) {
            $sql .= " AND l.agent_id = ?";
            $params[] = $_GET['agent_id'];
        }
        
        if (isset($_GET['location'])) {
            $sql .= " AND (
                LOWER(l.title) LIKE LOWER(?) OR 
                LOWER(l.description) LIKE LOWER(?) OR
                LOWER(JSON_UNQUOTE(JSON_EXTRACT(l.location, '$.address'))) LIKE LOWER(?) OR
                LOWER(JSON_UNQUOTE(JSON_EXTRACT(l.location, '$.city'))) LIKE LOWER(?) OR
                LOWER(JSON_UNQUOTE(JSON_EXTRACT(l.location, '$.area'))) LIKE LOWER(?)
            )";
            $searchTerm = '%' . $_GET['location'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY l.created_at DESC";
        
        // Pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
        $offset = ($page - 1) * $limit;
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $listings = $stmt->fetchAll();
        
        // Parse JSON fields
        foreach ($listings as &$listing) {
            $listing['location'] = json_decode($listing['location'] ?? '{}', true);
            $listing['amenities'] = json_decode($listing['amenities'] ?? '[]', true) ?? [];
            $listing['images'] = json_decode($listing['images'] ?? '[]', true) ?? [];
            $listing['ai_scan'] = isset($listing['ai_scan']) && $listing['ai_scan'] ? json_decode($listing['ai_scan'], true) : null;
            
            // Normalize location: convert 'area' to 'neighborhood' for frontend consistency
            if (isset($listing['location']['area']) && !isset($listing['location']['neighborhood'])) {
                $listing['location']['neighborhood'] = $listing['location']['area'];
            }
            // Also convert 'city' to 'neighborhood' if neighborhood is not set
            if (isset($listing['location']['city']) && !isset($listing['location']['neighborhood'])) {
                $listing['location']['neighborhood'] = $listing['location']['city'];
            }
            
            // Group agent data
            $listing['agent'] = [
                'id' => $listing['agent_id'],
                'full_name' => $listing['agent_name'],
                'email' => $listing['agent_email'],
                'phone_number' => $listing['agent_phone'],
                'is_verified_agent' => (bool)$listing['is_verified_agent'],
                'profile_picture_url' => $listing['agent_picture']
            ];
            
            // Remove redundant fields
            unset($listing['agent_id'], $listing['agent_name'], $listing['agent_email'], 
                  $listing['agent_phone'], $listing['agent_picture']);
        }
        
        jsonResponse([
            'listings' => $listings,
            'page' => $page,
            'limit' => $limit
        ]);
        
    } catch (PDOException $e) {
        error_log("Get Listings Error: " . $e->getMessage());
        errorResponse('Failed to fetch listings', 500);
    }
}

/**
 * Get single listing by ID
 * GET /api/listings.php?id=xxx
 */
function getListingById($id) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT 
                l.*,
                u.id as agent_id,
                u.full_name as agent_name,
                u.email as agent_email,
                u.phone_number as agent_phone,
                u.is_verified_agent,
                u.profile_picture_url as agent_picture
            FROM property_listings l
            JOIN users u ON l.agent_id = u.id
            WHERE l.id = ?
        ");
        $stmt->execute([$id]);
        $listing = $stmt->fetch();
        
        if (!$listing) {
            errorResponse('Listing not found', 404);
        }
        
        // Get images
        $stmt = $db->prepare("
            SELECT id, url, display_order, ai_scan
            FROM property_images
            WHERE listing_id = ?
            ORDER BY display_order
        ");
        $stmt->execute([$id]);
        $images = $stmt->fetchAll();
        
        // Parse JSON fields
        $listing['location'] = json_decode($listing['location'] ?? '{}', true);
        $listing['amenities'] = json_decode($listing['amenities'] ?? '[]', true) ?? [];
        $listing['images'] = $images;
        
        // Normalize location: convert 'area' to 'neighborhood' for frontend consistency
        if (isset($listing['location']['area']) && !isset($listing['location']['neighborhood'])) {
            $listing['location']['neighborhood'] = $listing['location']['area'];
        }
        // Also convert 'city' to 'neighborhood' if neighborhood is not set
        if (isset($listing['location']['city']) && !isset($listing['location']['neighborhood'])) {
            $listing['location']['neighborhood'] = $listing['location']['city'];
        }
        
        // Group agent data
        $listing['agent'] = [
            'id' => $listing['agent_id'],
            'full_name' => $listing['agent_name'],
            'email' => $listing['agent_email'],
            'phone_number' => $listing['agent_phone'],
            'is_verified_agent' => (bool)$listing['is_verified_agent'],
            'profile_picture_url' => $listing['agent_picture']
        ];
        
        unset($listing['agent_id'], $listing['agent_name'], $listing['agent_email'], 
              $listing['agent_phone'], $listing['agent_picture']);
        
        // Increment views
        $stmt = $db->prepare("UPDATE property_listings SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonResponse(['listing' => $listing]);
        
    } catch (PDOException $e) {
        error_log("Get Listing Error: " . $e->getMessage());
        errorResponse('Failed to fetch listing', 500);
    }
}

/**
 * Create new listing
 * POST /api/listings.php
 * Body: { title, description, property_type, price, location, bedrooms, bathrooms, area_sq_ft, amenities, images }
 */
function createListing() {
    $currentUser = Auth::requireRole('agent');
    
    $data = getRequestBody();
    
    // Validate required fields
    $required = ['title', 'price', 'location'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            errorResponse("Field '$field' is required");
        }
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        $listingId = generateUUID();
        
        $stmt = $db->prepare("
            INSERT INTO property_listings (
                id, agent_id, title, description, property_type, price, 
                location, bedrooms, bathrooms, area_sq_ft, amenities, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $listingId,
            $currentUser['id'],
            sanitizeString($data['title']),
            sanitizeString($data['description'] ?? ''),
            $data['property_type'] ?? null,
            (float)$data['price'],
            json_encode($data['location']),
            isset($data['bedrooms']) ? (int)$data['bedrooms'] : null,
            isset($data['bathrooms']) ? (int)$data['bathrooms'] : null,
            isset($data['area_sq_ft']) ? (int)$data['area_sq_ft'] : null,
            json_encode($data['amenities'] ?? []),
            'available'
        ]);
        
        // Insert images if provided
        if (!empty($data['images']) && is_array($data['images'])) {
            $stmt = $db->prepare("
                INSERT INTO property_images (id, listing_id, url, display_order)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($data['images'] as $index => $imageUrl) {
                $stmt->execute([
                    generateUUID(),
                    $listingId,
                    $imageUrl,
                    $index
                ]);
            }
        }
        
        $db->commit();
        
        // Fetch and return created listing
        getListingById($listingId);
        
    } catch (PDOException $e) {
        if (isset($db)) $db->rollBack();
        error_log("Create Listing Error: " . $e->getMessage());
        errorResponse('Failed to create listing', 500);
    }
}

/**
 * Update listing
 * PUT /api/listings.php?id=xxx
 */
function updateListing($id) {
    $currentUser = Auth::requireRole('agent');
    
    $data = getRequestBody();
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Check ownership
        $stmt = $db->prepare("SELECT agent_id FROM property_listings WHERE id = ?");
        $stmt->execute([$id]);
        $listing = $stmt->fetch();
        
        if (!$listing) {
            errorResponse('Listing not found', 404);
        }
        
        if ($listing['agent_id'] !== $currentUser['id'] && $currentUser['role'] !== 'admin') {
            errorResponse('You can only edit your own listings', 403);
        }
        
        // Build dynamic update query
        $updates = [];
        $params = [];
        
        $allowedFields = [
            'title', 'description', 'property_type', 'price', 'location',
            'bedrooms', 'bathrooms', 'area_sq_ft', 'amenities', 'status'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                
                if (in_array($field, ['location', 'amenities'])) {
                    $params[] = json_encode($data[$field]);
                } elseif ($field === 'price') {
                    $params[] = (float)$data[$field];
                } elseif (in_array($field, ['bedrooms', 'bathrooms', 'area_sq_ft'])) {
                    $params[] = (int)$data[$field];
                } else {
                    $params[] = sanitizeString($data[$field]);
                }
            }
        }
        
        if (empty($updates)) {
            errorResponse('No fields to update');
        }
        
        $params[] = $id;
        
        $sql = "UPDATE property_listings SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        getListingById($id);
        
    } catch (PDOException $e) {
        error_log("Update Listing Error: " . $e->getMessage());
        errorResponse('Failed to update listing', 500);
    }
}

/**
 * Delete listing
 * DELETE /api/listings.php?id=xxx
 */
function deleteListing($id) {
    $currentUser = Auth::requireRole('agent');
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Check ownership
        $stmt = $db->prepare("SELECT agent_id FROM property_listings WHERE id = ?");
        $stmt->execute([$id]);
        $listing = $stmt->fetch();
        
        if (!$listing) {
            errorResponse('Listing not found', 404);
        }
        
        if ($listing['agent_id'] !== $currentUser['id'] && $currentUser['role'] !== 'admin') {
            errorResponse('You can only delete your own listings', 403);
        }
        
        // Delete (cascade will handle images)
        $stmt = $db->prepare("DELETE FROM property_listings WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonResponse(['message' => 'Listing deleted successfully']);
        
    } catch (PDOException $e) {
        error_log("Delete Listing Error: " . $e->getMessage());
        errorResponse('Failed to delete listing', 500);
    }
}

?>
