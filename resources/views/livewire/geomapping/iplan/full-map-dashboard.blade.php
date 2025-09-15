<div class="container-fluid p-0" x-data="window.fullMapView(@js($provinceGeo))" x-init="initMap()">

    <button type="button" wire:click='buttonTest()' class="btn btn-primary" hidden>Test</button>
    <!-- Full screen map container -->
    <div class="position-relative" style="height: 100vh;">
        <!-- Full screen button -->
        <div class="position-absolute top-0 end-0 p-3" style="z-index: 1001;">
            <button @click="toggleFullscreen()" class="btn btn-light shadow-sm" title="Toggle Fullscreen">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>
        </div>

        <!-- Loading indicator -->
        @if ($isLoadingMap)
            <div class="d-flex justify-content-center align-items-center position-absolute top-0 start-0 w-100 h-100 bg-light bg-opacity-75"
                style="z-index: 1000;">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading map...</span>
                    </div>
                    <div class="text-muted">Initializing map...</div>
                </div>
            </div>
        @elseif ($isMapRendering)
            <div class="d-flex justify-content-center align-items-center position-absolute top-0 start-0 w-100 h-100 bg-light bg-opacity-75"
                style="z-index: 1000;">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Rendering map...</span>
                    </div>
                    <div class="text-muted">Rendering map...</div>
                </div>
            </div>
        @endif

        <!-- Map container -->
        <div wire:ignore id="map" style="height: 100vh; width: 100%;"></div>
    </div>
</div>

