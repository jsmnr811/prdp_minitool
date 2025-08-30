<div class="row g-4" wire:ignore.self x-data="window.mapSearch(@js($provinceGeo), @js($temporaryGeo))" x-init="initMap()">
    <div class="col-lg-9">
        <div class="card shadow-sm p-4 h-100">
            <!-- Search Box -->
            @include('geomapping.iplan.mini-comp.search-box')
            <div wire:ignore id="map" class="rounded shadow-sm" style="height: 600px;"></div>
            <!-- Map Helper -->
            @include('geomapping.iplan.mini-comp.map-helper')
        </div>
    </div>
    <div class="col-lg-3 ">
        <!-- Commodity Toggles -->
        @include('geomapping.iplan.mini-comp.commodity-toggles')
    </div>
</div>
<!-- Scripts -->
@script
<script>
    window.mapSearch = function(provinceGeo, temporaryGeo) {
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

            // Data
            provinceGeo,
            temporaryGeo,

            /** Initialize map and related features */
            initMap() {
                this.setupMap();
                this.map.options.fadeAnimation = false;
                this.map.options.zoomAnimation = false;

                this.addMarkers(this.provinceGeo, false);
                this.addMarkers(this.temporaryGeo, true);

                this.bindLivewireEvents();
                this.map.on('click', this.handleMapClick.bind(this));
                this.map.on('zoomstart', () => this.map.closePopup());
                this.initTooltips();

            },

            setupMap() {
                const bounds = L.latLngBounds(
                    L.latLng(4.215806, 116.931885),
                    L.latLng(21.321780, 126.604385)
                );

                this.map = L.map('map', {
                    maxBounds: bounds,
                    maxBoundsViscosity: 1.0,
                    minZoom: 5,
                    maxZoom: 18
                }).setView([this.lat, this.lon], 6);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                }).addTo(this.map);
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
            },

            handleMapClick(e) {
                const {
                    lat,
                    lng
                } = e.latlng;
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
                this.lat = parseFloat(res.lat);
                this.lon = parseFloat(res.lon);
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
                            alert('Please select a commodity and at least one intervention.');
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
                fetch(
                        `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&addressdetails=1&countrycodes=ph`
                    )
                    .then(res => res.json())
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
                        const fallback = `Lat: ${lat.toFixed(5)}, Lng: ${lon.toFixed(5)}`;
                        this.query = this.selectedLabel = fallback;
                        this.results = [];
                        this.open = false;
                        if (updateMap) this.placeMarker(lat, lon, fallback);
                    });
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
