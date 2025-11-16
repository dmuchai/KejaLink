/**
 * @fileoverview Listing Service - Handles property listing operations with API integration
 * 
 * This service provides CRUD operations for property listings, including:
 * - Fetching listings with advanced filtering
 * - Creating new property listings with image support
 * - Updating existing listings
 * - Managing agent metrics and statistics
 * - Data transformation between frontend and backend formats
 * 
 * @author Kejalink Team
 * @version 2.0.0 - Migrated from Supabase to PHP API
 */

import { listingsAPI, uploadAPI, imagesAPI, type Listing, type CreateListingData, type UpdateListingData, type ListingsFilters } from './apiClient';
import type { PropertyListing, AgentMetrics, PropertyImage } from '../types';
import { UserRole } from '../types';
import type { SearchFilters } from '../components/SearchBar';

/**
 * Transforms API Listing to frontend PropertyListing format
 */
const transformListing = (apiListing: Listing): PropertyListing => {
  return {
    id: apiListing.id,
    title: apiListing.title,
    description: apiListing.description,
    price: parseFloat(apiListing.price.toString()),
    location: {
      county: apiListing.location.county,
      neighborhood: apiListing.location.area,
      address: apiListing.location.address || '',
    },
    bedrooms: apiListing.bedrooms,
    bathrooms: apiListing.bathrooms,
    amenities: apiListing.amenities,
    areaSqFt: apiListing.area_sq_ft,
    images: apiListing.images.map(img => ({
      id: img.id,
      url: img.url,
      altText: undefined,
      aiScanStatus: img.ai_scan?.status || 'pending' as const,
      aiScanReason: img.ai_scan?.reason,
    })),
    agent: {
      id: apiListing.agent.id,
      name: apiListing.agent.full_name,
      email: apiListing.agent.email,
      role: apiListing.agent.is_verified_agent ? UserRole.AGENT : UserRole.AGENT,
      isVerifiedAgent: apiListing.agent.is_verified_agent,
      profilePictureUrl: apiListing.agent.profile_picture_url || undefined,
      phoneNumber: apiListing.agent.phone_number || undefined,
      createdAt: apiListing.created_at,
    },
    status: apiListing.status === 'unavailable' ? 'pending_verification' : apiListing.status,
    isFeatured: apiListing.is_featured,
    createdAt: apiListing.created_at,
    updatedAt: apiListing.updated_at,
    views: apiListing.views,
    saves: apiListing.saves,
    // Map optional rating summary if present
    ratingAverage: typeof (apiListing as any).rating_average === 'number' ? (apiListing as any).rating_average : undefined,
    ratingCount: typeof (apiListing as any).rating_count === 'number' ? (apiListing as any).rating_count : undefined,
  };
};

/**
 * Fetches property listings with optional filtering
 */
export const getListings = async (filters?: SearchFilters): Promise<PropertyListing[]> => {
  try {
    const apiFilters: ListingsFilters = {};

    if (filters) {
      if (filters.location) apiFilters.location = filters.location;
      if (filters.county) apiFilters.county = filters.county;
      if (filters.propertyType) apiFilters.property_type = filters.propertyType;
      if (filters.bedrooms) apiFilters.bedrooms = filters.bedrooms;
      if (filters.minPrice) apiFilters.minPrice = filters.minPrice;
      if (filters.maxPrice) apiFilters.maxPrice = filters.maxPrice;
    }

    const response = await listingsAPI.getAll(apiFilters);
    return response.listings.map(transformListing);
  } catch (error: any) {
    console.error('Error fetching listings:', error);
    throw new Error(error.message || 'Failed to fetch listings');
  }
};

/**
 * Fetches a single property listing by ID
 */
export const getListingById = async (id: string): Promise<PropertyListing | null> => {
  try {
    const response = await listingsAPI.getById(id);
    return transformListing(response.listing);
  } catch (error: any) {
    console.error(`Error fetching listing ${id}:`, error);
    if (error.message.includes('404')) {
      return null;
    }
    throw new Error(error.message || 'Failed to fetch listing');
  }
};

/**
 * Creates a new property listing
 */
