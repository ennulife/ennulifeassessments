/**
 * ENNU LabCorp Locator JavaScript
 * Find nearest LabCorp locations with Google Maps integration
 * 
 * @package ENNU_Life
 * @since 78.1.0
 */

(function($) {
    'use strict';

    // Global variables
    let map, marker, infoWindow, markers = [];
    let userLocation = null;

    /**
     * Initialize the LabCorp locator
     */
    function initLabCorpLocator() {
        console.log('Initializing LabCorp locator...');
        
        // Check if Google Maps is available
        if (typeof google === 'undefined' || !google.maps) {
            console.error('Google Maps not loaded');
            showError('Google Maps could not be loaded. Please check your internet connection.');
            return;
        }

        // Initialize map
        initMap();
        
        // Bind event handlers
        bindEvents();
        
        console.log('LabCorp locator initialized successfully');
    }

    /**
     * Initialize Google Map
     */
    function initMap() {
        const mapElement = document.getElementById('locator-map');
        if (!mapElement) {
            console.error('Map element not found');
            return;
        }

        // Create map centered on US
        map = new google.maps.Map(mapElement, {
            zoom: 4,
            center: { lat: 39.8283, lng: -98.5795 }, // Center of US
            styles: [
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ],
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true
        });

        // Create info window
        infoWindow = new google.maps.InfoWindow();
        
        // Add map loaded class for animations
        setTimeout(() => {
            mapElement.classList.add('loaded');
        }, 500);

        console.log('Map initialized');
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Search button
        $('#locator-search-btn').on('click', function() {
            const address = $('#locator-address').val().trim();
            if (address) {
                geocodeAddress(address);
            } else {
                showError('Please enter an address or use geolocation.');
            }
        });

        // Geolocation button
        $('#locator-geolocate-btn').on('click', function() {
            getUserLocation();
        });

        // Enter key on address input
        $('#locator-address').on('keypress', function(e) {
            if (e.which === 13) {
                $('#locator-search-btn').click();
            }
        });

        console.log('Event handlers bound');
    }

    /**
     * Get user's current location
     */
    function getUserLocation() {
        if (!navigator.geolocation) {
            showError('Geolocation is not supported by this browser.');
            return;
        }

        showLoading();
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const coords = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                handleLocationSuccess(coords);
            },
            function(error) {
                hideLoading();
                let errorMessage = 'Unable to retrieve your location. ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += 'Please allow location access and try again.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += 'Location information is unavailable.';
                        break;
                    case error.TIMEOUT:
                        errorMessage += 'Location request timed out.';
                        break;
                    default:
                        errorMessage += 'An unknown error occurred.';
                        break;
                }
                showError(errorMessage);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000 // 5 minutes
            }
        );
    }

    /**
     * Geocode an address
     */
    function geocodeAddress(address) {
        if (!google.maps) {
            showError('Google Maps not available');
            return;
        }

        showLoading();
        
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ address: address }, function(results, status) {
            if (status === 'OK') {
                const location = results[0].geometry.location;
                const coords = {
                    lat: location.lat(),
                    lng: location.lng()
                };
                handleLocationSuccess(coords);
            } else {
                hideLoading();
                showError('Address not found. Please try a different address.');
            }
        });
    }

    /**
     * Handle successful location retrieval
     */
    function handleLocationSuccess(coords) {
        userLocation = coords;
        
        // Center map on location
        map.setCenter(coords);
        map.setZoom(12);
        
        // Clear existing markers
        clearMarkers();
        
        // Add user location marker
        marker = new google.maps.Marker({
            position: coords,
            map: map,
            title: 'Your Location',
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" fill="#4285f4" stroke="white" stroke-width="2"/>
                        <circle cx="12" cy="12" r="3" fill="white"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(32, 32),
                anchor: new google.maps.Point(16, 16)
            }
        });
        
        // Search for nearby LabCorp locations
        searchNearbyLabCorp(coords);
    }

    /**
     * Search for nearby LabCorp locations
     */
    function searchNearbyLabCorp(coords) {
        const data = new FormData();
        data.append('action', 'ennu_get_nearest_labcorp');
        data.append('nonce', ennuLocator.nonce);
        data.append('lat', coords.lat);
        data.append('lng', coords.lng);

        fetch(ennuLocator.ajax_url, {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(result => {
            hideLoading();
            if (result.success) {
                displayResults(result.data.locations);
            } else {
                showError(result.data || 'Failed to find LabCorp locations');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showError('Failed to search for locations. Please try again.');
        });
    }

    /**
     * Display search results
     */
    function displayResults(locations) {
        const resultsContainer = $('#locator-results');
        
        if (!locations || locations.length === 0) {
            resultsContainer.html('<div class="no-results">No LabCorp locations found nearby. Try expanding your search area.</div>');
            return;
        }

        let html = '<div class="results-header"><h4>Nearby LabCorp Locations</h4></div>';
        
        locations.forEach((location, index) => {
            // Add map marker
            addLocationMarker(location, index);
            
            // Build result card
            html += `
                <div class="location-card" data-index="${index}">
                    <div class="location-header">
                        <h5 class="location-name">${location.name}</h5>
                        <span class="location-distance">${location.distance} km</span>
                    </div>
                    <div class="location-details">
                        <div class="location-address">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2" fill="none"/>
                                <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2" fill="none"/>
                            </svg>
                            ${location.address}
                        </div>
                        ${location.phone ? `
                            <div class="location-phone">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2" fill="none"/>
                                </svg>
                                <a href="tel:${location.phone}">${location.phone}</a>
                            </div>
                        ` : ''}
                        ${location.rating ? `
                            <div class="location-rating">
                                <span class="rating-stars">${generateStars(location.rating)}</span>
                                <span class="rating-score">${location.rating}</span>
                            </div>
                        ` : ''}
                    </div>
                    <div class="location-hours">
                        <strong>Hours:</strong><br>
                        <div class="hours-content">${location.hours}</div>
                    </div>
                    <div class="location-actions">
                        <a href="https://www.google.com/maps/dir/?api=1&origin=${userLocation.lat},${userLocation.lng}&destination=${location.lat},${location.lng}" 
                           target="_blank" class="btn btn-outline">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 11H1l2-2v6l-2-2h8m0 0l-4-4m4 4l-4 4m4-4h9a4 4 0 0 1 0 8H8" stroke="currentColor" stroke-width="2" fill="none"/>
                            </svg>
                            Get Directions
                        </a>
                        ${location.website ? `
                            <a href="${location.website}" target="_blank" class="btn btn-outline">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" stroke="currentColor" stroke-width="2" fill="none"/>
                                </svg>
                                Visit Website
                            </a>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        
        resultsContainer.html(html);
        
        // Bind click events for location cards
        $('.location-card').on('click', function() {
            const index = $(this).data('index');
            const location = locations[index];
            showLocationOnMap(location);
        });
    }

    /**
     * Add location marker to map
     */
    function addLocationMarker(location, index) {
        const marker = new google.maps.Marker({
            position: { lat: location.lat, lng: location.lng },
            map: map,
            title: location.name,
            animation: google.maps.Animation.DROP,
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" fill="#e53e3e" stroke="white" stroke-width="2"/>
                        <circle cx="12" cy="10" r="3" fill="white"/>
                    </svg>
                `),
                scaledSize: new google.maps.Size(32, 32),
                anchor: new google.maps.Point(16, 32)
            }
        });

        // Add click listener
        marker.addListener('click', function() {
            showLocationOnMap(location);
        });

        markers.push(marker);
    }

    /**
     * Show location info on map
     */
    function showLocationOnMap(location) {
        const content = `
            <div class="map-info-window">
                <h6>${location.name}</h6>
                <p>${location.address}</p>
                <p><strong>Distance:</strong> ${location.distance} km</p>
                ${location.phone ? `<p><strong>Phone:</strong> <a href="tel:${location.phone}">${location.phone}</a></p>` : ''}
                <div class="info-actions">
                    <a href="https://www.google.com/maps/dir/?api=1&origin=${userLocation.lat},${userLocation.lng}&destination=${location.lat},${location.lng}" 
                       target="_blank" class="btn btn-small">Get Directions</a>
                </div>
            </div>
        `;

        infoWindow.setContent(content);
        
        // Find the marker for this location
        const marker = markers.find(m => 
            m.getPosition().lat() === location.lat && 
            m.getPosition().lng() === location.lng
        );
        
        if (marker) {
            infoWindow.open(map, marker);
            map.setCenter({ lat: location.lat, lng: location.lng });
        }
    }

    /**
     * Generate star rating HTML
     */
    function generateStars(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        let html = '';
        
        for (let i = 0; i < fullStars; i++) {
            html += '★';
        }
        
        if (hasHalfStar) {
            html += '☆';
        }
        
        return html;
    }

    /**
     * Clear all markers except user location
     */
    function clearMarkers() {
        markers.forEach(marker => marker.setMap(null));
        markers = [];
    }

    /**
     * Show loading state
     */
    function showLoading() {
        $('#locator-loading').show();
        $('#locator-error').hide();
        $('#locator-results').empty();
    }

    /**
     * Hide loading state
     */
    function hideLoading() {
        $('#locator-loading').hide();
    }

    /**
     * Show error message
     */
    function showError(message) {
        $('#locator-error').html(`
            <div class="error-content">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                    <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
                    <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
                </svg>
                ${message}
            </div>
        `).show();
    }

    // Initialize when document is ready and Google Maps is loaded
    $(document).ready(function() {
        // Wait for Google Maps to load
        function checkGoogleMaps() {
            if (typeof google !== 'undefined' && google.maps) {
                initLabCorpLocator();
            } else {
                // Retry after a short delay
                setTimeout(checkGoogleMaps, 100);
            }
        }
        
        checkGoogleMaps();
    });

})(jQuery);