@script
<script>
    window.fullMapView = function(provinceGeo) {
        return {
            // Map instance
            map: null,

            // Marker layer group
            markerLayer: null,

            // Markers tracking map
            markersMap: new Map(),

            // Fullscreen state
            isFullscreen: false,

            /** Initialize map and display markers */
            initMap() {
                try {
                    // Setup map
                    this.setupMap();

                    // Add clickable markers for commodities
                    this.updateMarkers(provinceGeo);

                    // Setup Livewire listeners
                    this.setupLivewireListeners();

                    // Map rendering completed
                    this.$wire.set('isMapRendering', false);

                } catch (error) {
                    console.error('Error initializing map:', error);
                }
            },

            /** Setup Livewire event listeners */
            setupLivewireListeners() {
                Livewire.on('provinceGeoUpdated', newGeo => {
                    this.provinceGeo = newGeo.flat ? newGeo.flat() : newGeo;
                    this.updateMarkers(this.provinceGeo);
                });
            },

            /** Setup the map instance */
            setupMap() {
                const bounds = L.latLngBounds(
                    L.latLng(4.215806, 116.931885),
                    L.latLng(21.321780, 126.604385)
                );

                this.map = L.map('map', {
                    maxBounds: bounds,
                    maxBoundsViscosity: 1.0,
                    minZoom: 5,
                    maxZoom: 18,
                    zoomControl: true,
                    scrollWheelZoom: true,
                    doubleClickZoom: true,
                    boxZoom: true,
                    keyboard: true,
                    dragging: true,
                    touchZoom: true
                }).setView([12.8797, 121.774], 6);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(this.map);

                // Create marker layer group
                this.markerLayer = L.layerGroup().addTo(this.map);
            },

            /** Clear all markers */
            clearMarkers() {
                if (this.markerLayer) {
                    this.markerLayer.clearLayers();
                }
            },

            /** Add clickable markers for commodities with intervention popups */
            addMarkers(data) {
                if (!data || !Array.isArray(data)) {
                    console.log('No data or data is not an array:', data);
                    return;
                }

                console.log('Adding markers for data:', data);

                data.forEach(entry => {
                    if (!entry.latitude || !entry.longitude || !entry.commodity) {
                        console.log('Skipping entry due to missing data:', entry);
                        return;
                    }

                    console.log('Adding marker for:', entry.commodity.name, 'at', entry.latitude, entry.longitude);

                    const iconUrl = entry.commodity.icon.startsWith('http')
                        ? entry.commodity.icon
                        : `/icons/${entry.commodity.icon}`;

                    const customIcon = L.divIcon({
                        className: 'custom-marker-icon position-relative',
                        html: `<div class="marker-circle" style="opacity: 0;">
                            <img src="${iconUrl}" alt="${entry.commodity.name}"
                                onerror="this.onerror=null;this.src='/icons/commodities/default.png';"
                                style="width:32px; height:32px; border-radius:50%;" />
                        </div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    });

                    const marker = L.marker([entry.latitude, entry.longitude], { icon: customIcon }).addTo(this.markerLayer);

                    // Make marker visible after adding to DOM
                    setTimeout(() => {
                        const element = marker.getElement();
                        if (element) {
                            const circle = element.querySelector('.marker-circle');
                            if (circle) circle.style.opacity = '1';
                        }
                    }, 10);

                    // Create popup content with commodity name and interventions
                    const interventions = (entry.geo_interventions && entry.geo_interventions.length > 0) ?
                        entry.geo_interventions.map(i => `<li>${i.intervention?.name || ''}</li>`).join('') :
                        '<li>No interventions</li>';

                    const popupContent = `
                        <div class="text-center" style="min-width: 200px;">
                            <div class="fw-bold mb-2 text-primary">${entry.commodity.name}</div>
                            <div class="small text-muted my-2 text-start">
                                <strong>Interventions:</strong>
                                <ul class="mb-0 ps-3 mt-1">${interventions}</ul>
                            </div>
                        </div>`;

                    marker.bindPopup(popupContent);
                });

                console.log('Finished adding markers');
            },

            /** Toggle fullscreen mode */
            toggleFullscreen() {
                const container = document.querySelector('.container-fluid');
                const btn = document.querySelector('.btn[title="Toggle Fullscreen"] i');

                if (!this.isFullscreen) {
                    if (container.requestFullscreen) {
                        container.requestFullscreen();
                    } else if (container.webkitRequestFullscreen) {
                        container.webkitRequestFullscreen();
                    } else if (container.msRequestFullscreen) {
                        container.msRequestFullscreen();
                    }
                    if (btn) btn.className = 'bi bi-fullscreen-exit';
                    this.isFullscreen = true;
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    }
                    if (btn) btn.className = 'bi bi-arrows-fullscreen';
                    this.isFullscreen = false;
                }
            },

            /** Create unique key for marker */
            createMarkerKey(entry) {
                return `${entry.latitude}_${entry.longitude}_${entry.commodity.name}`;
            },

            /** Add a single marker with animation and sound */
            addMarker(entry) {
                const key = this.createMarkerKey(entry);
                if (this.markersMap.has(key)) return; // Already exists

                const iconUrl = entry.commodity.icon.startsWith('http')
                    ? entry.commodity.icon
                    : `/icons/${entry.commodity.icon}`;

                const customIcon = L.divIcon({
                    className: 'custom-marker-icon position-relative',
                    html: `<div class="marker-circle" style="opacity: 0;">
                        <img src="${iconUrl}" alt="${entry.commodity.name}"
                            onerror="this.onerror=null;this.src='/icons/commodities/default.png';"
                            style="width:32px; height:32px; border-radius:50%;" />
                    </div>`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 32]
                });

                const marker = L.marker([entry.latitude, entry.longitude], { icon: customIcon }).addTo(this.markerLayer);

                // Create popup content
                const interventions = (entry.geo_interventions && entry.geo_interventions.length > 0) ?
                    entry.geo_interventions.map(i => `<li>${i.intervention?.name || ''}</li>`).join('') :
                    '<li>No interventions</li>';

                const popupContent = `
                    <div class="text-center" style="min-width: 200px;">
                        <div class="fw-bold mb-2 text-primary">${entry.commodity.name}</div>
                        <div class="small text-muted my-2 text-start">
                            <strong>Interventions:</strong>
                            <ul class="mb-0 ps-3 mt-1">${interventions}</ul>
                        </div>
                    </div>`;

                marker.bindPopup(popupContent);

                // Store marker
                this.markersMap.set(key, { marker, popupContent, entry });

                // Start animation after marker is added to DOM
                setTimeout(() => {
                    const element = marker.getElement();
                    if (element) {
                        const circle = element.querySelector('.marker-circle');
                        if (circle) {
                            circle.style.opacity = '1';
                            element.classList.add('marker-bounce');
                        }
                    }
                }, 10);

                // Remove bounce class after animation
                setTimeout(() => {
                    const element = marker.getElement();
                    if (element) element.classList.remove('marker-bounce');
                }, 1010);
            },

            /** Remove a marker with fade-out animation */
            removeMarker(key) {
                const markerData = this.markersMap.get(key);
                if (!markerData) return;

                const element = markerData.marker.getElement();
                if (element) {
                    element.classList.add('marker-fade-out');
                    setTimeout(() => {
                        this.markerLayer.removeLayer(markerData.marker);
                        this.markersMap.delete(key);
                    }, 500); // Match CSS transition duration
                } else {
                    this.markerLayer.removeLayer(markerData.marker);
                    this.markersMap.delete(key);
                }
            },

            /** Update marker popup if content changed */
            updateMarker(key, newEntry) {
                const markerData = this.markersMap.get(key);
                if (!markerData) return;

                const interventions = (newEntry.geo_interventions && newEntry.geo_interventions.length > 0) ?
                    newEntry.geo_interventions.map(i => `<li>${i.intervention?.name || ''}</li>`).join('') :
                    '<li>No interventions</li>';

                const newPopupContent = `
                    <div class="text-center" style="min-width: 200px;">
                        <div class="fw-bold mb-2 text-primary">${newEntry.commodity.name}</div>
                        <div class="small text-muted my-2 text-start">
                            <strong>Interventions:</strong>
                            <ul class="mb-0 ps-3 mt-1">${interventions}</ul>
                        </div>
                    </div>`;

                if (markerData.popupContent !== newPopupContent) {
                    markerData.marker.setPopupContent(newPopupContent);
                    markerData.popupContent = newPopupContent;
                    markerData.entry = newEntry;
                }
            },

            /** Update markers based on new data */
            updateMarkers(newData) {
                if (!newData || !Array.isArray(newData)) return;

                const newKeys = new Set();
                const newMarkersMap = new Map();
                const newEntries = [];

                // Process additions and updates
                newData.forEach(entry => {
                    if (!entry.latitude || !entry.longitude || !entry.commodity) return;

                    const key = this.createMarkerKey(entry);
                    newKeys.add(key);

                    if (this.markersMap.has(key)) {
                        // Update if needed
                        this.updateMarker(key, entry);
                        newMarkersMap.set(key, this.markersMap.get(key));
                    } else {
                        // Collect new entries for staggered addition
                        newEntries.push({ entry, key });
                    }
                });

                // Stagger new marker additions with sound
                newEntries.forEach((obj, index) => {
                    setTimeout(() => {
                        this.addMarker(obj.entry);
                        newMarkersMap.set(obj.key, this.markersMap.get(obj.key));

                        // Play sound for new marker
                        const audio = new Audio('{{ asset('audio/popup.mp3') }}');
                        audio.play().catch((error) => {
                            console.error('Error playing sound:', error);
                        });
                    }, index * 200); // 200ms delay between each marker
                });

                // Process deletions
                for (const [key] of this.markersMap) {
                    if (!newKeys.has(key)) {
                        this.removeMarker(key);
                    }
                }

                // Update markersMap
                this.markersMap = newMarkersMap;
            }
        };
    };

    // Add CSS styles for animations
    const style = document.createElement('style');
    style.textContent = `
        .marker-bounce {
            animation: markerBounce 1s ease-out;
        }

        @keyframes markerBounce {
            0% { transform: scale(0); opacity: 1; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }

        .marker-fade-out {
            opacity: 0;
            transition: opacity 0.5s ease-out;
        }
    `;
    document.head.appendChild(style);
</script>
@endscript
