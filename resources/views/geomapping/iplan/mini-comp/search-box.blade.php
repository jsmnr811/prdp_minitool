  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
      <h5 class="fw-semibold mb-0">📍 Select Location</h5>
      @if ($temporaryGeo || $temporaryForDeletion)
          <button type="button" class="btn btn-success btn-sm d-block d-md-inline-block" wire:click="saveUpdates" :disabled="$wire.isSaving">
              @if($isSaving)
              <div class="spinner-border spinner-border-sm me-2" role="status">
                  <span class="visually-hidden">Saving...</span>
              </div>
              Saving...
              @else
              <i class="bi bi-save me-2"></i>
              <span class="d-none d-md-inline">Save Changes</span>
              <span class="d-md-none">Save</span>
              @endif
          </button>
      @endif
  </div>
  <div class="position-relative mb-3">
      <input type="text" class="form-control form-control-lg" x-model="query" @input.debounce.500="onInput"
          autocomplete="off" id="location-search" placeholder="Search for a city, region..."
          aria-label="Location search" :disabled="$wire.isSearching">
      @if($isSearching)
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
