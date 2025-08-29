<div class="row text-body mt-2" x-show="hasMarker">
    <div class="col-md-6">
        <div class="d-flex align-items-center" style="font-size: 9px;">
            <span class="fw-semibold text-dark me-2">Latitude:</span>
            <span x-text="$wire.lat ? $wire.lat.toFixed(6) : '-'"></span>
        </div>
    </div>
    <div class="col-md-6 mb-2">
        <div class="d-flex align-items-center" style="font-size: 9px;">
            <span class="fw-semibold text-dark me-2">Longitude:</span>
            <span x-text="$wire.lon ? $wire.lon.toFixed(6) : '-'"></span>
        </div>
    </div>
</div>
<small class="text-muted d-block mt-3">
    Alternatively, simply click on the map to pin a precise location.
</small>
