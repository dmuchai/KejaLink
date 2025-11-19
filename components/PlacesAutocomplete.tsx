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
  const isSelectingPlace = useRef(false);
  const selectedValue = useRef<string>('');
  const onChangeRef = useRef(onChange);
  const onPlaceSelectRef = useRef(onPlaceSelect);
  const [isLoaded, setIsLoaded] = useState(false);
  const [hasError, setHasError] = useState(false);

  // Keep refs up to date
  useEffect(() => {
    onChangeRef.current = onChange;
    onPlaceSelectRef.current = onPlaceSelect;
  }, [onChange, onPlaceSelect]);

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

        // Set flag to prevent onChange from interfering
        isSelectingPlace.current = true;

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

        // Store the selected value FIRST
        selectedValue.current = selectedPlace.address;
        
        // Set flag to prevent onChange handler from overriding
        isSelectingPlace.current = true;
        
        // Update the input field directly
        if (inputRef.current) {
          inputRef.current.value = selectedPlace.address;
        }
        
        // Update parent's state with the selected address
        onChangeRef.current(selectedPlace.address);
        
        // Notify parent component with full place data (coordinates, etc.)
        if (onPlaceSelectRef.current) {
          onPlaceSelectRef.current(selectedPlace);
        }

        // Reset flag after both callbacks complete
        setTimeout(() => {
          isSelectingPlace.current = false;
        }, 1000); // Increased to 1 second
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
  }, [isLoaded]); // Only re-run when Google Maps loads, not on every value change

  // Update input value when prop changes (e.g., when editing a listing)
  useEffect(() => {
    if (inputRef.current && value && !isSelectingPlace.current) {
      inputRef.current.value = value;
    }
  }, [value]);

  return (
    <div>
      <Input
        ref={inputRef}
        label={label}
        defaultValue={value}
        onChange={(e) => {
          const inputValue = e.target.value;
          console.log('Input onChange fired. isSelectingPlace:', isSelectingPlace.current, 'selectedValue:', selectedValue.current, 'inputValue:', inputValue);
          
          // FIRST: Block if Google Places is currently selecting
          if (isSelectingPlace.current) {
            console.log('onChange blocked - Google Places is selecting');
            return;
          }
          
          // SECOND: If we have a selected value from Google, protect it
          if (selectedValue.current) {
            // If input value matches selected value, ignore the onChange
            if (inputValue === selectedValue.current) {
              console.log('onChange blocked - value matches selected place');
              return;
            }
            // If user is deleting characters (input shorter than selected), they're editing - clear selected value
            if (inputValue.length < selectedValue.current.length) {
              console.log('User is editing selected value - clearing selection');
              selectedValue.current = '';
            } else {
              // Input is longer but doesn't match - user typed after selecting, ignore it
              console.log('onChange blocked - preserving selected value');
              return;
            }
          }
          
          // If we get here, it's a normal user typing - update parent
          onChange(inputValue);
        }}
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
