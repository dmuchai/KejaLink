export enum UserRole {
  TENANT = 'tenant',
  AGENT = 'agent',
  ADMIN = 'admin',
}

export interface User {
  id: string;
  email: string;
  name: string;
  role: UserRole;
  isVerifiedAgent: boolean;
  profilePictureUrl?: string;
  phoneNumber?: string;
  createdAt: string;
}

export interface PropertyImage {
  id: string;
  url: string;
  altText?: string;
  displayOrder?: number;  // Order of image display (not rendered in UI)
  aiScanStatus: 'pending' | 'clear' | 'flagged_reused' | 'flagged_poor_quality';
  aiScanReason?: string;
}

export type PropertyType = 'apartment' | 'studio' | 'bedsitter' | 'bungalow' | 'maisonette' | 'townhouse';

export interface PropertyListing {
  id:string;
  agent: User;
  title: string;
  description: string;
  propertyType?: PropertyType;
  location: {
    address: string;
    county: string;
    neighborhood: string;
    lat?: number;
    lng?: number;
  };
  price: number; // KES
  bedrooms: number;
  bathrooms: number;
  areaSqFt?: number;
  amenities: string[];
  images: PropertyImage[];
  status: 'available' | 'rented' | 'pending_verification';
  isFeatured: boolean;
  createdAt: string;
  updatedAt: string;
  views: number;
  saves: number;
  // Optional rating summary fields (populated if backend provides)
  ratingAverage?: number; // 0-5
  ratingCount?: number;   // total number of reviews
}

export interface RatingReview {
  id: string;
  raterUser: User;
  ratedAgent: User;
  propertyId?: string; // Optional, if review is for a specific property interaction
  rating: number; // 1-5
  comment: string;
  createdAt: string;
  isVerifiedInteraction: boolean; // e.g. if tenant actually contacted/viewed via platform
}

export interface AgentMetrics {
  totalListings: number;
  activeListings: number;
  totalViews: number;
  totalSaves: number;
  totalInquiries: number;
  averageRating?: number;
}

export interface AiEnhancedContent {
  enhancedDescription?: string;
  suggestedTitle?: string;
  pricingAdvice?: string;
}

export interface RentEstimate {
  location: string;
  bedrooms: number;
  minRent: number;
  maxRent: number;
  averageRent: number;
  confidence: 'high' | 'medium' | 'low';
  lastUpdated: string;
}

export interface GroundingChunk {
  web?: {
    uri: string;
    title: string;
  };
  retrievedContext?: {
    uri: string;
    title: string;
  };
}
