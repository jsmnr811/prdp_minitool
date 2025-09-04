<div class="card shadow-sm p-4 h-100">
    <h5 class="fs-6 mb-3 fw-bold">🗺️ Toggle Map Layers</h5>
    <hr class="mt-2">
    <div class="row row-cols-2 g-3" wire:ignore>
        @foreach ($commodities as $commodity)
        <div class="col">
            <div class="d-flex align-items-center" style="max-width: 100%;">
                <!-- Checkbox -->
                <input
                    class="form-check-input mt-1 me-2"
                    type="checkbox"
                    id="commodity-{{ $commodity->id }}"
                    wire:model.live="selectedFilterCommoditites"
                    value="{{ $commodity->id }}"
                    style="transform: scale(0.9);">

                <!-- Icon + Name side by side -->
                <label
                    for="commodity-{{ $commodity->id }}"
                    class="d-flex align-items-center text-break"
                    style="max-width: calc(100% - 30px); cursor: pointer;">
                    <img
                        src="{{ asset('icons/' . $commodity->icon) }}"
                        onerror="this.onerror=null;this.src='{{ asset('icons/commodities/default.png') }}';"
                        alt="{{ $commodity->name }}"
                        width="24" height="24"
                        class="me-2"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ $commodity->name }}">

                    <span class="text-muted text-wrap" style="font-size: 0.75rem;">
                        {{ $commodity->abbr ?? $commodity->name }}
                    </span>
                </label>
            </div>
        </div>
        @endforeach
    </div>
</div>