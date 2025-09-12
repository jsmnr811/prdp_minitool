<div class="row g-4" wire:ignore.self x-data="window.mapSearch(@js($provinceGeo), @js($temporaryGeo), @js($provinceBoundaries), @js($selectedProvinceId))" x-init="initMap()">
    <!-- Mobile: Stack vertically, Desktop: Side by side -->
    <div class="col-12 col-lg-9 order-2 order-lg-1">
        <div class="card shadow-sm p-3 p-md-4">
            <!-- Province Dropdown for Role 1 -->
            @if($userRole == 1)
            <div class="mb-3">
                <label for="province-select" class="form-label fw-bold">Zoom to Province</label>
                <select wire:model.live="selectedProvinceId" id="province-select" class="form-select form-select-sm">
                    <option value="">Select Province to Zoom</option>
                    <option value="-1">All Provinces</option>
                    @foreach($allProvinces as $province)
                    <option value="{{ $province['id'] }}">{{ $province['name'] }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Search Box -->
            @include('geomapping.iplan.mini-comp.search-box')

            <!-- Map Container with Loading Indicator - Responsive height -->
            <div class="position-relative">
                @if($isLoadingMap)
                <div class="d-flex justify-content-center align-items-center position-absolute top-0 start-0 w-100 bg-light bg-opacity-75 rounded shadow-sm" style="z-index: 1000; height: 60vh; min-height: 400px;">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-2" role="status">
                            <span class="visually-hidden">Loading map...</span>
                        </div>
                        <div class="text-muted">Initializing map...</div>
                    </div>
                </div>
                @endif
                <div wire:ignore id="map" class="rounded shadow-sm" style="height: 60vh; min-height: 400px; max-height: 800px;"></div>
            </div>
            <!-- Map Helper -->
            @include('geomapping.iplan.mini-comp.map-helper')
        </div>
    </div>

    <!-- Sidebar - Mobile: Full width, Desktop: Narrow -->
    <div class="col-12 col-lg-3 order-1 order-lg-2">
        <!-- Commodity Toggles -->
        @include('geomapping.iplan.mini-comp.commodity-toggles')

        <!-- Save Button - Mobile optimized -->
        <div class="card shadow-sm p-3 mt-3">
            <button
                wire:click="saveUpdates"
                :disabled="$wire.isSaving"
                class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2 btn-sm d-block d-md-flex"
            >
                @if($isSaving)
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Saving...</span>
                </div>
                <span class="d-none d-md-inline">Saving...</span>
                <span class="d-md-none">Saving...</span>
                @else
                <i class="bi bi-check-circle"></i>
                <span class="d-none d-md-inline">Save Changes</span>
                <span class="d-md-none">Save</span>
                @endif
            </button>
        </div>
    </div>
</div>
<!-- Scripts -->
@script
<script>

    window.mapSearch = function(provinceGeo, temporaryGeo, provinceBoundaries, selectedProvinceId) {
        return {
            // Reactive state
            query: '',
            results: [],
            open: false,
            selectedLabel: '',
            lat: 12.8797,
            lon: 121.7740,
            hasMarker: false,

            // Map and markers
            map: null,
            marker: null,
            markersProvince: [],
            markersTemporary: [],

            // Province polygons
            provincePolygons: [],

            // Mask layers for cleanup
            maskLayers: [],

            // Data
            provinceGeo,
            temporaryGeo,
            provinceBoundaries,
            selectedProvinceId,

            /** Show error alert using SweetAlert2 */
            showErrorAlert(message) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(message);
                }
            },

            /** Validate latitude and longitude coordinates */
            isValidCoordinates(lat, lng) {
                return !isNaN(lat) && !isNaN(lng) &&
                       lat >= -90 && lat <= 90 &&
                       lng >= -180 && lng <= 180;
            },

            /** Initialize map and related features */
            initMap() {
                try {
                    this.setupMap();
                    this.map.options.fadeAnimation = false;
                    this.map.options.zoomAnimation = false;
                    this.addMarkers(this.provinceGeo, false);
                    this.addMarkers(this.temporaryGeo, true);

                            // Province boundaries are now loaded directly in mount, so add polygons immediately
                                            console.log('Adding province polygons with pre-loaded data...');
                                            this.addProvincePolygons();

                                            // Add province visibility mask for role 2 users or role 1 when specific province selected
                                            if (@js($userRole) == 2 || (@js($userRole) == 1 && this.selectedProvinceId && this.selectedProvinceId !== -1)) {
                                                this.addProvinceVisibilityMask();
                                            }

                            this.bindLivewireEvents();
                            this.map.on('click', this.handleMapClick.bind(this));
                            this.map.on('zoomstart', () => this.map.closePopup());

                            this.initTooltips();

                            console.log('Map initialized successfully');
                } catch (error) {
                    console.error('Error initializing map:', error);
                    this.showErrorAlert('Failed to initialize map. Please refresh the page.');
                }
            },

            setupMap() {
                try {
                    // Default bounds for Philippines
                    let bounds = L.latLngBounds(
                        L.latLng(4.215806, 116.931885),
                        L.latLng(21.321780, 126.604385)
                    );
                    let initialZoom = 6;
                    let initialCenter = [this.lat, this.lon];

                    // For role 2 users, restrict to their province boundaries
                    if (@js($userRole) == 2 && this.provinceBoundaries && this.provinceBoundaries.length > 0) {
                        const province = this.provinceBoundaries[0];
                        if (province.latitude && province.longitude) {
                            // Set view to user's province center
                            initialCenter = [province.latitude, province.longitude];
                            initialZoom = 9; // Closer zoom for province view

                            // Create bounds based on province geometry to allow panning within province
                            try {
                                const geometry = JSON.parse(province.boundary_geojson);
                                if (geometry.type === 'Polygon') {
                                    const coords = geometry.coordinates[0];
                                    const lats = coords.map(coord => coord[1]);
                                    const lngs = coords.map(coord => coord[0]);

                                    const minLat = Math.min(...lats);
                                    const maxLat = Math.max(...lats);
                                    const minLng = Math.min(...lngs);
                                    const maxLng = Math.max(...lngs);

                                    // Calculate province dimensions
                                    const latRange = maxLat - minLat;
                                    const lngRange = maxLng - minLng;

                                    // Add padding but keep it reasonable for navigation
                                    const latPadding = Math.max(latRange * 0.2, 0.05); // At least 0.05 degrees
                                    const lngPadding = Math.max(lngRange * 0.2, 0.05); // At least 0.05 degrees

                                    bounds = L.latLngBounds(
                                        L.latLng(minLat - latPadding, minLng - lngPadding),
                                        L.latLng(maxLat + latPadding, maxLng + lngPadding)
                                    );
                                }
                            } catch (error) {
                                console.warn('Error setting province bounds:', error);
                            }
                        }
                    }

                    this.map = L.map('map', {
                        maxBounds: bounds,
                        maxBoundsViscosity: 1.0,
                        minZoom: @js($userRole) == 2 ? 8 : 5, // Allow role 2 to zoom in more but restrict zoom out
                        maxZoom: 18
                    }).setView(initialCenter, initialZoom);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                    }).addTo(this.map);
                } catch (error) {
                    console.error('Error setting up map:', error);
                    throw new Error('Failed to setup map. Please check your internet connection.');
                }
            },

            initTooltips() {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    new bootstrap.Tooltip(el);
                });
            },

            /** Add markers from geo data */
            addMarkers(data, isTemporary) {
                const markerList = isTemporary ? this.markersTemporary : this.markersProvince;

                // Close popups and remove existing markers to avoid Leaflet internal errors
                markerList.forEach(m => {
                    if (this.map.hasLayer(m)) {
                        m.closePopup();
                        this.map.removeLayer(m);
                    }
                });
                markerList.length = 0;

                const points = Array.isArray(data[0]) ? data.flat() : data;
                points.forEach(entry => {
                    if (!entry.latitude || !entry.longitude || !entry.commodity) return;

                    const icon = this.createIcon(entry.commodity.icon);
                    const marker = L.marker([entry.latitude, entry.longitude], {
                        icon
                    }).addTo(this.map);

                    const popupContent = this.getPopupHTML(entry, isTemporary);

                    marker.bindPopup(popupContent);

                    marker.on('popupopen', e => {
                        const popupEl = e.popup.getElement();

                        const deleteBtn = popupEl.querySelector('.btn-delete-temp');
                        if (deleteBtn) {
                            deleteBtn.onclick = () => {
                                console.log('Deleting commodity', entry.commodity.id, 'temp:',
                                    isTemporary);
                                window.deleteTempCommodity(entry.commodity.id, isTemporary ? 1 :
                                    0);
                            };
                        }
                    });

                    marker.on('popupclose', e => {
                        console.log('Popup closed for commodity', entry.commodity.id);
                    });

                    markerList.push(marker);
                });
            },

            /** Add province polygons to the map */
            addProvincePolygons() {
                console.log('addProvincePolygons called with boundaries:', this.provinceBoundaries);

                // Clear existing polygons
                this.provincePolygons.forEach(polygon => {
                    if (this.map.hasLayer(polygon)) {
                        this.map.removeLayer(polygon);
                    }
                });
                this.provincePolygons.length = 0;

                if (!this.provinceBoundaries || this.provinceBoundaries.length === 0) {
                    console.log('No province boundaries to display');
                    return;
                }

                console.log('Processing ' + this.provinceBoundaries.length + ' province boundaries');

                let filteredBoundaries = this.provinceBoundaries;
                if (this.selectedProvinceId && this.selectedProvinceId !== -1 && this.selectedProvinceId !== '') {
                    filteredBoundaries = this.provinceBoundaries.filter(p => p.id == this.selectedProvinceId);
                }

                filteredBoundaries.forEach(province => {
                    if (!province.boundary_geojson) {
                        return;
                    }

                    try {
                        const geometry = JSON.parse(province.boundary_geojson);

                        if (geometry.type === 'Polygon') {
                            // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
                            const leafletCoords = geometry.coordinates[0].map(coord => [coord[1], coord[0]]);

                            // Highlight user's province for role 2
                            const isUserProvince = @js($userRole) == 2 && this.provinceBoundaries.length === 1;

                            const polygon = L.polygon(leafletCoords, {
                                color: isUserProvince ? '#dc2626' : '#496B4A', // Red for user's province, green for others
                                weight: isUserProvince ? 3 : 2, // Thicker border for user's province
                                opacity: 0.9,
                                fill: false, // No fill, just borders
                                interactive: false // Don't interfere with map clicks
                            }).addTo(this.map);

                            this.provincePolygons.push(polygon);
                            console.log('Added polygon for province:', province.name);

                        } else if (geometry.type === 'MultiPolygon') {
                            // Highlight user's province for role 2
                            const isUserProvince = @js($userRole) == 2 && this.provinceBoundaries.length === 1;

                            geometry.coordinates.forEach(coords => {
                                // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
                                const leafletCoords = coords[0].map(coord => [coord[1], coord[0]]);

                                const polygon = L.polygon(leafletCoords, {
                                    color: isUserProvince ? '#dc2626' : '#496B4A', // Red for user's province, green for others
                                    weight: isUserProvince ? 3 : 2, // Thicker border for user's province
                                    opacity: 0.9,
                                    fill: false, // No fill, just borders
                                    interactive: false // Don't interfere with map clicks
                                }).addTo(this.map);

                                this.provincePolygons.push(polygon);
                                console.log('Added multipolygon part for province:', province.name);
                            });
                        }
                    } catch (error) {
                        console.error('Error parsing province boundary for', province.name, ':', error);
                    }
                });
            },


            /** Check if a point is within the user's province boundaries */
            isPointInProvince(lat, lng) {
                if (!this.provinceBoundaries || this.provinceBoundaries.length === 0) {
                    return false;
                }

                // Check each province boundary
                for (const province of this.provinceBoundaries) {
                    if (!province.boundary_geojson) {
                        continue;
                    }

                    try {
                        const geometry = JSON.parse(province.boundary_geojson);

                        if (geometry.type === 'Polygon') {
                            // Convert coordinates from [lng, lat] to [lat, lng] for consistency
                            const polygonCoords = geometry.coordinates[0].map(coord => [coord[1], coord[0]]);

                            if (this.pointInPolygon([lat, lng], polygonCoords)) {
                                return true;
                            }

                        } else if (geometry.type === 'MultiPolygon') {
                            // Check each polygon in the multipolygon
                            for (const coords of geometry.coordinates) {
                                const polygonCoords = coords[0].map(coord => [coord[1], coord[0]]);

                                if (this.pointInPolygon([lat, lng], polygonCoords)) {
                                    return true;
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Error checking point in province for', province.name, ':', error);
                    }
                }

                return false;
            },

            /** Point in polygon algorithm using ray casting */
            pointInPolygon(point, polygon) {
                const [x, y] = point;
                let inside = false;

                for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
                    const [xi, yi] = polygon[i];
                    const [xj, yj] = polygon[j];

                    if (((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi)) {
                        inside = !inside;
                    }
                }

                return inside;
            },

            /** Update province visibility mask based on current selection */
            updateProvinceVisibilityMask() {
                console.log('updateProvinceVisibilityMask called with selectedProvinceId:', this.selectedProvinceId, 'userRole:', @js($userRole));

                // Remove existing mask first
                this.removeProvinceVisibilityMask();

                // Apply mask if conditions are met
                const shouldApplyMask = @js($userRole) == 2 ||
                    (@js($userRole) == 1 && this.selectedProvinceId && this.selectedProvinceId !== -1);

                console.log('Should apply mask:', shouldApplyMask);

                if (shouldApplyMask) {
                    this.addProvinceVisibilityMask();
                }
            },

            /** Remove existing province visibility mask */
            removeProvinceVisibilityMask() {
                // Remove all stored mask layers
                this.maskLayers.forEach(layer => {
                    if (this.map.hasLayer(layer)) {
                        this.map.removeLayer(layer);
                    }
                });
                this.maskLayers.length = 0; // Clear the array
            },

            /** Add province visibility mask for role 2 users or role 1 when specific province selected */
            addProvinceVisibilityMask() {
                console.log('addProvinceVisibilityMask called');
                console.log('provinceBoundaries:', this.provinceBoundaries);
                console.log('selectedProvinceId:', this.selectedProvinceId);
                console.log('userRole:', @js($userRole));

                if (!this.provinceBoundaries || this.provinceBoundaries.length === 0) {
                    console.log('No province boundaries available, skipping mask creation');
                    return;
                }

                // Determine which provinces to show as cutouts
                let provincesToShow = this.provinceBoundaries;

                // For role 1 users with specific province selected, only show that province
                if (@js($userRole) == 1 && this.selectedProvinceId && this.selectedProvinceId !== -1) {
                    provincesToShow = this.provinceBoundaries.filter(p => p.id == this.selectedProvinceId);
                    console.log('Filtered provinces for role 1 user:', provincesToShow);
                }

                console.log('Provinces to show as cutouts:', provincesToShow);

                if (provincesToShow.length === 0) {
                    console.log('No provinces to show, skipping mask creation');
                    return; // No provinces to show, don't apply mask
                }

                console.log('Creating world bounds mask...');

                // Create a grey/white semi-transparent mask that covers the entire world
                const worldBounds = [
                    [-90, -180], // Southwest
                    [90, -180],  // Northwest
                    [90, 180],   // Northeast
                    [-90, 180],  // Southeast
                    [-90, -180]  // Southwest (close the polygon)
                ];

                console.log('World bounds:', worldBounds);

                // Create the mask polygon (world coverage) - grey/white semi-transparent overlay
                const maskPolygon = L.polygon(worldBounds, {
                    color: '#f8f9fa',
                    weight: 0,
                    fillColor: '#f8f9fa',
                    fillOpacity: 0.7, // Semi-transparent grey/white overlay
                    interactive: true // Allow clicks to be captured
                }).addTo(this.map);

                console.log('Mask polygon created and added to map');

                // Store mask layer for cleanup
                this.maskLayers.push(maskPolygon);

                // Create a single polygon with holes for all provinces to show
                console.log('Creating polygon with holes for', provincesToShow.length, 'provinces');

                // Start with world bounds as outer ring
                const polygonCoords = [worldBounds];

                // Add each province as an inner ring (hole)
                provincesToShow.forEach(province => {
                    if (!province.boundary_geojson) {
                        console.log('No boundary_geojson for province:', province.name);
                        return;
                    }

                    try {
                        const geometry = JSON.parse(province.boundary_geojson);
                        console.log('Processing geometry for province:', province.name, 'type:', geometry.type);

                        if (geometry.type === 'Polygon') {
                            // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
                            const leafletCoords = geometry.coordinates[0].map(coord => [coord[1], coord[0]]);
                            polygonCoords.push(leafletCoords);
                            console.log('Added polygon hole for province:', province.name);

                        } else if (geometry.type === 'MultiPolygon') {
                            // For MultiPolygon, add each polygon part as a separate hole
                            geometry.coordinates.forEach((coords, index) => {
                                const leafletCoords = coords[0].map(coord => [coord[1], coord[0]]);
                                polygonCoords.push(leafletCoords);
                                console.log('Added multipolygon hole part', index, 'for province:', province.name);
                            });
                        }
                    } catch (error) {
                        console.error('Error processing province boundary for', province.name, ':', error);
                    }
                });

                console.log('Final polygon coordinates structure:', polygonCoords.length, 'rings');

                // Remove the simple world mask and create the polygon with holes
                this.map.removeLayer(maskPolygon);
                this.maskLayers = this.maskLayers.filter(layer => layer !== maskPolygon);

                // Create the mask polygon with holes
                const maskWithHoles = L.polygon(polygonCoords, {
                    color: '#f8f9fa',
                    weight: 0,
                    fillColor: '#f8f9fa',
                    fillOpacity: 0.7, // Semi-transparent grey/white overlay
                    interactive: true // Allow clicks to be captured
                }).addTo(this.map);

                console.log('Mask with holes created and added to map');

                // Store the new mask layer
                this.maskLayers.push(maskWithHoles);

                // Add click handler to mask to prevent interaction outside province (for role 2 or when specific province selected)
                maskWithHoles.on('click', (e) => {
                    // Prevent the click from propagating to map
                    L.DomEvent.stopPropagation(e);
                    // Optional: Show a message
                    console.log('Cannot interact outside the selected province boundaries');
                });
            },

            createIcon(iconPath) {
                const url = iconPath.startsWith('http') ? iconPath : `/icons/${iconPath}`;
                return L.divIcon({
                    className: 'custom-marker-icon position-relative',
                    html: `
                    <div class="marker-circle">
                        <img src="${url}" alt="Icon"
                            onerror="this.onerror=null;this.src='/icons/commodities/default.png';"
                            style="width:32px; height:32px; border-radius:50%;" />
                    </div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32],
                });
            },

            getPopupHTML(entry, isTemporary = false) {
                const name = entry.commodity.name + (isTemporary ? ' (Temporary)' : '');
                const interventions = (entry.geo_interventions && entry.geo_interventions.length > 0) ?
                    entry.geo_interventions.map(i => `<li>${i.intervention?.name || ''}</li>`).join('') :
                    '<li>No interventions</li>';

                return `
                <div class="text-center" style="min-width: 150px;">
                    <div class="fw-bold mb-2 text-primary">${name}</div>
                    <div class="small text-muted my-2 text-start">
                        <ul class="mb-0 ps-3">${interventions}</ul>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon d-flex align-items-center gap-1 btn-delete-temp">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                </div>`;
            },

            bindLivewireEvents() {
                Livewire.on('temporaryGeoUpdated', newGeo => {
                    this.temporaryGeo = newGeo.flat ? newGeo.flat() : newGeo;
                    this.addMarkers(this.temporaryGeo, true);
                });

                Livewire.on('provinceGeoUpdated', newGeo => {
                    console.log('Province geo updated event received:', newGeo);
                    this.provinceGeo = newGeo.flat ? newGeo.flat() : newGeo;
                    this.addMarkers(this.provinceGeo, false);
                });

                Livewire.on('removeMarkers', () => {
                    this.placeMarker(0, 0);
                });

                Livewire.on('zoomToProvince', (data) => {
                    console.log(data);
                    if (this.map && data.lat && data.lng && this.isValidCoordinates(data.lat, data.lng)) {
                        // Use zoom level 11 for better province visibility (within 10-12 range)
                        this.map.setView([data.lat, data.lng], 11, {
                            animate: true,
                            duration: 1.5
                        });

                        console.log('dasdas');
                        // Remove existing province marker if it exists
                        if (this.map.zoomToProvinceMarker) {
                            this.map.removeLayer(this.map.zoomToProvinceMarker);
                        }

                        // Add a marker at the province center with province name
                        this.map.zoomToProvinceMarker = L.marker([data.lat, data.lng], {
                            icon: L.divIcon({
                                className: 'province-center-marker',
                                html: '<div style="background-color: #dc3545; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.3);"></div>',
                                iconSize: [12, 12],
                                iconAnchor: [6, 6]
                            })
                        })
                        .addTo(this.map)
                        .bindPopup(`<div class="text-center"><strong>${data.name}</strong><br><small>Province Center</small></div>`)
                        .openPopup();

                        // Auto-close popup after 3 seconds
                        setTimeout(() => {
                            if (this.map.zoomToProvinceMarker) {
                                this.map.zoomToProvinceMarker.closePopup();
                            }
                        }, 3000);
                    } else {
                        console.warn('Invalid coordinates received for zoomToProvince:', data);
                        this.showErrorAlert('Unable to zoom to province: Invalid location data');
                    }
                });

                Livewire.on('selectedProvinceChanged', newSelectedProvinceId => {
                    console.log('selectedProvinceChanged event received with ID:', newSelectedProvinceId);
                    this.selectedProvinceId = newSelectedProvinceId;
                    this.addProvincePolygons();

                    // Update province visibility mask based on selection
                    this.updateProvinceVisibilityMask();
                });

                // Note: provinceBoundariesLoaded event is no longer used since we load data directly in mount
            },

            handleMapClick(e) {
                const { lat, lng } = e.latlng;

                // For role 2 users, check if click is within province boundaries
                if (@js($userRole) == 2) {
                    if (!this.isPointInProvince(lat, lng)) {
                        this.showErrorAlert(
                            'You can only place markers within your assigned province boundaries. ' +
                            'Please click on a location inside your province to add a commodity marker.'
                        );
                        return; // Don't allow marker placement outside province
                    }
                }

                this.lat = lat;
                this.lon = lng;
                this.$wire.set('lat', lat);
                this.$wire.set('lon', lng);
                this.reverseGeocode(lat, lng, true);
            },

            onInput() {
                if (this.query.length < 3) {
                    this.results = [];
                    this.open = this.hasMarker;
                    return;
                }

                this.$wire.set('query', this.query);
                this.$wire.search().then(() => {
                    this.results = this.$wire.results;
                    this.open = this.results.length > 0 || this.hasMarker;
                });
            },

            selectResult(res) {
                const lat = parseFloat(res.lat);
                const lon = parseFloat(res.lon);

                // For role 2 users, check if search result is within province boundaries
                if (@js($userRole) == 2) {
                    if (!this.isPointInProvince(lat, lon)) {
                        this.showErrorAlert(
                            'The selected location "' + res.display_name + '" is outside your province boundaries. ' +
                            'Please search for a location within your assigned province.'
                        );
                        return;
                    }
                }

                this.lat = lat;
                this.lon = lon;
                this.query = this.selectedLabel = res.display_name;

                this.$wire.set('lat', this.lat);
                this.$wire.set('lon', this.lon);

                if (!this.map) {
                    return;
                }

                this.map.setView([this.lat, this.lon], 14);
                this.placeMarker(this.lat, this.lon, this.selectedLabel);

                this.results = [];
                this.open = false;
            },

            placeMarker(lat, lon, label = '') {
                if (lat === 0 && lon === 0) {
                    if (this.marker && this.map.hasLayer(this.marker)) {
                        this.marker.closePopup();
                        this.map.removeLayer(this.marker);
                        this.marker = null;
                    }
                    this.hasMarker = false;
                    return;
                }

                const commodityOptions = @js($commodities).map(c =>
                    `<option value="${c.id}">${c.name}</option>`).join('');
                const interventionOptions = @js($interventions).map(i =>
                    `<option value="${i.id}">${i.name}</option>`).join('');

                const popupContent = `
    <div style="min-width: 300px;">
        <label for="commodity-select" class="form-label fw-bold mb-1">Commodity</label>
        <select id="commodity-select" class="form-select mb-3 text-start">
            <option value="">Select commodity</option>
            ${commodityOptions}
        </select>

        <label for="intervention-select" class="form-label fw-bold mb-1">Interventions</label>
        <select id="intervention-select" class="form-select mb-3 text-start" multiple>
            ${interventionOptions}
        </select>

        <div class="d-grid mt-2">
            <button id="add-commodity-btn" class="btn btn-sm btn-success">Add</button>
        </div>
    </div>`;

                if (!this.marker) {
                    // Create marker for the first time
                    this.marker = L.marker([lat, lon], {
                        draggable: true
                    }).addTo(this.map);

                    this.marker.bindPopup(popupContent);

                    this.marker.on('popupopen', e => {
                        this.setupPopupHandlers(e.popup.getElement(), lat, lon);
                    });

                    this.marker.on('dragend', e => {
                        const newPos = e.target.getLatLng();
                        this.lat = newPos.lat;
                        this.lon = newPos.lng;

                        this.$wire.set('lat', this.lat);
                        this.$wire.set('lon', this.lon);
                        // Do NOT call placeMarker here or openPopup again to avoid animation conflicts
                    });

                    this.marker.openPopup(); // Open popup only once here
                } else {
                    // Update marker position and popup content without reopening popup
                    this.marker.setLatLng([lat, lon]);

                    // Update popup content (optional)
                    this.marker.setPopupContent(popupContent);
                }

                this.hasMarker = true;
                this.selectedLabel = label;
            },

            setupPopupHandlers(popupEl) {
                const component = Alpine.closestDataStack(popupEl)?.find(x => x.$wire)?.$wire;
                if (!component) {
                    console.warn('Livewire component not found in popup');
                    return;
                }

                const commoditySelect = popupEl.querySelector('#commodity-select');
                const interventionSelect = popupEl.querySelector('#intervention-select');
                const addBtn = popupEl.querySelector('#add-commodity-btn');

                let selectedCommodity = null;
                let selectedInterventions = [];

                if (commoditySelect) {
                    $(commoditySelect).select2({
                        dropdownParent: $(popupEl),
                        width: '100%',
                        placeholder: 'Select commodity'
                    }).on('change', function() {
                        selectedCommodity = this.value;
                    });
                }

                if (interventionSelect) {
                    $(interventionSelect).select2({
                        dropdownParent: $(popupEl),
                        width: '100%',
                        placeholder: 'Select interventions',
                        multiple: true
                    }).on('change', function() {
                        selectedInterventions = $(this).val() || [];
                    });
                }

                if (addBtn) {
                    addBtn.addEventListener('click', () => {
                        if (!selectedCommodity || selectedInterventions.length === 0) {
                            swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Please select a commodity and at least one intervention.'
                            })
                            return;
                        }

                        const markerLatLng = this.marker.getLatLng();

                        component.set('lat', markerLatLng.lat);
                        component.set('lon', markerLatLng.lng);
                        component.set('selectedCommodity', selectedCommodity);
                        component.set('selectedInterventions', selectedInterventions);
                        component.addTempCommodity();
                        this.map.closePopup();
                    });
                }

                const markerLatLng = this.marker.getLatLng();
                component.set('lat', markerLatLng.lat);
                component.set('lon', markerLatLng.lng);
            },
            reverseGeocode(lat, lon, updateMap = false) {
                try {
                    // For role 2 users, validate location before reverse geocoding
                    if (@js($userRole) == 2 && updateMap) {
                        if (!this.isPointInProvince(lat, lon)) {
                            this.showErrorAlert('Location is outside your province boundaries');
                            return;
                        }
                    }

                    fetch(
                            `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&addressdetails=1&countrycodes=ph`
                        )
                        .then(res => {
                            if (!res.ok) {
                                throw new Error(`HTTP error! status: ${res.status}`);
                            }
                            return res.json();
                        })
                        .then(data => {
                            const name = data.display_name || `Lat: ${lat.toFixed(5)}, Lng: ${lon.toFixed(5)}`;
                            this.query = this.selectedLabel = name;
                            this.$wire.set('query', name);
                            this.results = [];
                            this.open = false;
                            if (updateMap) {
                                this.map.setView([lat, lon], this.map.getZoom());
                                this.placeMarker(lat, lon, name);
                            }
                        })
                        .catch(err => {
                            console.warn('Reverse geocoding failed:', err);
                            const fallback = `Lat: ${lat.toFixed(5)}, Lng: ${lon.toFixed(5)}`;
                            this.query = this.selectedLabel = fallback;
                            this.results = [];
                            this.open = false;
                            if (updateMap) this.placeMarker(lat, lon, fallback);
                        });
                } catch (error) {
                    console.error('Error in reverse geocoding:', error);
                    this.showErrorAlert('Failed to get location information');
                }
            },
        };
    };

    window.deleteTempCommodity = function(commodityId, isTemp) {
        if (!commodityId) return;
        Livewire.dispatch('deleteTempCommodity', {
            payload: {
                id: commodityId,
                isTemp
            },
        });
    };
</script>
@endscript
