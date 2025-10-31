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
    const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
    const maxSizeMB = (MAX_FILE_SIZE / 1024 / 1024).toFixed(0);
    return {
      valid: false,
      error: `File "${file.name}" is too large (${fileSizeMB}MB). Maximum allowed size is ${maxSizeMB}MB. Please compress or resize your image.`,
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
 * Images are automatically linked to the listing via the backend API
 * 
 * @param {string} listingId - The ID of the listing to associate images with
 * @param {File[]} files - Array of image files to upload
 * @returns {Promise<string[]>} Array of public URLs for successfully uploaded images
 */
export async function uploadImagesToStorageAndSaveMetadata(
  listingId: string,
  files: File[]
): Promise<string[]> {
  const uploadedUrls: string[] = [];
  const errors: string[] = [];

  for (const file of files) {
    // Validate file
    const validation = validateImageFile(file);
    if (!validation.valid) {
      console.error(`File ${file.name} validation failed:`, validation.error);
      errors.push(`${file.name}: ${validation.error}`);
      continue;
    }

    try {
      // Upload to server WITH listing_id so backend can create the property_images record
      const response = await uploadAPI.uploadImage(file, listingId);
      uploadedUrls.push(response.url);
      console.log(`Successfully uploaded ${file.name} to ${response.url} and linked to listing ${listingId}`);
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
 * Export all image upload functions
 */
export default {
  uploadImages,
  uploadImagesToStorageAndSaveMetadata,
  validateImageFile,
  MAX_FILE_SIZE,
  SUPPORTED_IMAGE_TYPES,
};
