/**
 * API Client for KejaLink Backend
 * Handles all HTTP requests to the PHP API
 */

const API_BASE_URL = 'https://kejalink.co.ke/api';

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

  if (!response.ok) {
    const error = await response.json().catch(() => ({ error: 'Request failed' }));
    throw new Error(error.error || `HTTP ${response.status}`);
  }

  return response.json();
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
};

// ============================================
// LISTINGS API
// ============================================

export interface Location {
  county: string;
  area: string;
  address?: string;
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
  property_type: 'apartment' | 'house' | 'studio' | 'bedsitter' | 'commercial';
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
}

export interface ListingsResponse {
  listings: Listing[];
  page: number;
  limit: number;
}

export interface CreateListingData {
  title: string;
  description: string;
  property_type: 'apartment' | 'house' | 'studio' | 'bedsitter' | 'commercial';
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
    return apiRequest(`/api/listings.php${query}`);
  },

  getById: async (id: string): Promise<{ listing: Listing }> => {
    return apiRequest(`/api/listings.php?id=${id}`);
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
