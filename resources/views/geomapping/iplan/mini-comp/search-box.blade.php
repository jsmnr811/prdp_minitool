  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
      <h5 class="fw-semibold mb-0">📍 Select Location</h5>
          <div x-show="hasChanges" class="d-flex gap-2">
              <button id="save-updates-btn" @click="handleSaveUpdates" wire:loading.attr="disabled"
                class="btn btn-success d-flex align-items-center justify-content-center gap-2 py-2 py-sm-1 btn-sm">
                    <i class="bi bi-check-circle"></i>
                    <span class="d-none d-sm-inline">Save Changes</span>
                    <span class="d-sm-none">Save</span>
              </button>
          </div>
  </div>
  <div class="position-relative mb-3">
      <input type="text" class="form-control form-control-lg" x-model="query" @input.debounce.500="onInput"
          autocomplete="off" id="location-search" placeholder="Search for a city, region..."
          aria-label="Location search" :disabled="$wire.isSearching">
      @if ($isSearching)
          <div class="position-absolute top-50 end-0 translate-middle-y me-3">
              <div class="spinner-border spinner-border-sm text-muted" role="status">
                  <span class="visually-hidden">Searching...</span>
              </div>
          </div>
      @endif
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
