  <div class="d-flex justify-content-between align-items-center mb-3 ">
      <h5 class=" fw-semibold">📍 Select Location</h5>
      @if ($temporaryGeo || $temporaryForDeletion)
          <button type="button" class="btn btn-success" wire:click="saveUpdates"><i class="bi bi-save me-2"></i>Save Changes</button>
      @endif
  </div>
  <div class="position-relative mb-3">
      <input type="text" class="form-control form-control-lg" x-model="query" @input.debounce.500="onInput"
          autocomplete="off" id="location-search" placeholder="Search for a city, region..."
          aria-label="Location search">
      <div x-show="open && results.length"
          class="search-results position-absolute top-100 start-0 w-100 border rounded bg-white shadow-sm "
          style="max-height: 200px; overflow-y: auto; z-index:9999">
          <template x-for="(res, idx) in results" :key="idx">
              <div @click="selectResult(res)" class="p-2 cursor-pointer border-bottom hover:bg-light"
                  :title="res.display_name" style="cursor:pointer">
                  <span x-text="res.display_name"></span>
              </div>
          </template>
      </div>
  </div>
