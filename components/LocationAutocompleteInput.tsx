import React, { useEffect, useRef, useState } from 'react';
import Input from './Input';

interface LocationAutocompleteInputProps {
  label: string;
  name: string;
  value: string;
  onChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  onPlaceSelected?: (place: google.maps.places.PlaceResult) => void;
  required?: boolean;
  placeholder?: string;
}

/**
 * LocationAutocompleteInput
 * Uses Google Places Autocomplete to suggest addresses as user types
 * Calls onPlaceSelected with full place details including lat/lng when a place is selected
 */
const LocationAutocompleteInput: React.FC<LocationAutocompleteInputProps> = ({
  label,
  name,
  value,
  onChange,
  onPlaceSelected,
  required = false,
  placeholder = 'Start typing an address...'
}) => {
  const inputRef = useRef<HTMLInputElement>(null);
  const autocompleteRef = useRef<google.maps.places.Autocomplete | null>(null);
  const [isLoaded, setIsLoaded] = useState(false);

  useEffect(() => {
    // Check if Google Maps API is loaded
    if (!window.google || !window.google.maps || !window.google.maps.places) {
      console.error('Google Maps Places API not loaded');
      return;
    }

    if (!inputRef.current || autocompleteRef.current) {
      return;
    }

    try {
      // Initialize autocomplete with Kenya bias
      const autocomplete = new google.maps.places.Autocomplete(inputRef.current, {
        componentRestrictions: { country: 'ke' }, // Restrict to Kenya
        fields: ['address_components', 'formatted_address', 'geometry', 'name', 'place_id'],
        types: ['address', 'establishment'] // Allow both addresses and establishments
      });

      // Listen for place selection
      autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        
        if (!place.geometry || !place.geometry.location) {
          console.warn('Place has no geometry');
          return;
        }

        // Call the callback with full place details
        if (onPlaceSelected) {
          onPlaceSelected(place);
        }
      });

      autocompleteRef.current = autocomplete;
      setIsLoaded(true);
    } catch (error) {
      console.error('Error initializing autocomplete:', error);
    }

    // Cleanup
    return () => {
      if (autocompleteRef.current) {
        google.maps.event.clearInstanceListeners(autocompleteRef.current);
        autocompleteRef.current = null;
      }
    };
  }, [onPlaceSelected]);

  return (
    <Input
      ref={inputRef}
      label={label}
      name={name}
      value={value}
      onChange={onChange}
      required={required}
      placeholder={placeholder}
      autoComplete="off" // Disable browser autocomplete
    />
  );
};

export default LocationAutocompleteInput;
