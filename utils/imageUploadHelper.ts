/**
 * @fileoverview Image Upload Helper - Handles file uploads to PHP backend
 * 
 * This module provides utilities for uploading property images to the server
 * and managing image metadata. It includes:
 * - File validation (type, size)
 * - Authenticated uploads
 * - Error handling and logging
 * 
 * @author Kejalink Team
 * @version 2.0.0 - Migrated from Supabase to PHP API
 */

import { uploadAPI } from '../services/apiClient';

/**
 * Maximum file size allowed for image uploads (5MB)
 * @constant {number}
 */
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

/**
 * Supported image MIME types
 * @constant {string[]}
 */
const SUPPORTED_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

/**
 * Validates a single image file
 * 
 * @param {File} file - File to validate
 * @returns {{valid: boolean, error?: string}} Validation result
 */
export function validateImageFile(file: File): { valid: boolean; error?: string } {
  // Check file type
  if (!SUPPORTED_IMAGE_TYPES.includes(file.type)) {
    return {
      valid: false,
      error: `Unsupported file type: ${file.type}. Supported types: JPG, PNG, GIF, WEBP`,
    };
  }

  // Check file size
  if (file.size > MAX_FILE_SIZE) {
    return {
      valid: false,
      error: `File size exceeds maximum allowed size of ${MAX_FILE_SIZE / 1024 / 1024}MB`,
    };
  }

  return { valid: true };
}

/**
 * Uploads multiple image files to the server
 * 
 * This function:
 * 1. Validates each file (type, size)
 * 2. Uploads to server via API
 * 3. Returns array of public URLs for successful uploads
 * 
 * @param {File[]} files - Array of image files to upload
 * @returns {Promise<string[]>} Array of public URLs for successfully uploaded images
 * 
 * @example
 * const fileInput = document.getElementById('images');
 * const files = Array.from(fileInput.files);
 * const urls = await uploadImages(files);
 * console.log('Uploaded images:', urls);
 * 
 * @example
 * // Handle upload errors gracefully
 * try {
 *   const urls = await uploadImages(files);
 *   if (urls.length < files.length) {
 *     console.warn('Some uploads failed');
 *   }
 * } catch (error) {
 *   console.error('Upload failed:', error);
 * }
 */
export async function uploadImages(files: File[]): Promise<string[]> {
  const uploadedUrls: string[] = [];
  const errors: string[] = [];

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    
    // Validate file
    const validation = validateImageFile(file);
    if (!validation.valid) {
      console.error(`File ${file.name} validation failed:`, validation.error);
      errors.push(`${file.name}: ${validation.error}`);
      continue;
    }

    try {
      // Upload to server
      const response = await uploadAPI.uploadImage(file);
      uploadedUrls.push(response.url);
      console.log(`Successfully uploaded ${file.name} to ${response.url}`);
    } catch (error: any) {
      console.error(`Failed to upload ${file.name}:`, error);
      errors.push(`${file.name}: ${error.message || 'Upload failed'}`);
    }
  }

  // If some uploads failed, log the errors
  if (errors.length > 0) {
    console.warn('Some file uploads failed:', errors);
  }

  return uploadedUrls;
}

/**
 * Uploads images to storage and saves metadata to property_images table
 * Note: With the new API, images are uploaded and associated with listings
 * via separate API calls. This function maintains backward compatibility.
 * 
 * @param {string} listingId - The ID of the listing to associate images with
 * @param {File[]} files - Array of image files to upload
 * @returns {Promise<string[]>} Array of public URLs for successfully uploaded images
 */
export async function uploadImagesToStorageAndSaveMetadata(
  listingId: string,
  files: File[]
): Promise<string[]> {
  // Upload images
  const urls = await uploadImages(files);
  
  // Note: In the new API architecture, images are associated with listings
  // through the listings API. The frontend should call the listings update
  // endpoint with the image URLs after this function returns.
  
  return urls;
}

/**
 * Export all image upload functions
 */
export default {
  uploadImages,
  uploadImagesToStorageAndSaveMetadata,
  validateImageFile,
  MAX_FILE_SIZE,
  SUPPORTED_IMAGE_TYPES,
};
