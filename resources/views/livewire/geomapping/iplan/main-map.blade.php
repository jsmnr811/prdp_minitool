<div class="row g-4" wire:ignore.self x-data="window.mapSearch(@js($provinceGeo), @js($temporaryGeo), @js($provinceBoundaries), @js($selectedProvinceId), @js($regionBoundaries), @js($selectedRegionId))" x-init="initMap()">
    <!-- Mobile: Stack vertically, Desktop: Side by side -->
    <div class="col-12 col-lg-9 order-2 order-lg-1">
        <div class="card shadow-sm p-2 p-sm-3 p-md-4">

            {{-- <button wire:click='test'>dasdadsa</button> --}}
            <!-- Province Dropdown for Role 1 -->
            @if ($userRole == 1)
                <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-3 mb-3">
                    <div class="flex-fill">
                        <label for="region-select" class="form-label fw-bold fs-6 fs-sm-5">Zoom to Region</label>
                        <select wire:model.live="selectedRegionId" id="region-select" class="form-select form-select-sm">
                            <option value=""></option>
                            @foreach ($allRegions as $region)
                                <option value="{{ $region['code'] }}">{{ $region['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-fill">
                        <label for="province-select" class="form-label fw-bold fs-6 fs-sm-5">Zoom to Province</label>
                        <select wire:model.live="selectedProvinceId" id="province-select"
                            class="form-select form-select-sm">
                            <option value=""></option>
                            @foreach ($allProvinces as $province)
                                <option value="{{ $province['code'] }}">{{ $province['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            @endif

            <!-- Search Box -->
            @include('geomapping.iplan.mini-comp.search-box')

            <!-- Map Container with Loading Indicator - Responsive height -->
            <div class="position-relative">
                @if ($isLoadingMap)
                    <div class="d-flex justify-content-center align-items-center position-absolute top-0 start-0 w-100 bg-light bg-opacity-75 rounded shadow-sm"
                        style="z-index: 1000; height: 80vh; min-height: 400px;">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-2" role="status">
                                <span class="visually-hidden">Loading map...</span>
                            </div>
                            <div class="text-muted">Initializing map...</div>
                        </div>
                    </div>
                @elseif ($isMapRendering)
                    <div class="d-flex justify-content-center align-items-center position-absolute top-0 start-0 w-100 bg-light bg-opacity-75 rounded shadow-sm"
                        style="z-index: 1000; height: 80vh; min-height: 400px;">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-2" role="status">
                                <span class="visually-hidden">Rendering map...</span>
                            </div>
                            <div class="text-muted">Rendering map...</div>
                        </div>
                    </div>
                @endif
                <div wire:ignore id="map" class="rounded shadow-sm"
                    style="height: 50vh; min-height: 600px; max-height: 600px;" oncontextmenu="return false;"></div>
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
        <div class="card shadow-sm p-2 p-sm-3 mt-3 d-xl-none d-lg-none  d-md-none d-sm-block">
            <button wire:click="saveUpdates" :disabled="$wire.isSaving"
                class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2 py-2 py-sm-1 btn-sm">
                @if ($isSaving)
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Saving...</span>
                    </div>
                    <span class="d-none d-sm-inline">Saving...</span>
                    <span class="d-sm-none">Saving...</span>
                @else
                    <i class="bi bi-check-circle"></i>
                    <span class="d-none d-sm-inline">Save Changes</span>
                    <span class="d-sm-none">Save</span>
                @endif
            </button>
        </div>
    </div>
</div>
<!-- Scripts -->


@script
    <script>
        window.mapSearch = function(provinceGeo, temporaryGeo, provinceBoundaries, selectedProvinceId, regionBoundaries,
            selectedRegionId) {
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

                // Region polygons
                regionPolygons: [],

                // Mask layers for cleanup
                maskLayers: [],
                provinceMaskLayers: [],
                regionMaskLayers: [],

                // Data
                provinceGeo,
                temporaryGeo,
                provinceBoundaries,
                selectedProvinceId,
                regionBoundaries,
                selectedRegionId,

                /** Show error alert using SweetAlert2 */
                showErrorAlert(message, timer = 3000) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message,
                            confirmButtonText: 'OK',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: timer
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

                    // Normalize initial values
                    // Handle selectedProvinceId array case like [null]
                    if (Array.isArray(this.selectedProvinceId)) {
                        if (this.selectedProvinceId.length === 0 || this.selectedProvinceId[0] === null || this
                            .selectedProvinceId[0] === '' || this.selectedProvinceId[0] === 'null' || this
                            .selectedProvinceId[0] === '-1') {
                            this.selectedProvinceId = null;
                        } else {
                            this.selectedProvinceId = this.selectedProvinceId[0];
                        }
                    } else if (this.selectedProvinceId === '' || this.selectedProvinceId === null || this
                        .selectedProvinceId === 'null' || this.selectedProvinceId === '-1') {
                        this.selectedProvinceId = null;
                    }

                    if (this.selectedRegionId === '' || this.selectedRegionId === null || this.selectedRegionId ===
                        'null' || this.selectedRegionId === '-1') {
                        this.selectedRegionId = null;
                    }

                    try {

                        this.setupMap();
                        this.map.options.fadeAnimation = false;
                        this.map.options.zoomAnimation = false;
                        this.addMarkers(this.provinceGeo, false);
                        this.addMarkers(this.temporaryGeo, true);

                        // Defer polygon and mask loading to improve initial loading performance
                        setTimeout(() => {
                            // Add appropriate boundaries based on user role
                            if (@js($userRole) == 1) {
                                // Role 1: Show region boundaries by default, province boundaries only when selected
                                if (!this.selectedProvinceId || this.selectedProvinceId === -1 || this
                                    .selectedProvinceId === '') {
                                    this.addRegionPolygons();
                                } else {
                                    this.addProvincePolygons();
                                }
                            } else if (@js($userRole) == 2) {
                                // Role 2: Always show their province boundaries
                                this.addProvincePolygons();
                            }

                            // Add province visibility mask for role 2 users or role 1 when specific province selected
                            if (@js($userRole) == 2 || (@js($userRole) == 1 &&
                                    this
                                    .selectedProvinceId && this.selectedProvinceId !== -1)) {
                                this.addProvinceVisibilityMask();
                            }

                            // Add region visibility mask for role 1 users when specific region selected and no province
                            if (@js($userRole) == 1 && this.selectedRegionId && this
                                .selectedRegionId !== -
                                1 &&
                                (!this.selectedProvinceId || this.selectedProvinceId === -1 || this
                                    .selectedProvinceId ===
                                    '')) {
                                this.addRegionVisibilityMask();
                            }
                        }, 100);

                        this.bindLivewireEvents();
                        this.map.on('click', this.handleMapClick.bind(this));
                        this.map.on('zoomstart', () => this.map.closePopup());

                        this.initTooltips();

                    } catch (error) {
                        console.error('Error initializing map:', error);
                        this.showErrorAlert('Failed to initialize map. Please refresh the page.');
                    } finally {
                        // Map rendering completed
                        this.$wire.set('isMapRendering', false);
                    }
                },
                setupMap() {
                    try {
                        let bounds = L.latLngBounds(
                            L.latLng(4.215806, 116.931885),
                            L.latLng(21.321780, 126.604385)
                        );
                        let initialZoom = 6;
                        let initialCenter = [this.lat, this.lon];

                        if (@js($userRole) == 2 && this.provinceBoundaries && this.provinceBoundaries
                            .length > 0) {
                            const province = this.provinceBoundaries[0];
                            if (province.boundary_geojson) {
                                try {
                                    const geometry = JSON.parse(province.boundary_geojson);

                                    if (geometry.type === 'Polygon') {
                                        const coords = geometry.coordinates[0]; // outer ring
                                        const [centLat, centLng] = getPolygonCentroid(coords);
                                        initialCenter = [centLat, centLng];
                                        initialZoom = 10;
                                    }

                                    if (geometry.type === 'MultiPolygon') {
                                        // pick the first polygon for centroid
                                        const coords = geometry.coordinates[0][0];
                                        const [centLat, centLng] = getPolygonCentroid(coords);
                                        initialCenter = [centLat, centLng];
                                        initialZoom = 10;
                                    }
                                } catch (error) {
                                    console.warn('Error setting province bounds:', error);
                                }
                            }
                        }

                        this.map = L.map('map', {
                            maxBounds: bounds,
                            maxBoundsViscosity: 1.0,
                            minZoom: @js($userRole) == 2 ? 8 : 5,
                            maxZoom: 18
                        }).setView(initialCenter, initialZoom);

                        // Create custom pane for mask layers above markers
                        this.map.createPane('maskPane');
                        this.map.getPane('maskPane').style.zIndex = '650';

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                        }).addTo(this.map);

                        // Map setup completed
                    } catch (error) {
                        console.error('Error setting up map:', error);
                        throw new Error('Failed to setup map. Please check your internet connection.');
                    }

                    // Helper inside setupMap
                    function getPolygonCentroid(coords) {
                        let area = 0,
                            x = 0,
                            y = 0;
                        for (let i = 0, j = coords.length - 1; i < coords.length; j = i++) {
                            const [x0, y0] = coords[j]; // lng, lat
                            const [x1, y1] = coords[i];
                            const f = (x0 * y1 - x1 * y0);
                            area += f;
                            x += (x0 + x1) * f;
                            y += (y0 + y1) * f;
                        }
                        area *= 0.5;
                        if (area === 0) {
                            const lats = coords.map(c => c[1]);
                            const lngs = coords.map(c => c[0]);
                            return [
                                (lats.reduce((a, b) => a + b, 0)) / lats.length,
                                (lngs.reduce((a, b) => a + b, 0)) / lngs.length
                            ];
                        }
                        return [y / (6 * area), x / (6 * area)]; // return [lat, lng]
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
                                    window.deleteTempCommodity(isTemporary ? entry.commodity.id :
                                        entry.id, isTemporary ? 1 :
                                        0);
                                };
                            }
                        });

                        markerList.push(marker);
                    });

                    // Ensure mask layers stay on top after adding markers
                    this.bringMasksToFront();
                },

                /** Add region polygons to the map */
                addRegionPolygons() {
                    // Check if map object exists
                    if (!this.map) {
                        return;
                    }

                    // Clear existing polygons
                    this.regionPolygons.forEach(polygon => {
                        if (this.map.hasLayer(polygon)) {
                            this.map.removeLayer(polygon);
                        }
                    });
                    this.regionPolygons.length = 0;

                    if (!this.regionBoundaries || this.regionBoundaries.length === 0) {
                        return;
                    }

                    let filteredBoundaries = this.regionBoundaries;
                    if (this.selectedRegionId && this.selectedRegionId !== -1 && this.selectedRegionId !== '') {
                        filteredBoundaries = this.regionBoundaries.filter(r => r.code == this.selectedRegionId);
                    }

                    filteredBoundaries.forEach((region, index) => {
                        if (!region.boundary_geojson) {
                            return;
                        }

                        try {
                            const geometry = JSON.parse(region.boundary_geojson);

                            if (geometry.type === 'Polygon') {
                                // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
                                const leafletCoords = geometry.coordinates[0].map(coord => [coord[1], coord[
                                    0]]);

                                const polygon = L.polygon(leafletCoords, {
                                    color: '#4A90E2', // Blue for regions
                                    weight: 2,
                                    opacity: 0.8,
                                    fillColor: '#4A90E2',
                                    fillOpacity: 0.1, // Light fill
                                    interactive: false // Don't interfere with map clicks
                                }).addTo(this.map);

                                this.regionPolygons.push(polygon);

                            } else if (geometry.type === 'MultiPolygon') {
                                geometry.coordinates.forEach((coords, partIndex) => {
                                    // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
                                    const leafletCoords = coords[0].map(coord => [coord[1], coord[0]]);

                                    const polygon = L.polygon(leafletCoords, {
                                        color: '#4A90E2', // Blue for regions
                                        weight: 2,
                                        opacity: 0.8,
                                        fillColor: '#4A90E2',
                                        fillOpacity: 0.1, // Light fill
                                        interactive: false // Don't interfere with map clicks
                                    }).addTo(this.map);

                                    this.regionPolygons.push(polygon);
                                });
                            }
                        } catch (error) {
                            console.error(`Error parsing region boundary for ${region.name}:`, error);
                        }
                    });
                },

                /** Add province polygons to the map */
                addProvincePolygons() {
                    // Clear existing polygons
                    this.provincePolygons.forEach(polygon => {
                        if (this.map.hasLayer(polygon)) {
                            this.map.removeLayer(polygon);
                        }
                    });
                    this.provincePolygons.length = 0;

                    if (!this.provinceBoundaries || this.provinceBoundaries.length === 0) {
                        return;
                    }

                    let filteredBoundaries = this.provinceBoundaries;
                    if (this.selectedProvinceId && this.selectedProvinceId !== -1 && this.selectedProvinceId !== '') {
                        filteredBoundaries = this.provinceBoundaries.filter(p => p.code == this.selectedProvinceId);
                    }

                    filteredBoundaries.forEach(province => {
                        if (!province.boundary_geojson) {
                            return;
                        }

                        try {
                            const geometry = JSON.parse(province.boundary_geojson);

                            if (geometry.type === 'Polygon') {
                                // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
                                const leafletCoords = geometry.coordinates[0].map(coord => [coord[1], coord[
                                    0]]);

                                // Highlight user's province for role 2
                                const isUserProvince = @js($userRole) == 2 && this
                                    .provinceBoundaries.length === 1;

                                const polygon = L.polygon(leafletCoords, {
                                    color: isUserProvince ? '#dc2626' :
                                    '#496B4A', // Red for user's province, green for others
                                    weight: isUserProvince ? 3 :
                                    2, // Thicker border for user's province
                                    opacity: 0.9,
                                    fill: false, // No fill, just borders
                                    interactive: false // Don't interfere with map clicks
                                }).addTo(this.map);

                                this.provincePolygons.push(polygon);

                            } else if (geometry.type === 'MultiPolygon') {
                                // Highlight user's province for role 2
                                const isUserProvince = @js($userRole) == 2 && this
                                    .provinceBoundaries.length === 1;

                                geometry.coordinates.forEach(coords => {
                                    // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
                                    const leafletCoords = coords[0].map(coord => [coord[1], coord[0]]);

                                    const polygon = L.polygon(leafletCoords, {
                                        color: isUserProvince ? '#dc2626' :
                                        '#496B4A', // Red for user's province, green for others
                                        weight: isUserProvince ? 3 :
                                        2, // Thicker border for user's province
                                        opacity: 0.9,
                                        fill: false, // No fill, just borders
                                        interactive: false // Don't interfere with map clicks
                                    }).addTo(this.map);

                                    this.provincePolygons.push(polygon);
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

                    // Determine which provinces to check based on selection
                    let provincesToCheck = this.provinceBoundaries;

                    // For role 1 users with specific province selected, only check that province
                    if (@js($userRole) == 1 && this.selectedProvinceId && this.selectedProvinceId !== -
                        1) {
                        provincesToCheck = this.provinceBoundaries.filter(p => p.code == this.selectedProvinceId);
                    }

                    // Check each relevant province boundary
                    for (const province of provincesToCheck) {
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

                /** Check if a point is within the selected region boundaries */
                isPointInRegion(lat, lng) {
                    if (!this.regionBoundaries || this.regionBoundaries.length === 0) {
                        return false;
                    }

                    // Filter to selected region if one is selected
                    let regionsToCheck = this.regionBoundaries;
                    if (this.selectedRegionId && this.selectedRegionId !== -1) {
                        regionsToCheck = this.regionBoundaries.filter(r => r.code == this.selectedRegionId);
                    }

                    // Check each region boundary
                    for (const region of regionsToCheck) {
                        if (!region.boundary_geojson) {
                            continue;
                        }

                        try {
                            const geometry = JSON.parse(region.boundary_geojson);

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
                            console.error('Error checking point in region for', region.name, ':', error);
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

                /** Bring all mask layers to the front to ensure they cover markers */
                bringMasksToFront() {
                    // Bring province mask layers to front
                    this.provinceMaskLayers.forEach(layer => {
                        if (this.map.hasLayer(layer)) {
                            layer.bringToFront();
                        }
                    });

                    // Bring region mask layers to front
                    this.regionMaskLayers.forEach(layer => {
                        if (this.map.hasLayer(layer)) {
                            layer.bringToFront();
                        }
                    });
                },

                /** Update province visibility mask based on current selection */
                updateProvinceVisibilityMask() {
                    // Remove existing mask first
                    this.removeProvinceVisibilityMask();

                    // Apply mask if conditions are met
                    const shouldApplyMask = @js($userRole) == 2 ||
                        (@js($userRole) == 1 && this.selectedProvinceId && this.selectedProvinceId !== -
                            1);

                    if (shouldApplyMask) {
                        this.addProvinceVisibilityMask();
                    }
                },

                /** Remove existing province visibility mask */
                removeProvinceVisibilityMask() {
                    // Remove all stored province mask layers
                    this.provinceMaskLayers.forEach((layer) => {
                        if (this.map.hasLayer(layer)) {
                            this.map.removeLayer(layer);
                        }
                    });
                    this.provinceMaskLayers.length = 0; // Clear the array
                },

                /** Add province visibility mask for role 2 users or role 1 when specific province selected */
                addProvinceVisibilityMask() {
                    if (!this.provinceBoundaries || this.provinceBoundaries.length === 0) {
                        return;
                    }

                    // Determine which provinces to show as cutouts
                    let provincesToShow = this.provinceBoundaries;

                    // For role 1 users with specific province selected, only show that province
                    if (@js($userRole) == 1 && this.selectedProvinceId && this.selectedProvinceId !== -
                        1) {
                        provincesToShow = this.provinceBoundaries.filter(p => p.code == this.selectedProvinceId);
                    }

                    if (provincesToShow.length === 0) {
                        return; // No provinces to show, don't apply mask
                    }

                    // Create a grey/white semi-transparent mask that covers the entire world
                    const worldBounds = [
                        [-90, -180], // Southwest
                        [90, -180], // Northwest
                        [90, 180], // Northeast
                        [-90, 180], // Southeast
                        [-90, -180] // Southwest (close the polygon)
                    ];

                    // Create the mask polygon (world coverage) - darker grey semi-transparent overlay
                    const maskPolygon = L.polygon(worldBounds, {
                        color: '#666666',
                        weight: 1,
                        fillColor: '#666666',
                        fillOpacity: 0.7, // More visible semi-transparent overlay
                        interactive: true // Allow clicks to be captured
                    }).addTo(this.map);

                    // Store mask layer for cleanup
                    this.provinceMaskLayers.push(maskPolygon);

                    // Create a single polygon with holes for all provinces to show
                    // Start with world bounds as outer ring
                    const polygonCoords = [worldBounds];

                    // Add each province as an inner ring (hole)
                    provincesToShow.forEach(province => {
                        if (!province.boundary_geojson) {
                            return;
                        }

                        try {
                            const geometry = JSON.parse(province.boundary_geojson);

                            if (geometry.type === 'Polygon') {
                                // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
                                const leafletCoords = geometry.coordinates[0].map(coord => [coord[1], coord[
                                    0]]);
                                polygonCoords.push(leafletCoords);

                            } else if (geometry.type === 'MultiPolygon') {
                                // For MultiPolygon, add each polygon part as a separate hole
                                geometry.coordinates.forEach((coords, index) => {
                                    const leafletCoords = coords[0].map(coord => [coord[1], coord[0]]);
                                    polygonCoords.push(leafletCoords);
                                });
                            }
                        } catch (error) {
                            console.error('Error processing province boundary for', province.name, ':', error);
                        }
                    });

                    // Remove the simple world mask and create the polygon with holes
                    this.map.removeLayer(maskPolygon);
                    this.maskLayers = this.maskLayers.filter(layer => layer !== maskPolygon);

                    // Create the mask polygon with holes
                    const maskWithHoles = L.polygon(polygonCoords, {
                        color: '#666666',
                        weight: 1,
                        fillColor: '#666666',
                        fillOpacity: 0.7, // More visible semi-transparent overlay with holes
                        interactive: true, // Allow clicks to be captured
                        pane: 'maskPane' // Use custom pane above markers
                    });

                    maskWithHoles.addTo(this.map);

                    // Store the new mask layer
                    this.provinceMaskLayers.push(maskWithHoles);

                    // Add click handler to mask to prevent interaction outside province (for role 2 or when specific province selected)
                    maskWithHoles.on('click', (e) => {
                        // Prevent the click from propagating to map
                        L.DomEvent.stopPropagation(e);
                        // Prevent default behavior
                        L.DomEvent.preventDefault(e);
                        // Show error message to user
                        this.showErrorAlert(
                            'You can only interact within the selected province boundaries. Please click inside the province to add markers.'
                        );
                        return false;
                    });


                    // Also prevent other interactions like double-click, context menu, etc.
                    maskWithHoles.on('dblclick contextmenu', (e) => {
                        L.DomEvent.stopPropagation(e);
                        L.DomEvent.preventDefault(e);
                        return false;
                    });

                    // Ensure mask stays on top of all layers including markers
                    this.map.on('layeradd', () => {
                        if (this.map.hasLayer(maskWithHoles)) {
                            maskWithHoles.bringToFront();
                        }
                    });
                },

                /** Update region visibility mask based on current selection */
                updateRegionVisibilityMask() {
                    // Remove existing mask first
                    this.removeRegionVisibilityMask();

                    // Apply mask if conditions are met
                    const shouldApplyMask = @js($userRole) == 1 && this.selectedRegionId && this
                        .selectedRegionId !== -1 &&
                        (!this.selectedProvinceId || this.selectedProvinceId === -1 || this.selectedProvinceId === '');

                    if (shouldApplyMask) {
                        this.addRegionVisibilityMask();
                    }
                },

                /** Remove existing region visibility mask */
                removeRegionVisibilityMask() {
                    // Remove all stored region mask layers
                    this.regionMaskLayers.forEach((layer) => {
                        if (this.map.hasLayer(layer)) {
                            this.map.removeLayer(layer);
                        }
                    });
                    this.regionMaskLayers.length = 0; // Clear the array
                },

                /** Add region visibility mask for role 1 users when specific region selected and no province */
                addRegionVisibilityMask() {
                    if (!this.regionBoundaries || this.regionBoundaries.length === 0) {
                        return;
                    }

                    // Determine which regions to show as cutouts
                    let regionsToShow = this.regionBoundaries;

                    // For role 1 users with specific region selected, only show that region
                    if (@js($userRole) == 1 && this.selectedRegionId && this.selectedRegionId !== -1) {
                        regionsToShow = this.regionBoundaries.filter(r => r.code == this.selectedRegionId);
                    }

                    if (regionsToShow.length === 0) {
                        return; // No regions to show, don't apply mask
                    }

                    // Create a grey/white semi-transparent mask that covers the entire world
                    const worldBounds = [
                        [-90, -180], // Southwest
                        [90, -180], // Northwest
                        [90, 180], // Northeast
                        [-90, 180], // Southeast
                        [-90, -180] // Southwest (close the polygon)
                    ];


                    // Create the mask polygon (world coverage) - darker grey semi-transparent overlay
                    const maskPolygon = L.polygon(worldBounds, {
                        color: '#666666',
                        weight: 1,
                        fillColor: '#666666',
                        fillOpacity: 0.7, // More visible semi-transparent overlay
                        interactive: true // Allow clicks to be captured
                    }).addTo(this.map);


                    // Store mask layer for cleanup
                    this.regionMaskLayers.push(maskPolygon);
                    // Start with world bounds as outer ring
                    const polygonCoords = [worldBounds];

                    // Add each region as an inner ring (hole)
                    regionsToShow.forEach(region => {
                        if (!region.boundary_geojson) {
                            return;
                        }

                        try {
                            const geometry = JSON.parse(region.boundary_geojson);

                            if (geometry.type === 'Polygon') {
                                // Convert coordinates from [lng, lat] to [lat, lng] for Leaflet
                                const leafletCoords = geometry.coordinates[0].map(coord => [coord[1], coord[
                                    0]]);
                                polygonCoords.push(leafletCoords);

                            } else if (geometry.type === 'MultiPolygon') {
                                // For MultiPolygon, add each polygon part as a separate hole
                                geometry.coordinates.forEach((coords, index) => {
                                    const leafletCoords = coords[0].map(coord => [coord[1], coord[0]]);
                                    polygonCoords.push(leafletCoords);

                                });
                            }
                        } catch (error) {
                            console.error('Error processing region boundary for', region.name, ':', error);
                        }
                    });


                    // Remove the simple world mask and create the polygon with holes
                    this.map.removeLayer(maskPolygon);
                    this.maskLayers = this.maskLayers.filter(layer => layer !== maskPolygon);

                    // Create the mask polygon with holes
                    const maskWithHoles = L.polygon(polygonCoords, {
                        color: '#666666',
                        weight: 1,
                        fillColor: '#666666',
                        fillOpacity: 0.7, // More visible semi-transparent overlay with holes
                        interactive: true, // Allow clicks to be captured
                        pane: 'maskPane' // Use custom pane above markers
                    }).addTo(this.map);


                    // Store the new mask layer
                    this.regionMaskLayers.push(maskWithHoles);

                    // Add click handler to mask to prevent interaction outside region
                    maskWithHoles.on('click', (e) => {
                        // Prevent the click from propagating to map
                        L.DomEvent.stopPropagation(e);
                        // Prevent default behavior
                        L.DomEvent.preventDefault(e);
                        // Show error message to user
                        this.showErrorAlert(
                            'You can only interact within the selected region boundaries. Please click inside the region to add markers.'
                        );
                        return false;
                    });

                    // Also prevent other interactions like double-click, context menu, etc.
                    maskWithHoles.on('dblclick contextmenu', (e) => {
                        L.DomEvent.stopPropagation(e);
                        L.DomEvent.preventDefault(e);
                        return false;
                    });

                    // Ensure mask stays on top of all layers including markers
                    this.map.on('layeradd', () => {
                        if (this.map.hasLayer(maskWithHoles)) {
                            maskWithHoles.bringToFront();
                        }
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
                        this.provinceGeo = newGeo.flat ? newGeo.flat() : newGeo;
                        this.addMarkers(this.provinceGeo, false);
                    });

                    Livewire.on('removeMarkers', () => {
                        this.placeMarker(0, 0);
                    });

                    Livewire.on('zoomToProvince', (data) => {
                        // If data comes as an array, pick the first item
                        const province = Array.isArray(data) ? data[0] : data;

                        if (this.map && province.lat && province.lng && this.isValidCoordinates(province.lat,
                                province.lng)) {
                            // Zoom smoothly to province center
                            this.map.setView([province.lat, province.lng], 9, {
                                animate: true,
                                duration: 1.5
                            });
                        } else {
                            this.showErrorAlert('Unable to zoom to province: Invalid location data');
                        }
                    });

                    Livewire.on('zoomToRegion', (data) => {
                        // If data comes as an array, pick the first item
                        const region = Array.isArray(data) ? data[0] : data;

                        if (this.map && region.lat && region.lng && this.isValidCoordinates(region.lat,
                                region.lng)) {
                            // Set the selected region ID to ensure boundaries are rendered
                            this.selectedRegionId = region.code;

                            // Zoom smoothly to region center
                            this.map.setView([region.lat, region.lng], 7, {
                                animate: true,
                                duration: 1.5
                            });

                            // Ensure region's boundaries are rendered and mask is applied
                            if (@js($userRole) == 1 && (!this.selectedProvinceId || this
                                    .selectedProvinceId === -1)) {
                                // Clear existing region polygons and re-add them to show the zoomed region
                                this.regionPolygons.forEach(polygon => {
                                    if (this.map.hasLayer(polygon)) {
                                        this.map.removeLayer(polygon);
                                    }
                                });
                                this.regionPolygons.length = 0;
                                this.addRegionPolygons();

                                // Apply region mask if region is selected
                                if (this.selectedRegionId && this.selectedRegionId !== -1) {
                                    this.updateRegionVisibilityMask();
                                }
                            }
                        } else {
                            this.showErrorAlert('Unable to zoom to region: Invalid location data');
                        }
                    });

                    Livewire.on('selectedProvinceChanged', newSelectedProvinceId => {
                        this.selectedProvinceId = newSelectedProvinceId;

                        // Normalize null/empty values to null for consistent handling
                        // Handle array case like [null]
                        if (Array.isArray(this.selectedProvinceId)) {
                            if (this.selectedProvinceId.length === 0 || this.selectedProvinceId[0] === null ||
                                this.selectedProvinceId[0] === '' || this.selectedProvinceId[0] === 'null' ||
                                this.selectedProvinceId[0] === '-1') {
                                this.selectedProvinceId = null;
                            } else {
                                this.selectedProvinceId = this.selectedProvinceId[0];
                            }
                        } else if (this.selectedProvinceId === '' || this.selectedProvinceId === null || this
                            .selectedProvinceId === 'null' || this.selectedProvinceId === '-1') {
                            this.selectedProvinceId = null;
                        }

                        // Clear existing polygons first
                        this.provincePolygons.forEach(polygon => {
                            if (this.map.hasLayer(polygon)) {
                                this.map.removeLayer(polygon);
                            }
                        });
                        this.provincePolygons.length = 0;

                        this.regionPolygons.forEach(polygon => {
                            if (this.map.hasLayer(polygon)) {
                                this.map.removeLayer(polygon);
                            }
                        });
                        this.regionPolygons.length = 0;

                        if (@js($userRole) == 1) {
                            // Role 1: Show province boundaries when selected, region boundaries when none selected
                            if (this.selectedProvinceId && this.selectedProvinceId !== -1) {
                                this.addProvincePolygons();
                            } else {
                                this.addRegionPolygons();
                            }
                        } else if (@js($userRole) == 2) {
                            // Role 2: Always show province boundaries
                            this.addProvincePolygons();
                        }

                        // Update province visibility mask based on selection
                        this.updateProvinceVisibilityMask();

                        // Handle region visibility mask - remove if province selected, apply if region selected and no province
                        if (this.selectedProvinceId && this.selectedProvinceId !== -1) {
                            // Province selected, remove region mask
                            this.removeRegionVisibilityMask();
                        } else if (@js($userRole) == 1 && this.selectedRegionId && this
                            .selectedRegionId !== -1) {
                            // No province selected but region is selected, apply region mask
                            this.updateRegionVisibilityMask();
                        } else {
                            // Neither province nor region selected, remove region mask
                            this.removeRegionVisibilityMask();
                        }
                    });

                    Livewire.on('selectedRegionChanged', newSelectedRegionId => {
                        this.selectedRegionId = newSelectedRegionId;

                        // Normalize null/empty values to null for consistent handling
                        if (this.selectedRegionId === '' || this.selectedRegionId === null || this
                            .selectedRegionId === 'null' || this.selectedRegionId === '-1') {
                            this.selectedRegionId = null;
                        }

                        // Normalize selectedProvinceId to handle array cases like [null]
                        if (Array.isArray(this.selectedProvinceId)) {
                            if (this.selectedProvinceId.length === 0 || this.selectedProvinceId[0] === null ||
                                this.selectedProvinceId[0] === '' || this.selectedProvinceId[0] === 'null' ||
                                this.selectedProvinceId[0] === '-1') {
                                this.selectedProvinceId = null;
                            } else {
                                this.selectedProvinceId = this.selectedProvinceId[0];
                            }
                        } else if (this.selectedProvinceId === '' || this.selectedProvinceId === null || this
                            .selectedProvinceId === 'null' || this.selectedProvinceId === '-1') {
                            this.selectedProvinceId = null;
                        }

                        // Clear existing region polygons first
                        this.regionPolygons.forEach(polygon => {
                            if (this.map.hasLayer(polygon)) {
                                this.map.removeLayer(polygon);
                            }
                        });
                        this.regionPolygons.length = 0;

                        if (@js($userRole) == 1) {
                            // Role 1: Handle region selection
                            if (this.selectedRegionId && this.selectedRegionId !== -1) {
                                // Region selected
                                if (!this.selectedProvinceId || this.selectedProvinceId === -1) {
                                    this.addRegionPolygons(); // Show selected region boundaries
                                } else {
                                    // Province selected, don't show region polygons
                                }
                            } else {
                                // Region deselected
                                if (!this.selectedProvinceId || this.selectedProvinceId === -1) {
                                    this.addRegionPolygons(); // Show all region boundaries
                                } else {
                                    // Province is selected, not calling addRegionPolygons
                                }
                            }
                        }
                        // Role 2 doesn't use region selection, so no changes needed

                        // Update region visibility mask based on selection
                        this.updateRegionVisibilityMask();
                    });

                    // Note: provinceBoundariesLoaded event is no longer used since we load data directly in mount
                },

                handleMapClick(e) {
                    const {
                        lat,
                        lng
                    } = e.latlng;

                    // Enhanced validation: Check if click is within allowed boundaries
                    let isValidLocation = true;
                    let errorMessage = '';

                    // For role 2 users, always check if click is within province boundaries
                    if (@js($userRole) == 2) {
                        if (!this.isPointInProvince(lat, lng)) {
                            isValidLocation = false;
                            errorMessage =
                                'You can only place markers within your assigned province boundaries. Please click on a location inside your province to add a commodity marker.';
                        }
                    }

                    // For role 1 users with region selected but no province, check if click is within region boundaries
                    if (@js($userRole) == 1 && this.selectedRegionId && this.selectedRegionId !== -1 &&
                        (!this.selectedProvinceId || this.selectedProvinceId === -1 || this.selectedProvinceId === '')
                    ) {
                        if (!this.isPointInRegion(lat, lng)) {
                            isValidLocation = false;
                            errorMessage =
                                'You can only place markers within the selected region boundaries. Please click on a location inside the selected region to add a commodity marker.';
                        }
                    }

                    // For role 1 users with specific province selected, check if click is within that province
                    if (@js($userRole) == 1 && this.selectedProvinceId && this.selectedProvinceId !== -
                        1) {
                        if (!this.isPointInProvince(lat, lng)) {
                            isValidLocation = false;
                            errorMessage =
                                'You can only place markers within the selected province boundaries. Please click on a location inside the selected province to add a commodity marker.';
                        }
                    }

                    // If location is not valid, show error and prevent any further processing
                    if (!isValidLocation) {
                        this.showErrorAlert(errorMessage);
                        // Prevent the click from doing anything else
                        L.DomEvent.stopPropagation(e);
                        L.DomEvent.preventDefault(e);
                        return false;
                    }

                    // Only proceed if location is valid
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

                    // Enhanced validation for search results
                    let isValidLocation = true;
                    let errorMessage = '';

                    // For role 2 users, check if search result is within province boundaries
                    if (@js($userRole) == 2) {
                        if (!this.isPointInProvince(lat, lon)) {
                            isValidLocation = false;
                            errorMessage = 'The selected location "' + res.display_name +
                                '" is outside your province boundaries. Please search for a location within your assigned province.';
                        }
                    }

                    // For role 1 users with region selected but no province, check if search result is within region boundaries
                    if (@js($userRole) == 1 && this.selectedRegionId && this.selectedRegionId !== -1 &&
                        (!this.selectedProvinceId || this.selectedProvinceId === -1 || this.selectedProvinceId === '')
                    ) {
                        if (!this.isPointInRegion(lat, lon)) {
                            isValidLocation = false;
                            errorMessage = 'The selected location "' + res.display_name +
                                '" is outside the selected region boundaries. Please search for a location within the selected region.';
                        }
                    }

                    // For role 1 users with specific province selected, check if search result is within that province
                    if (@js($userRole) == 1 && this.selectedProvinceId && this.selectedProvinceId !== -
                        1) {
                        if (!this.isPointInProvince(lat, lon)) {
                            isValidLocation = false;
                            errorMessage = 'The selected location "' + res.display_name +
                                '" is outside the selected province boundaries. Please search for a location within the selected province.';
                        }
                    }

                    // If location is not valid, show error and prevent selection
                    if (!isValidLocation) {
                        this.showErrorAlert(errorMessage, 5000);
                        return;
                    }

                    // Only proceed if location is valid
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
                        // Enhanced validation for reverse geocoding
                        let isValidLocation = true;
                        let errorMessage = '';

                        // For role 2 users, validate location before reverse geocoding
                        if (@js($userRole) == 2 && updateMap) {
                            if (!this.isPointInProvince(lat, lon)) {
                                isValidLocation = false;
                                errorMessage = 'Location is outside your province boundaries';
                            }
                        }

                        // For role 1 users with region selected but no province, validate location before reverse geocoding
                        if (@js($userRole) == 1 && this.selectedRegionId && this.selectedRegionId !== -
                            1 &&
                            (!this.selectedProvinceId || this.selectedProvinceId === -1 || this.selectedProvinceId ===
                                '') && updateMap) {
                            if (!this.isPointInRegion(lat, lon)) {
                                isValidLocation = false;
                                errorMessage = 'Location is outside the selected region boundaries';
                            }
                        }

                        // For role 1 users with specific province selected, validate location before reverse geocoding
                        if (@js($userRole) == 1 && this.selectedProvinceId && this.selectedProvinceId !==
                            -1 && updateMap) {
                            if (!this.isPointInProvince(lat, lon)) {
                                isValidLocation = false;
                                errorMessage = 'Location is outside the selected province boundaries';
                            }
                        }

                        // If location is not valid, show error and prevent reverse geocoding
                        if (!isValidLocation) {
                            this.showErrorAlert(errorMessage);
                            return;
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
