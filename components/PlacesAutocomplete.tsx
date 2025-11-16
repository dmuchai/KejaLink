import React, { useEffect, useRef, useState } from 'react';
import Input from './Input';

interface PlacesAutocompleteProps {
  value: string;
  onChange: (value: string) => void;
  onPlaceSelect?: (place: {
    address: string;
    latitude: number;
    longitude: number;
    city?: string;
    county?: string;
  }) => void;
  label?: string;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
}

export const PlacesAutocomplete: React.FC<PlacesAutocompleteProps> = ({
  value,
  onChange,
  onPlaceSelect,
  label = 'Address',
  placeholder = 'Start typing an address...',
  required = false,
  disabled = false,
}) => {
  const inputRef = useRef<HTMLInputElement>(null);
  const autocompleteRef = useRef<google.maps.places.Autocomplete | null>(null);
  const [isLoaded, setIsLoaded] = useState(false);
  const [hasError, setHasError] = useState(false);

  useEffect(() => {
    // Check if Google Maps API is loaded
    if (typeof google !== 'undefined' && google.maps && google.maps.places) {
      setIsLoaded(true);
    } else {
      console.warn('Google Maps Places API not loaded yet');
      // Fall back to regular input after 5 seconds
      setTimeout(() => {
        if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
          setHasError(true);
        }
      }, 5000);
    }
  }, []);

  useEffect(() => {
    if (!isLoaded || !inputRef.current || autocompleteRef.current) return;

    try {
      // Initialize autocomplete restricted to Kenya
      const autocomplete = new google.maps.places.Autocomplete(inputRef.current, {
        componentRestrictions: { country: 'ke' },
        fields: ['address_components', 'formatted_address', 'geometry', 'name'],
        types: ['address'],
      });

      autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();

        if (!place.geometry || !place.geometry.location) {
          console.warn('No geometry found for selected place');
          return;
        }

        // Extract address components
        let city = '';
        let county = '';
        
        if (place.address_components) {
          for (const component of place.address_components) {
            const types = component.types;
            
            // Get city (locality or administrative_area_level_2)
            if (types.includes('locality')) {
              city = component.long_name;
            } else if (types.includes('administrative_area_level_2') && !city) {
              city = component.long_name;
            }
            
            // Get county (administrative_area_level_1)
            if (types.includes('administrative_area_level_1')) {
              county = component.long_name;
            }
          }
        }

        const selectedPlace = {
          address: place.formatted_address || place.name || value,
          latitude: place.geometry.location.lat(),
          longitude: place.geometry.location.lng(),
          city: city || undefined,
          county: county || undefined,
        };

        console.log('Place selected:', selectedPlace);

        // Update the input value
        onChange(selectedPlace.address);

        // Notify parent component
        if (onPlaceSelect) {
          onPlaceSelect(selectedPlace);
        }
      });

      autocompleteRef.current = autocomplete;
    } catch (error) {
      console.error('Error initializing Google Places Autocomplete:', error);
      setHasError(true);
    }

    return () => {
      if (autocompleteRef.current) {
        google.maps.event.clearInstanceListeners(autocompleteRef.current);
      }
    };
  }, [isLoaded, onPlaceSelect, onChange, value]);

  return (
    <div>
      <Input
        ref={inputRef}
        label={label}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder={placeholder}
        required={required}
        disabled={disabled}
      />
      {!isLoaded && !hasError && (
        <p className="text-xs text-gray-500 mt-1">Loading address autocomplete...</p>
      )}
      {hasError && (
        <p className="text-xs text-amber-600 mt-1">
          ⚠️ Address autocomplete unavailable. Please enter the full address manually, then add coordinates by editing the listing later.
        </p>
      )}
    </div>
  );
};
