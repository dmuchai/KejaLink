/**
 * API Client for KejaLink Backend
 * Handles all HTTP requests to the PHP API
 */

// Determine API base URL
// Priority: explicit VITE_API_BASE_URL -> current origin (works for prod and dev proxy) -> fallback
const getDefaultBase = () => {
  if (typeof window !== 'undefined' && window.location?.origin) {
    return window.location.origin;
  }
  return (import.meta.env.VITE_FALLBACK_ORIGIN as string) || 'https://kejalink.co.ke';
};

const ENV_BASE = (import.meta.env.VITE_API_BASE_URL as string | undefined);
const isBrowser = typeof window !== 'undefined';
const isLocalOrigin = isBrowser && /^(localhost|127\.0\.0\.1)$/.test(window.location.hostname);
const isEnvLocalhost = !!ENV_BASE && /localhost|127\.0\.0\.1/.test(ENV_BASE);

// Safety: if a localhost ENV_BASE leaks into a non-local build, ignore it and use current origin.
const API_BASE_URL: string = (!ENV_BASE || (!isLocalOrigin && isEnvLocalhost))
  ? getDefaultBase()
  : ENV_BASE;

if (import.meta.env.DEV) {
  // eslint-disable-next-line no-console
  console.log('API Base URL:', API_BASE_URL);
}

// Helper function to get auth token
function getAuthToken(): string | null {
  return localStorage.getItem('auth_token');
}

// Helper function to make authenticated requests
async function apiRequest<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const token = getAuthToken();
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    ...(options.headers || {}),
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(`${API_BASE_URL}${endpoint}`, {
    ...options,
    headers,
  });

  // Try to parse JSON if Content-Type indicates JSON; otherwise capture text for clearer errors
  const contentType = response.headers.get('Content-Type') || '';
  const isJson = contentType.includes('application/json');

  if (!response.ok) {
    if (isJson) {
      const error = await response.json().catch(() => ({ error: 'Request failed' }));
      throw new Error(error.error || `HTTP ${response.status}`);
    } else {
      const text = await response.text().catch(() => 'Request failed');
      throw new Error(text.slice(0, 300) || `HTTP ${response.status}`);
    }
  }

  if (isJson) {
    return response.json();
  } else {
    // Non-JSON success response isn't expected; surface a helpful error
    const text = await response.text();
    throw new Error(`Expected JSON but received: ${text.slice(0, 300)}`);
  }
}

// ============================================
// AUTHENTICATION API
// ============================================

export interface RegisterData {
  email: string;
  password: string;
  full_name: string;
  role: 'tenant' | 'agent';
}

export interface LoginData {
  email: string;
  password: string;
}

export interface User {
  id: string;
  email: string;
  full_name: string;
  role: 'tenant' | 'agent' | 'admin';
  is_verified_agent: boolean;
  profile_picture_url: string | null;
  phone_number: string | null;
  created_at?: string;
  updated_at?: string;
}

export interface AuthResponse {
  message: string;
  token: string;
  user: User;
}