export const createListing = async (
  listingData: Partial<PropertyListing> & { agent_id?: string }
): Promise<PropertyListing> => {
  try {
    // Validate required fields
    if (!listingData.title || !listingData.price || !listingData.location) {
      throw new Error('Missing required fields: title, price, and location');
    }

    // Prepare create data
    const createData: CreateListingData = {
      title: listingData.title,
      description: listingData.description || '',
      property_type: 'apartment',
      price: listingData.price,
      location: {
        county: listingData.location.county,
        area: listingData.location.neighborhood,
        address: listingData.location.address,
      },
      bedrooms: listingData.bedrooms || 1,
      bathrooms: listingData.bathrooms || 1,
      area_sq_ft: listingData.areaSqFt || 500,
      amenities: listingData.amenities || [],
      status: listingData.status === 'pending_verification' ? 'available' : listingData.status || 'available',
    };

    const response = await listingsAPI.create(createData);
    return transformListing(response.listing);
  } catch (error: any) {
    console.error('Error creating listing:', error);
    throw new Error(error.message || 'Failed to create listing');
  }
};

/**
 * Updates an existing property listing
 */
export const updateListing = async (
  listingId: string,
  updates: Partial<PropertyListing>
): Promise<PropertyListing> => {
  try {
    // Prepare update data
    const updateData: UpdateListingData = {};

    if (updates.title) updateData.title = updates.title;
    if (updates.description) updateData.description = updates.description;
    if (updates.price !== undefined) updateData.price = updates.price;
    if (updates.location) {
      updateData.location = {
        county: updates.location.county,
        area: updates.location.neighborhood,
        address: updates.location.address,
      };
    }
    if (updates.bedrooms) updateData.bedrooms = updates.bedrooms;
    if (updates.bathrooms) updateData.bathrooms = updates.bathrooms;
    if (updates.areaSqFt) updateData.area_sq_ft = updates.areaSqFt;
    if (updates.amenities) updateData.amenities = updates.amenities;
    if (updates.status) updateData.status = updates.status === 'pending_verification' ? 'available' : updates.status;

    const response = await listingsAPI.update(listingId, updateData);
    return transformListing(response.listing);
  } catch (error: any) {
    console.error(`Error updating listing ${listingId}:`, error);
    throw new Error(error.message || 'Failed to update listing');
  }
};

/**
 * Deletes a property listing
 */
export const deleteListing = async (listingId: string): Promise<void> => {
  try {
    await listingsAPI.delete(listingId);
  } catch (error: any) {
    console.error(`Error deleting listing ${listingId}:`, error);
    throw new Error(error.message || 'Failed to delete listing');
  }
};

/**
 * Uploads a property image
 */
export const uploadPropertyImage = async (file: File): Promise<string> => {
  try {
    const response = await uploadAPI.uploadImage(file);
    return response.url;
  } catch (error: any) {
    console.error('Error uploading image:', error);
    throw new Error(error.message || 'Failed to upload image');
  }
};

/**
 * Deletes a single image by id from a listing
 */
export const deleteListingImage = async (imageId: string): Promise<void> => {
  try {
    await imagesAPI.delete(imageId);
  } catch (error: any) {
    // If endpoint not deployed yet (404) or image already gone, treat as non-fatal
    if (error.message && /404/.test(error.message)) {
      console.warn(`Image ${imageId} not found on server (404). Skipping.`);
      return;
    }
    console.error(`Error deleting image ${imageId}:`, error);
    throw new Error(error.message || 'Failed to delete image');
  }
};

/**
 * Gets agent metrics and statistics
 * Note: This will need to be implemented on the backend if needed
 */
export const getAgentMetrics = async (agentId: string): Promise<AgentMetrics> => {
  try {
    // Get all listings for this agent
    const response = await listingsAPI.getAll({ agent_id: agentId });
    const listings = response.listings;

    // Calculate metrics
    const totalListings = listings.length;
    const totalViews = listings.reduce((sum, listing) => sum + listing.views, 0);
    const totalSaves = listings.reduce((sum, listing) => sum + listing.saves, 0);
    const activeListings = listings.filter(l => l.status === 'available').length;

    return {
      totalListings,
      activeListings,
      totalViews,
      totalSaves,
      totalInquiries: 0, // This would need to come from a separate inquiries API
      averageRating: undefined,
    };
  } catch (error: any) {
    console.error(`Error fetching agent metrics for ${agentId}:`, error);
    throw new Error(error.message || 'Failed to fetch agent metrics');
  }
};

/**
 * Export all listing service functions
 */
export const listingService = {
  getListings,
  getListingById,
  createListing,
  updateListing,
  deleteListing,
  uploadPropertyImage,
  deleteListingImage,
  getAgentMetrics,
};