export const authAPI = {
  register: async (data: RegisterData): Promise<AuthResponse> => {
    return apiRequest('/api/auth.php?action=register', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  login: async (data: LoginData): Promise<AuthResponse> => {
    return apiRequest('/api/auth.php?action=login', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  getProfile: async (): Promise<{ user: User }> => {
    return apiRequest('/api/auth.php?action=profile');
  },

  logout: async (): Promise<{ message: string }> => {
    return apiRequest('/api/auth.php?action=logout', {
      method: 'POST',
    });
  },

  forgotPassword: async (email: string): Promise<{ message: string }> => {
    return apiRequest('/api/auth.php?action=forgot-password', {
      method: 'POST',
      body: JSON.stringify({ email }),
    });
  },

  validateResetToken: async (token: string): Promise<{ valid: boolean }> => {
    return apiRequest(`/api/auth.php?action=validate-reset-token&token=${encodeURIComponent(token)}`);
  },

  resetPassword: async (token: string, newPassword: string): Promise<{ message: string }> => {
    return apiRequest('/api/auth.php?action=reset-password', {
      method: 'POST',
      body: JSON.stringify({ token, new_password: newPassword }),
    });
  },
};

// ============================================
// LISTINGS API
// ============================================

export interface Location {
  county: string;
  area: string;
  neighborhood?: string;
  address?: string;
  latitude?: number;
  longitude?: number;
}

export interface PropertyImage {
  id: string;
  url: string;
  display_order: number;
  ai_scan: any;
}

export interface Listing {
  id: string;
  title: string;
  description: string;
  property_type: 'apartment' | 'studio' | 'bedsitter' | 'bungalow' | 'maisonette' | 'townhouse';
  price: number;
  location: Location;
  bedrooms: number;
  bathrooms: number;
  area_sq_ft: number;
  amenities: string[];
  status: 'available' | 'rented' | 'unavailable';
  is_featured: boolean;
  views: number;
  saves: number;
  created_at: string;
  updated_at: string;
  images: PropertyImage[];
  agent: {
    id: string;
    full_name: string;
    email: string;
    phone_number: string | null;
    is_verified_agent: boolean;
    profile_picture_url: string | null;
  };
  // Optional rating fields if backend exposes them
  rating_average?: number;
  rating_count?: number;
}

export interface ListingsResponse {
  listings: Listing[];
  page: number;
  limit: number;
}

export interface CreateListingData {
  title: string;
  description: string;
  property_type: 'apartment' | 'studio' | 'bedsitter' | 'bungalow' | 'maisonette' | 'townhouse';
  price: number;
  location: Location;
  bedrooms: number;
  bathrooms: number;
  area_sq_ft: number;
  amenities: string[];
  status?: 'available' | 'rented' | 'unavailable';
}

export interface UpdateListingData extends Partial<CreateListingData> {}

export interface ListingsFilters {
  bedrooms?: number;
  property_type?: string;
  county?: string;
  minPrice?: number;
  maxPrice?: number;
  status?: string;
  agent_id?: string;
  location?: string;
  page?: number;
  limit?: number;
}

export const listingsAPI = {
  getAll: async (filters?: ListingsFilters): Promise<ListingsResponse> => {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined) {
          params.append(key, String(value));
        }
      });
    }
    const query = params.toString() ? `?${params.toString()}` : '';
    const data = await apiRequest<ListingsResponse>(`/api/listings.php${query}`);
    return normalizeListingsResponse(data);
  },

  getById: async (id: string): Promise<{ listing: Listing }> => {
    const data = await apiRequest<{ listing: Listing }>(`/api/listings.php?id=${id}`);
    return { listing: normalizeListing(data.listing) };
  },

  create: async (data: CreateListingData): Promise<{ listing: Listing }> => {
    return apiRequest('/api/listings.php', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  update: async (id: string, data: UpdateListingData): Promise<{ listing: Listing }> => {
    return apiRequest(`/api/listings.php?id=${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  },

  delete: async (id: string): Promise<{ message: string }> => {
    return apiRequest(`/api/listings.php?id=${id}`, {
      method: 'DELETE',
    });
  },
};

// ============================================
// UPLOAD API
// ============================================

export const uploadAPI = {
  uploadImage: async (file: File, listingId?: string): Promise<{ url: string; filename: string; id?: string }> => {
    const formData = new FormData();
    formData.append('image', file);
    
    // If listing_id is provided, include it so the backend can link the image
    if (listingId) {
      formData.append('listing_id', listingId);
    }

    const token = getAuthToken();
    const headers: HeadersInit = {};
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${API_BASE_URL}/api/upload.php`, {
      method: 'POST',
      headers,
      body: formData,
    });

    if (!response.ok) {
      const error = await response.json().catch(() => ({ error: 'Upload failed' }));
      throw new Error(error.error || `HTTP ${response.status}`);
    }

    return response.json();
  },
};

// ============================================
// IMAGES API (manage individual images)
// ============================================

export const imagesAPI = {
  delete: async (id: string): Promise<{ message: string }> => {
    return apiRequest(`/api/images.php?id=${encodeURIComponent(id)}`, {
      method: 'DELETE',
    });
  },
};

// ============================================
// STORAGE HELPERS
// ============================================

export const storage = {
  setToken: (token: string) => {
    localStorage.setItem('auth_token', token);
  },

  getToken: () => {
    return localStorage.getItem('auth_token');
  },

  removeToken: () => {
    localStorage.removeItem('auth_token');
  },

  setUser: (user: User) => {
    localStorage.setItem('user', JSON.stringify(user));
  },

  getUser: (): User | null => {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  },

  removeUser: () => {
    localStorage.removeItem('user');
  },

  clear: () => {
    storage.removeToken();
    storage.removeUser();
  },
};

// ============================================
// NORMALIZATION HELPERS
// ============================================

function normalizeImageUrl(url: string): string {
  if (!url) return url;
  try {
    // Canonical path is /uploads
    // Already an absolute URL to /uploads
    if (/^https?:\/\//.test(url) && /\/uploads\//.test(url)) return url;

    // If it's an absolute URL to /api/uploads/, rewrite to /uploads/
    if (/^https?:\/\//.test(url) && /\/api\/uploads\//.test(url)) {
      return url.replace(/\/api\/uploads\//, '/uploads/');
    }

    // If it starts with /api/uploads/, rewrite to /uploads/
    if (/^\/api\/uploads\//.test(url)) {
      return url.replace(/^\/api\/uploads\//, '/uploads/');
    }

    // If it already starts with /uploads/, keep
    if (/^\/uploads\//.test(url)) return url;

    // If it's a bare filename or 'uploads/filename', prefix with /uploads/
    if (/^uploads\//.test(url)) {
      return url.replace(/^uploads\//, '/uploads/');
    }

    // Bare filename (no slashes)
    if (!/\/\//.test(url)) {
      return `/uploads/${url}`;
    }

    // Fallback: return as-is
    return url;
  } catch {
    // Safe fallback to /uploads
  if (!/\/\//.test(url)) return `/uploads/${url}`;
    return url.replace(/^\/api\/uploads\//, '/uploads/');
  }
}

function normalizeListing(listing: Listing): Listing {
  if (!listing) return listing as any;
  if (Array.isArray(listing.images)) {
    listing.images = listing.images.map(img => ({
      ...img,
      url: normalizeImageUrl(img.url),
    }));
  }
  return listing;
}

function normalizeListingsResponse(res: ListingsResponse): ListingsResponse {
  if (Array.isArray(res?.listings)) {
    res.listings = res.listings.map(normalizeListing);
  }
  return res;
}
